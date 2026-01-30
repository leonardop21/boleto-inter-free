<?php

namespace App\Console\Commands;

use App\Interfaces\BoletoInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Interfaces\CustomerInterface;
use App\Interfaces\HolidayInterface;
use Carbon\Carbon;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class GerarBoleto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:auto_generate_boleto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera os boletos automaticamente para clientes ativos com serviços definidos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CustomerInterface $customer, BoletoInterface $boleto, HolidayInterface $holiday)
    {
        parent::__construct();
        $this->customer = $customer;
        $this->boleto = $boleto;
        $this->holiday = $holiday;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $prefixLog = "Gerar boleto -";
        $this->logs("{$prefixLog} Procurando customers ativos e com serviços atrelados");
        $getActiveCustomer = $this->customer->customerActiveWith('services');
        $this->logs("{$prefixLog} Encontrado {$getActiveCustomer->count()} customers ativos \n\n");

        $boletos = [];
        foreach($getActiveCustomer as $customer){
            $total_price = 0;

            $this->logs("{$prefixLog} {$customer->name} tem {$customer->services->count()} serviços ativos");

            foreach($customer->services as $customerService){
                $this->logs("{$prefixLog} {$customerService->name} R$ {$customerService->price}");
                $total_price += number_format($customerService->price, 2, '.', '');
            }

            $this->logs("{$prefixLog} Total dos serviços de {$customer->name} R$ {$total_price}");

            $data =  $this->generateData($customer, $total_price);

            $this->logs("{$prefixLog} meu número {$data['my_number']} vencimento dia {$data['due_date']}");
            
            $response = $this->boleto->store($data);

            $success = is_array($response) && isset($response['success']) && $response['success'] === true;
            $boletoId = $success && isset($response['data']->id) ? $response['data']->id : null;

            if (!$success || $boletoId === null) {
                $errorMessages = [];
                $statusCode = null;
                $rawBody = null;
                if (is_array($response) && isset($response['response'])) {
                    $errorMessages = $this->boleto->extractInterErrors($response['response']);
                    $statusCode = $response['status_code'] ?? null;
                    try {
                        $rawBody = (string) $response['response']->getBody();
                    } catch (\Throwable $e) {
                        $rawBody = null;
                    }
                }
                $reason = !empty($errorMessages) ? implode('; ', $errorMessages) : ($rawBody ?: 'Resposta sem detalhe');
                $this->logs("{$prefixLog} Falha ao gerar boleto para {$customer->name}. Motivo: {$reason}");
                $this->boleto->failedSendBoleto(['erro' => $reason]);

                Bugsnag::notify(
                    new \Exception("Falha ao gerar boleto: {$customer->name}"),
                    function ($report) use ($customer, $reason, $statusCode, $rawBody) {
                        $report->setMetaData([
                            'gerar_boleto' => [
                                'customer_id'   => $customer->id,
                                'customer_name' => $customer->name,
                                'motivo'        => $reason,
                                'status_code'   => $statusCode,
                                'raw_body'      => $rawBody,
                            ],
                        ]);
                    }
                );
                continue;
            }

            $boletos[] = $boletoId;

            $this->logs("{$prefixLog} terminado customer {$customer->name} pegando o próximo \n\n");
            
        }

        $this->logs("{$prefixLog} finalizado a geração de boletos, chamando a tarefa de Download");
        $this->call("ln:downloadBoleto", ["boletos_id" => $boletos]);
    }

    public function generateData($customer, $total_price){
        $due_date = $this->resolveDueDate($customer->expiration_day_boleto);
        $data['my_number'] = $this->generateMyNumber($customer);
        $data['price_cron'] = $total_price;
        $due_date = $this->checkHoliday($due_date);
        $data['due_date'] = $due_date;
        $data['date_to_pay_after_due_date'] = $customer->date_to_pay_after_due_date;
        $data['customer_id'] = $customer->id;
        return $data;
    }

    /**
     * Resolve a data de vencimento: se o dia já passou no mês atual, usa o próximo mês.
     * Se o dia configurado for 31 e o mês tiver menos dias (ex.: 28 ou 30), usa o último dia do mês.
     *
     * @param int $expirationDay Dia do vencimento (1-31) configurado no cliente
     * @return string Data no formato Y-m-d
     */
    private function resolveDueDate($expirationDay)
    {
        $day = (int) $expirationDay;
        $day = min(max($day, 1), 31);

        $today = Carbon::today();

        // Primeiro candidato: mês atual com o dia (respeitando último dia do mês)
        $candidate = $today->copy()->startOfMonth();
        $lastDayOfMonth = $candidate->copy()->endOfMonth()->day;
        $dayToUse = min($day, $lastDayOfMonth);
        $due = $candidate->copy()->addDays($dayToUse - 1);

        // Só se a data já tiver passado (retroativa), usar o próximo mês; vencimento hoje gera para hoje
        if ($due->isPast()) {
            // Primeiro dia do mês atual + 1 mês = primeiro dia do próximo mês (evita overflow ex.: jan 29 + 1 mês)
            $firstDayNextMonth = $today->copy()->startOfMonth()->addMonth();
            $lastDayOfMonth = $firstDayNextMonth->copy()->endOfMonth()->day;
            $dayToUse = min($day, $lastDayOfMonth);
            $due = $firstDayNextMonth->copy()->addDays($dayToUse - 1);
        }

        return $due->format('Y-m-d');
    }

    public function generateMyNumber($customer){
       $generate = $customer->fantasy_name ? substr($customer->fantasy_name, 0, 3) . "-". mt_rand("1000", "99999999") : substr($customer->name, 0, 3) . "-" . mt_rand("1000", "99999999");
       return preg_replace('/\s+/', '', $generate);
    }

    public function checkHoliday($due_date){
        $prefixLog = "Gerar boleto -";
        $this->logs("{$prefixLog} Checando se é feriado ou final de semana");
        $explode =  explode("-", $due_date);
        $holiday = $explode[2] . "/" . $explode[1];
        $checkHoliday = $this->holiday->find($holiday);
        $checkWeekend = checkWeekend($due_date, $checkHoliday);
        $this->logs("{$prefixLog} Data atual de vencimento: {$due_date} - após checagem de feriado ou final de semana: {$checkWeekend}");
        return $checkWeekend;
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-boleto/boleto.log'),
        ])->info($message);
    }
}
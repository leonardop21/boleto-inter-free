<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Interfaces\CustomerInterface;
use App\Interfaces\HolidayInterface;
use App\Interfaces\NotifishInterface;
use App\Repositories\PixRepository;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class GeneratePix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:auto_generate_pix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o pix e qr code e envia para os clientes ativos com serviços definidos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CustomerInterface $customer, PixRepository $pix, NotifishInterface $notifish, HolidayInterface $holiday)
    {
        parent::__construct();
        $this->customer = $customer;
        $this->pix = $pix;
        $this->notifish = $notifish;
        $this->holiday = $holiday;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $prefixLog = "Gerar pix -";
        $this->logs("{$prefixLog} Procurando customers ativos e com serviços atrelados");
        $getActiveCustomer = $this->customer->customerActiveWith('services');
        $this->logs("{$prefixLog} Encontrado {$getActiveCustomer->count()} customers ativos \n\n");

        $pixes = [];
        foreach($getActiveCustomer as $customer){
            $total_price = 0;
            $this->logs("{$prefixLog} {$customer->name} tem {$customer->services->count()} serviços ativos");

            foreach($customer->services as $customerService){
                $this->logs("{$prefixLog} {$customerService->name} R$ {$customerService->price}");
                $total_price += number_format($customerService->price, 2, '.', '');
            }

            $this->logs("{$prefixLog} Total dos serviços de {$customer->name} R$ {$total_price}");

            // Mesma lógica do boleto: dia passado → próximo mês; dia 31 → último do mês; feriado/fim de semana
            $due_date = $this->resolveDueDate($customer->expiration_day_boleto);
            $due_date = $this->checkHoliday($due_date);

            $data = [
                "descripton" => "Pix gerado automaticamente",
                "my_number"  => $this->generateMyNumber($customer),
                "price"      => $total_price,
                "due_date"   => $due_date
            ];

            $this->logs("{$prefixLog} {$customer->name} meu número {$data['my_number']} valor R$ {$total_price} vencimento {$data['due_date']}");

            $response = $this->pix->storePix($customer, $data, true);

            $success = is_array($response) && isset($response['success']) && $response['success'] === true;
            $pixId = $success && isset($response['data']->id) ? $response['data']->id : null;

            if (!$success || $pixId === null) {
                $reason = $response['message'] ?? ($response['raw_body'] ?? 'Resposta sem detalhe');
                $statusCode = $response['status_code'] ?? null;
                $rawBody = $response['raw_body'] ?? null;
                $this->logs("{$prefixLog} Falha ao gerar pix para {$customer->name}. Motivo: {$reason}");
                $this->notifish->sendNotificationMessage("Falha ao gerar pix para o Customer: {$customer->name}");

                Bugsnag::notify(
                    new \Exception("Falha ao gerar PIX: {$customer->name}"),
                    function ($report) use ($customer, $reason, $statusCode, $rawBody, $response) {
                        $report->setMetaData([
                            'gerar_pix' => [
                                'customer_id'   => $customer->id,
                                'customer_name' => $customer->name,
                                'motivo'        => $reason,
                                'status_code'   => $statusCode,
                                'raw_body'      => $rawBody,
                                'data'          => $response['data'] ?? null,
                            ],
                        ]);
                    }
                );
                continue;
            }

            $pixes[] = $pixId;
            $this->logs("{$prefixLog} terminado customer {$customer->name} pegando o próximo \n\n");
        }

        $this->logs("{$prefixLog} finalizado a geração de pixes, chamando a tarefa de geração de QRCODE");
        $this->call("ln:mount_qr_code", ["pixes_id" => $pixes]);
    }

    public function generateMyNumber($customer){
        $generate = $customer->fantasy_name ? substr($customer->fantasy_name, 0, 3) . "-". mt_rand("1000", "99999999") : substr($customer->name, 0, 3) . "-" . mt_rand("1000", "99999999");
        return preg_replace('/\s+/', '', $generate);
    }

    /**
     * Resolve a data de vencimento: se o dia já passou no mês atual, usa o próximo mês.
     * Se o dia configurado for 31 e o mês tiver menos dias, usa o último dia do mês.
     * Mesma lógica do comando GerarBoleto.
     */
    private function resolveDueDate($expirationDay)
    {
        $day = (int) $expirationDay;
        $day = min(max($day, 1), 31);
        $today = Carbon::today();
        $candidate = $today->copy()->startOfMonth();
        $lastDayOfMonth = $candidate->copy()->endOfMonth()->day;
        $dayToUse = min($day, $lastDayOfMonth);
        $due = $candidate->copy()->addDays($dayToUse - 1);
        if ($due->isPast()) {
            $firstDayNextMonth = $today->copy()->startOfMonth()->addMonth();
            $lastDayOfMonth = $firstDayNextMonth->copy()->endOfMonth()->day;
            $dayToUse = min($day, $lastDayOfMonth);
            $due = $firstDayNextMonth->copy()->addDays($dayToUse - 1);
        }
        return $due->format('Y-m-d');
    }

    /**
     * Ajusta a data se cair em feriado ou final de semana (mesma lógica do GerarBoleto).
     */
    private function checkHoliday($due_date)
    {
        $prefixLog = "Gerar pix -";
        $this->logs("{$prefixLog} Checando se é feriado ou final de semana");
        $parts = explode("-", $due_date);
        $holiday = ($parts[2] ?? '') . "/" . ($parts[1] ?? '');
        $checkHoliday = $this->holiday->find($holiday);
        $adjusted = checkWeekend($due_date, $checkHoliday);
        $this->logs("{$prefixLog} Data de vencimento: {$due_date} - após feriado/fim de semana: {$adjusted}");
        return $adjusted;
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-pix/pix.log'),
        ])->info($message);
    }
}
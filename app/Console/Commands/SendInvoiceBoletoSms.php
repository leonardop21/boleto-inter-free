<?php

namespace App\Console\Commands;

use App\Interfaces\BoletoInterface;
use App\Services\LnService;
use Illuminate\Console\Command;
use App\Interfaces\PixInterface;
use Illuminate\Support\Facades\Log;

class SendInvoiceBoletoSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send-boleto-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia SMS de cobranças boleto';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BoletoInterface $boleto, LnService $lnService)
    {
        parent::__construct();
        $this->boleto = $boleto;
        $this->send = $lnService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $prefixLog = "Lembrete de fatura boleto via SMS -";
        $this->logs("{$prefixLog} Pegando Boletos");
        
        $boletoes = $this->boleto->getDueDateBoleto2Resend();

        $baseUrl = "https://api.smsdev.com.br/v1/send?";
        $token = env('TOKEN_SMS');
       
        $this->logs("{$prefixLog} Total de boletos: " . count($boletoes));

        if($boletoes->isEmpty()){
            $this->logs("{$prefixLog} Nenhum boleto encontrado");
            return;
        }

       if(checkDoliday()){
            $this->logs("{$prefixLog} Hoje é feriado, não tem cobrança");
            return;
       }

        foreach($boletoes as $boleto){
            $this->logs("{$prefixLog} Enviando cobrança via sms de boleto para {$boleto->customer->name}");
            $name = explode(" ", $boleto->customer->name)[0];

            $message = "Ola, {$name}! A LN SISTEMAS informa que existem pendências financeiras referentes a faturas não quitadas. Considere efetuar o pagamento agora via PIX." ;

            $url = $baseUrl . "key={$token}&type=9&number={$boleto->customer->phone}&msg=" . urlencode($message);

            $response = $this->send->get($url);

            if($response->getStatusCode() == 200){
                $this->boleto->update(["sms_notify_send" => date("Y-m-d H:i:s")], $boleto->id);
                $this->logs("{$prefixLog} Cobrança enviada com sucesso para {$boleto->customer->name}");
            }	
            else {
                $this->logs("{$prefixLog} Erro ao enviar cobrança para {$boleto->customer->name}");
            }
        }

        $this->logs("{$prefixLog} último envio");

    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/cobranca-boleto-sms/boleto.log'),
        ])->info($message);
    }
}

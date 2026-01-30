<?php

namespace App\Console\Commands;

use App\Interfaces\BoletoInterface;
use Illuminate\Console\Command;
use App\Services\LnService;
use Illuminate\Support\Facades\Log;

class SendInvoiceBoletoVoicer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send-boleto-voicer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta e envia cobranças boleto via Voicer';

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
        $prefixLog = "Lembrete de fatura boleto via Voicer -";
        $this->logs("{$prefixLog} Pegando boletos");
        
        $boletos = $this->boleto->getDueDateBoleto2Resend();

        $login = env('VOICER_LOGIN');
        $token = env('VOICER_TOKEN');
        $msg = "audio:" . env('VOICER_AUDIO');
       
        $this->logs("{$prefixLog} Total de boletos: " . count($boletos));

        if($boletos->isEmpty()){
            $this->logs("{$prefixLog} Nenhum boleto encontrado");
            return;
        }

       if(checkDoliday()){
            $this->logs("{$prefixLog} Hoje é feriado, não tem cobrança");
            return;
       }

        foreach($boletos as $boleto){
            $this->logs("{$prefixLog} Enviando cobrança via voicer de boleto para {$boleto->customer->name}");
            
            $url = "https://painel.kingsms.com.br/kingsms/api.php?acao=sendsms&login={$login}&token={$token}&numero={$boleto->customer->phone}&msg={$msg}";

            $response = $this->send->get($url);


            if($response->getStatusCode() == 200){
                $this->boleto->update(["voicer_notify_send" => date("Y-m-d H:i:s")], $boleto->id);
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
            'path' => storage_path('logs/cobranca-boleto-voicer/boleto.log'),
        ])->info($message);
    }
}

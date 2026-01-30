<?php

namespace App\Console\Commands;

use App\Interfaces\CustomerInterface;
use Illuminate\Console\Command;
use App\Interfaces\PixInterface;
use App\Interfaces\SendInterface;
use App\Models\Holiday;
use App\Services\LnService;
use Illuminate\Support\Facades\Log;

class SendInvoicePixVoicer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send-pix-voicer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta e envia cobranças PIX via Voicer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PixInterface $pix, LnService $lnService)
    {
        parent::__construct();
        $this->pix = $pix;
        $this->send = $lnService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $prefixLog = "Lembrete de fatura pix via Voicer -";
        $this->logs("{$prefixLog} Pegando Pixes");
        
        $pixes = $this->pix->getDueDateExpiredPixResend();

        $login = env('VOICER_LOGIN');
        $token = env('VOICER_TOKEN');
        $msg = "audio:" . env('VOICER_AUDIO');
       
        $this->logs("{$prefixLog} Total de Pixes: " . count($pixes));

        if($pixes->isEmpty()){
            $this->logs("{$prefixLog} Nenhum Pix encontrado");
            return;
        }

       if(checkDoliday()){
            $this->logs("{$prefixLog} Hoje é feriado, não tem cobrança");
            return;
       }

        foreach($pixes as $pix){
            $this->logs("{$prefixLog} Enviando cobrança via voicer de Pix para {$pix->customer->name}");
            
            $url = "https://painel.kingsms.com.br/kingsms/api.php?acao=sendsms&login={$login}&token={$token}&numero={$pix->customer->phone}&msg={$msg}";

            $response = $this->send->get($url);


            if($response->getStatusCode() == 200){
                $this->pix->update(["voicer_notify_send" => date("Y-m-d H:i:s")], $pix->id);
                $this->logs("{$prefixLog} Cobrança enviada com sucesso para {$pix->customer->name}");
            }	
            else {
                $this->logs("{$prefixLog} Erro ao enviar cobrança para {$pix->customer->name}");
            }
        }

        $this->logs("{$prefixLog} último envio");
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/cobranca-pix-voicer/pix.log'),
        ])->info($message);
    }
}

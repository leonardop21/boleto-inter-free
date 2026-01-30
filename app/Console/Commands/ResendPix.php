<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Interfaces\PixInterface;
use Illuminate\Support\Facades\Log;
use App\Repositories\SendRepository;

class ResendPix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:resend-pix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lembrete de vencimento de fatura';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PixInterface $pix, SendRepository $sendPix)
    {
        parent::__construct();
        $this->pix = $pix;
        $this->sendPix = $sendPix;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pixes = $this->pix->getDueDatePixResend();
        $prefixLog = "Lembrete Pix -";

        if(count($pixes) == 0){
            $this->logs("{$prefixLog} Nada para enviar");
            die();
        }

        $this->logs("{$prefixLog} Iniciando o envio de lembrete de cobrança" . count($pixes) . " pixes");
        
        $failed_ids = [];
        $success_ids = [];
        foreach($pixes as $pix){
            $this->logs("{$prefixLog} Enviando lembrete de cobrança pix id {$pix->id}");
            $response = $this->sendPix->sendEmailPix($pix->id, true);

            if($response->getStatusCode() != 200){
                $failed_ids[] .= $pix->id;
            }else{
                $success_ids[] .= $pix->id;
                $this->pix->update(["email_notify_send" => date("Y-m-d H:i:s")], $pix->id);
            }
            
            $this->logs("{$prefixLog} Resposta envio pix de lembrete {$pix->id} --- {$response}");
            $this->logs("------------------------------------------------------------------  \n\n\n");
            $this->logs("{$prefixLog} Pegando o próximo pix de lembrete");
        }

        if($failed_ids){
            $this->logs("{$prefixLog} falha ao enviar pix de lembrete, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $this->pix->failedSendPix($failed_ids);
        }

        if($success_ids){
            $this->logs("{$prefixLog} Pixes de lembrete enviados com sucesso, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $this->pix->successSendPix($success_ids);
        }
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-pix/pix-lembrete.log'),
        ])->info($message);
    }
}

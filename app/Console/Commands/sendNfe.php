<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Interfaces\NfeInterface;
use App\Interfaces\NotifishInterface;
use Illuminate\Support\Facades\Log;

class SendNfe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send-nfe {nfes_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia NF por e-mail e notifica via Notifish (WhatsApp)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NfeInterface $nf, NotifishInterface $notifish)
    {
        parent::__construct();
        $this->nf = $nf;
        $this->notifish = $notifish;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $nfes_id = $this->argument("nfes_id");
        $prefixLog = "Enviar NF -";

        if(count($nfes_id) == 0){
            $this->logs("{$prefixLog} Nada para enviar");
            die();
        }

        $this->logs("{$prefixLog} Iniciando o envio de " . count($nfes_id) . " Nfes");
        
        $failed_ids = [];
        $success_ids = [];
        foreach($nfes_id as $id){
            $this->logs("{$prefixLog} Enviando a NF id {$id}");
            $response = $this->nf->sendNf($id);

            if($response->getStatusCode() != 200){
                $failed_ids[] .= $id;
            }else{
                $success_ids[] .= $id;
                $this->nf->update(["email_notify_send" => date("Y-m-d H:i:s")], $id);
            }
            
            $this->logs("{$prefixLog} Resposta envio nf {$id} --- {$response}");
            $this->logs("------------------------------------------------------------------  \n\n\n");
            $this->logs("{$prefixLog} Pegando a próxima nf");
        }

        if ($success_ids) {
            $this->logs("{$prefixLog} Nf-es enviados com sucesso, alertando via Notifish " . env('NOTIFY_SEND_BOLETO_WHATSAPP'));
            $this->notifish->notifySendNfeWhatsApp($success_ids, true);
        }

    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-nfe/nfe.log'),
        ])->info($message);
    }
}

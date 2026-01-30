<?php

namespace App\Console\Commands;

use App\Models\Pix;
use Illuminate\Console\Command;
use App\Interfaces\PixInterface;
use App\Interfaces\SendInterface;
use App\Interfaces\NotifishInterface;
use Illuminate\Support\Facades\Log;
use App\Repositories\SendRepository;

class SendPix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send_pix {pixes_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia o pix por e-mail e Notifish (WhatsApp)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PixInterface $pix, SendInterface $send, NotifishInterface $notifish)
    {
        parent::__construct();
        $this->send = $send;
        $this->pix = $pix;
        $this->model = Pix::class;
        $this->notifish = $notifish;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pixes_id = $this->argument("pixes_id");
        $prefixLog = "Enviar Pix -";

        if(count($pixes_id) == 0){
            $this->logs("{$prefixLog} Nada para enviar");
            die();
        }

        $this->logs("{$prefixLog} Iniciando o envio de " . count($pixes_id) . " pixes");
        
        $failed_ids = [];
        $success_ids = [];

        foreach($pixes_id as $id){
            $this->logs("{$prefixLog} Enviando Pix id {$id}");
            $response = $this->send->sendEmailPix($id);

            if($response->getStatusCode() != 200){
                $failed_ids[] .= $id;
            }else{
                $success_ids[] .= $id;
                $this->pix->update(["email_notify_send" => date("Y-m-d H:i:s")], $id);
            }
            
            $this->logs("{$prefixLog} Resposta envio pix {$id} --- {$response}");
            $this->logs("------------------------------------------------------------------  \n\n\n");
            $this->logs("{$prefixLog} Pegando o próximo pix");
        }

        if($failed_ids){
            $this->logs("{$prefixLog} falha ao enviar pix, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $title = "Falha ao enviar Cobranças via pix";
            $this->send->sendNotificationSucessOrFailed($this->model, $title, "invoice-failed", $failed_ids);
        }

        if($success_ids){
            $this->logs("{$prefixLog} Pixes enviados com sucesso, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $title = "Cobranças via Pix enviadas com sucesso";
            $this->send->sendNotificationSucessOrFailed($this->model, $title, "invoice-success", $success_ids);
        }

        $this->logs("{$prefixLog} Envios por e-mail finalizado. Chamando envios via Notifish \n\n");
        $key = env("NOTIFISH_UUID");

        if (!empty($key)) {
            $this->sendNotifish($pixes_id);
        }
    }

    public function sendNotifish($ids)
    {
        $prefixLog = "Enviar Pix Notifish -";
        $success_ids = [];
        $failed_ids = [];

        foreach ($ids as $key => $id) {
            try {
                $response = $this->notifish->sendMessageBeforePix($id);
                $response = is_string($response) ? json_decode($response) : $response;
                $isSuccess = $response && strtolower($response->status ?? '') === 'success';

                if ($isSuccess) {
                    $success_ids[] = $id;
                } else {
                    $failed_ids[] = $id;
                }
            } catch (\Exception $e) {
                $this->logs("{$prefixLog} Erro ao enviar Pix ID {$id}: " . $e->getMessage());
                $failed_ids[] = $id;
            }
            sleep(2);
        }

        if ($failed_ids) {
            $this->logs("{$prefixLog} falha ao enviar Pix, alertando via Notifish " . env('NOTIFY_SEND_BOLETO_WHATSAPP'));
            $this->notifish->notifySendBoletoWhatsApp($failed_ids, false, true);
        }

        if ($success_ids) {
            $this->logs("{$prefixLog} Pixes enviados com sucesso, alertando via Notifish " . env('NOTIFY_SEND_BOLETO_WHATSAPP'));
            $this->notifish->notifySendBoletoWhatsApp($success_ids, true, true);
        }

        $this->logs("{$prefixLog} finalizado envios via Notifish \n\n");
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-pix/pix.log'),
        ])->info($message);
    }
}
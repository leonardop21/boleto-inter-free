<?php

namespace App\Console\Commands;

use App\Models\Pix;
use Illuminate\Console\Command;
use App\Interfaces\PixInterface;
use App\Interfaces\SendInterface;
use Illuminate\Support\Facades\Log;

class ResendDueDatePix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:resend-duedate-pix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lembrete de pix vencido';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PixInterface $pix, SendInterface $send)
    {
        parent::__construct();
        $this->pix = $pix;
        $this->send = $send;
        $this->model = Pix::class;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pixes = $this->pix->getDueDateExpiredPixResend();
        $prefixLog = "Enviar pix atrasado -";

        if(count($pixes) == 0){
            $this->logs("{$prefixLog} Nada para enviar");
            die();
        }

        $this->logs("{$prefixLog} Iniciando o envio de pixes vencidos, total de: " . count($pixes));
        
        $failed_ids = [];
        $success_ids = [];
        foreach($pixes as $pix){
            $this->logs("{$prefixLog} Enviando lembrete de pix vencido id {$pix->id}");
            $response = $this->send->sendEmailPix($pix->id, 'vencido');

            if($response->getStatusCode() != 200){
                $failed_ids[] .= $pix->id;
            }else{
                $success_ids[] .= $pix->id;
                $this->pix->update(["email_notify_send" => date("Y-m-d H:i:s")], $pix->id);
            }
            
            $this->logs("{$prefixLog} Resposta envio pix vencido {$pix->id} --- {$response}");
            $this->logs("------------------------------------------------------------------  \n\n\n");
            $this->logs("{$prefixLog} Pegando o próximo pix vencido");
        }

        if($failed_ids){
            $this->logs("{$prefixLog} falha ao enviar pix vencido, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $title = "Falha ao enviar Cobranças via pix";
            $this->send->sendNotificationSucessOrFailed($this->model, $title, "invoice-failed", $failed_ids);
        }

        if($success_ids){
            $this->logs("{$prefixLog} Pix vencidos enviados com sucesso, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $title = "Cobranças via Pix enviadas com sucesso";
            $this->send->sendNotificationSucessOrFailed($this->model, $title, "invoice-success", $success_ids);
        }
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/send-pix/pix-vencido.log'),
        ])->info($message);
    }
}

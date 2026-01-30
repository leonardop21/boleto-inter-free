<?php

namespace App\Console\Commands;

use App\Interfaces\BoletoInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBoleto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send-boleto {boletos_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia o boleto por e-mail e chama a função de enviar pelo WhatsApp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BoletoInterface $boleto)
    {
        parent::__construct();
        $this->boleto = $boleto;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $boletos_id = $this->argument("boletos_id");
        $prefixLog = "Enviar boleto -";

        if(count($boletos_id) == 0){
            $this->logs("{$prefixLog} Nada para enviar");
            die();
        }

        $this->logs("{$prefixLog} Iniciando o envio de " . count($boletos_id) . " boletos");
        
        $failed_ids = [];
        $success_ids = [];
        foreach($boletos_id as $id){
            $this->logs("{$prefixLog} Enviando boleto id {$id}");
            $response = $this->boleto->sendBoleto($id);

            if($response->getStatusCode() != 200){
                $failed_ids[] .= $id;
            }else{
                $success_ids[] .= $id;
                $this->boleto->update(["email_notify_send" => date("Y-m-d H:i:s")], $id);
            }
            
            $this->logs("{$prefixLog} Resposta envio boleto {$id} --- {$response}");
            $this->logs("------------------------------------------------------------------  \n\n\n");
            $this->logs("{$prefixLog} Pegando o próximo boleto");
        }

        if($failed_ids){
            $this->logs("{$prefixLog} falha ao enviar boleto, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $this->boleto->failedSendBoleto($failed_ids);
        }

        if($success_ids){
            $this->logs("{$prefixLog} Boletos enviados com sucesso, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $this->boleto->successSendBoleto($success_ids);
        }

        $this->logs("{$prefixLog} Envios por e-mail finalizado. Chamando envios via Notifish \n\n");
        $key = env("NOTIFISH_UUID");

        if (!empty($key)) {
            $this->call("ln:send-whatsapp", ["boletos_id" => $boletos_id]);
        }
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-boleto/boleto.log'),
        ])->info($message);
    }
}

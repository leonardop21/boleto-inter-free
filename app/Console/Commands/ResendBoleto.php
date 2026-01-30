<?php

namespace App\Console\Commands;

use App\Interfaces\BoletoInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResendBoleto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:resend-boleto';

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
        $boletos = $this->boleto->getDueDateBoletoResend();
        $prefixLog = "Enviar boleto -";

        if(count($boletos) == 0){
            $this->logs("{$prefixLog} Nada para enviar");
            die();
        }

        $this->logs("{$prefixLog} Iniciando o envio de lembrete de cobrança" . count($boletos) . " boletos");
        
        $failed_ids = [];
        $success_ids = [];
        foreach($boletos as $boleto){
            $this->logs("{$prefixLog} Enviando lembrete de cobrança boleto id {$boleto->id}");
            $response = $this->boleto->sendBoleto($boleto->id, true);

            if($response->getStatusCode() != 200){
                $failed_ids[] .= $boleto->id;
            }else{
                $success_ids[] .= $boleto->id;
                $this->boleto->update(["email_notify_send" => date("Y-m-d H:i:s")], $boleto->id);
            }
            
            $this->logs("{$prefixLog} Resposta envio boleto de lembrete {$boleto->id} --- {$response}");
            $this->logs("------------------------------------------------------------------  \n\n\n");
            $this->logs("{$prefixLog} Pegando o próximo boleto de lembrete");
        }

        if($failed_ids){
            $this->logs("{$prefixLog} falha ao enviar boleto de lembrete, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $this->boleto->failedSendBoleto($failed_ids);
        }

        if($success_ids){
            $this->logs("{$prefixLog} Boletos de lembrete enviados com sucesso, alertando via e-mail " . env('NOTIFY_SEND_BOLETO'));
            $this->boleto->successSendBoleto($success_ids);
        }
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-boleto/boleto-lembrete.log'),
        ])->info($message);
    }
}

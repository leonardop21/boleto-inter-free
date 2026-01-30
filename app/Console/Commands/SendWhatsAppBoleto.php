<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Interfaces\NotifishInterface;
use Illuminate\Support\Facades\Log;

class SendWhatsAppBoleto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send-whatsapp {boletos_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia boletos via Notifish (WhatsApp)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotifishInterface $notifish)
    {
        parent::__construct();
        $this->notifish = $notifish;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $boletos_id = $this->argument("boletos_id");
        
        // Normaliza o argumento: pode vir como array (programático) ou string (CLI)
        if (is_array($boletos_id)) {
            // Já é array, usa diretamente
            $boletos_id = $boletos_id;
        } elseif (is_string($boletos_id)) {
            // Remove colchetes se existirem (ex: "[1,2,3]" vira "1,2,3")
            $boletos_id = trim($boletos_id, '[]');
            
            // Tenta decodificar JSON primeiro (ex: "[1,2,3]")
            $decoded = json_decode($boletos_id, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $boletos_id = $decoded;
            } else {
                // Se não for JSON válido, tenta separar por vírgula
                $boletos_id = array_filter(array_map('trim', explode(',', $boletos_id)));
                if (empty($boletos_id)) {
                    // Se ainda assim estiver vazio, trata como array com um único elemento (o próprio valor original)
                    $boletos_id = [trim($boletos_id, '[]')];
                }
            }
        } else {
            // Fallback: converte para array
            $boletos_id = [$boletos_id];
        }
        
        // Garante que todos os IDs sejam inteiros
        $boletos_id = array_map('intval', array_filter($boletos_id, function($id) {
            return is_numeric($id) && $id > 0;
        }));
        
        if (empty($boletos_id)) {
            $this->error('Nenhum ID de boleto válido fornecido.');
            return 1;
        }
        
        $prefixLog = "Notifish send - ";

        $success_ids = [];
        $failed_ids = [];

        foreach ($boletos_id as $key => $id) {
            try {
                $response = $this->notifish->sendMessageBeforeBoleto($id);
                $response = is_string($response) ? json_decode($response) : $response;
                $isSuccess = $response && strtolower($response->status ?? '') === 'success';

                if ($isSuccess) {
                    $success_ids[] = $id;
                } else {
                    $failed_ids[] = $id;
                }
            } catch (\Exception $e) {
                $this->logs("{$prefixLog} Erro ao enviar boleto ID {$id}: " . $e->getMessage());
                $failed_ids[] = $id;
            }

            sleep(2);
        }

        if ($failed_ids) {
            $this->logs("{$prefixLog} falha ao enviar boleto, alertando via Notifish " . env('NOTIFY_SEND_BOLETO_WHATSAPP'));
            $this->notifish->notifySendBoletoWhatsApp($failed_ids, false);
        }

        if ($success_ids) {
            $this->logs("{$prefixLog} Boletos enviados com sucesso, alertando via Notifish " . env('NOTIFY_SEND_BOLETO_WHATSAPP'));
            $this->notifish->notifySendBoletoWhatsApp($success_ids, true);
        }

        $this->logs("{$prefixLog} finalizado envios via Notifish \n\n");
        
        return 0;
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-boleto/boleto.log'),
        ])->info($message);
    }
}

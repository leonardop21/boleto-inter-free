<?php

namespace App\Console\Commands;

use App\Interfaces\BoletoInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DownloadBoleto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:downloadBoleto {boletos_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baixa os boletos gerados no inter';

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
        $prefixLog = "Download boleto -";

        if(count($boletos_id) == 0){
            $this->logs("{$prefixLog} Nada para baixar");
            die();
        }

        $this->logs("{$prefixLog} recebido " . count($boletos_id) . " ids de boletos");

        foreach($boletos_id as $id){
           $this->logs("{$prefixLog} efetuando download boleto id {$id}");
           $response = $this->boleto->getPdf($id);
           $this->logs("{$prefixLog} resposta {$response}");
        }

        $this->logs("{$prefixLog} finalizado o download de boletos, chamando a tarefa de envio:" . json_encode($boletos_id) . "\n\n");
        $this->call("ln:send-boleto", ["boletos_id" => $boletos_id ]);
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-boleto/boleto.log'),
        ])->info($message);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Interfaces\HolidayInterface;
use App\Interfaces\CustomerInterface;
use App\Interfaces\PixInterface;
use App\Repositories\PixRepository;

class MountQrCodePix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:mount_qr_code {pixes_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o QR code dos pixes gerados pelo inter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PixInterface $pix)
    {
        parent::__construct();
        $this->pix = $pix;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pixes_id = $this->argument("pixes_id");
        $prefixLog = "Criar QrCode -";

        if(count($pixes_id) == 0){
            $this->logs("{$prefixLog} Nada para criar");
            die();
        }

        $this->logs("{$prefixLog} recebido " . count($pixes_id) . " ids de pixes");

        foreach($pixes_id as $id){
           $this->logs("{$prefixLog} criando QRCODE pix id {$id}");
           $response = $this->pix->generateQRcode($id);
           $this->logs("{$prefixLog} resposta {$response}");
        }

        $this->logs("{$prefixLog} finalizado a criação dos pixes, chamando a tarefa de envio: " . json_encode($pixes_id) . "\n\n");
        $this->call("ln:send_pix", ["pixes_id" => $pixes_id ]);
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-pix/pix.log'),
        ])->info($message);
    }
}
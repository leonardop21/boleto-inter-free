<?php

namespace App\Console\Commands;

use App\Services\LnService;
use Illuminate\Console\Command;
use App\Interfaces\PixInterface;
use Illuminate\Support\Facades\Log;

class SendInvoicePixSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:send-pix-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia SMS de cobranças PIX';

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

        $prefixLog = "Lembrete de fatura pix via SMS -";
        $this->logs("{$prefixLog} Pegando Pixes");
        
        $pixes = $this->pix->getDueDateExpiredPixResend();

        $baseUrl = "https://api.smsdev.com.br/v1/send?";
        $token = env('TOKEN_SMS');
       
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
            $this->logs("{$prefixLog} Enviando cobrança via sms de Pix para {$pix->customer->name}");
            $name = explode(" ", $pix->customer->name)[0];

            $message = "Ola, {$name}! A LN SISTEMAS informa que existem pendências financeiras referentes a faturas não quitadas. Considere efetuar o pagamento agora via PIX." ;

            $url = $baseUrl . "key={$token}&type=9&number={$pix->customer->phone}&msg=" . urlencode($message);

            $response = $this->send->get($url);

            if($response->getStatusCode() == 200){
                $this->pix->update(["sms_notify_send" => date("Y-m-d H:i:s")], $pix->id);
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
            'path' => storage_path('logs/cobranca-pix-sms/pix.log'),
        ])->info($message);
    }
}

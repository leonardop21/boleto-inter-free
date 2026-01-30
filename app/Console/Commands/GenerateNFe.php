<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Interfaces\NfeInterface;
use Illuminate\Support\Facades\Log;
use App\Interfaces\CustomerInterface;

class GenerateNFe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:generate-nfe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Geração automatica de NF para todos os clientes ativos e com serviços atrelados';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CustomerInterface $customer, NfeInterface $nfe)
    {
        parent::__construct();

        $this->customer = $customer;
        $this->nfe = $nfe;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $prefixLog = "Gerar NFe -";

        $this->logs("{$prefixLog} Procurando customers ativos e disponíveis para emitir NF");
        $getActiveCustomer = $this->customer->customerActiveWithNfe('customerNfeType');

        if($getActiveCustomer->count() < 1){
            $this->logs("{$prefixLog} Nenhum customer ativo ou disponível para emitir nf \n\n");
            die();
        }

        $this->logs("{$prefixLog} Encontrado {$getActiveCustomer->count()} customers ativos \n\n");

        $nfeId = [];
        foreach($getActiveCustomer as $customer){
            $total_price = 0;

            $this->logs("{$prefixLog} {$customer->name} tem {$customer->services->count()} serviços ativos");

            foreach($customer->services as $customerService){
                $this->logs("{$prefixLog} {$customerService->name} R$ {$customerService->price}");
                $total_price += number_format($customerService->price, 2, '.', '');
            }

            $this->logs("{$prefixLog} Total dos serviços de {$customer->name} R$ {$total_price}");

            
            $response = $this->nfe->store($customer);

            if(!$response){
               continue;
            }

            $nfeId[] = $response->id;

            $this->logs("{$prefixLog} terminado customer {$customer->name} pegando o próximo \n\n");
            
        }

        $this->logs("{$prefixLog} finalizado a geração de NFes, chamando a tarefa de envio de NFes");
        $this->call("ln:send-nfe", ["nfes_id" => $nfeId]);

    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-nfe/nfe.log'),
        ])->info($message);
    }
}

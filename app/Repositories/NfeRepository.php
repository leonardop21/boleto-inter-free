<?php 

namespace App\Repositories;

use App\Models\Nfe;
use App\Services\NfeService;
use App\Interfaces\NfeInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Interfaces\CustomerInterface;
use Illuminate\Support\Facades\Cache;

class NfeRepository extends AbstractRepository implements NfeInterface
{

    protected $model = Nfe::class;

    public function __construct(CustomerInterface $customer, NfeService $nfeService)
    {
        parent::__construct();
        $this->nfeService = $nfeService;
        $this->customer = $customer;
    }

    public function find($id) {}

    public function withPaginate($with, $customer_id){
        $page = request()->get('page', 1);

        if($customer_id){
            if(!Cache::tags($this->tableCache)->has('_page_get_nfe_' . $customer_id . "_" . $page)){
                $records = $this->model::where('customer_id', $customer_id)->with('customer')->orderBy('id', 'desc')->paginate(10);
                Cache::tags([$this->tableCache])->put('_page_get_nfe_' .  $customer_id . "_" . $page, $records, $this->time_cache);
            }
            return Cache::tags([$this->tableCache])->get('_page_get_nfe_' .  $customer_id . "_" . $page);
        }

        if(!Cache::tags($this->tableCache)->has('_page_with_' . $page)){
            $records = $this->model::with($with)->orderBy('id', 'desc')->paginate(10);
            Cache::tags([$this->tableCache])->put('_page_with_' . $page, $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_page_with_' . $page);
    }

    public function store($customer){
        if(!isset($customer->customer_nfe_type_id)){
            $this->logs("Não é possível gerar a NF, verifique o customer_nfe_type_id para o customer_id " . $customer->id . "\n\n");
            return 0;
        }

        $this->model = Nfe::class;
        $abs = AbstractRepository::class;

        $archive = $this->createNote($customer);

        $this->logs("Chamando a geração da NFe via webservice " . $customer->id);

        $response = $this->nfeService->sendNfe($archive);

        $xml = simplexml_load_string(trim($response));

        $newData = [];
        $newData['customer_id'] = $customer->id;
        $newData['boleto_id'] = isset($customer->boleto_id) ? $customer->boleto_id : null;
        $newData['nfe_number'] = $xml->numero_nfse;
        $newData['serie_nfse'] = $xml->serie_nfse;
        $newData['date_time'] = $xml->data_nfse . " " . $xml->hora_nfse;
        $newData['situation_code_nfe'] = $xml->situacao_codigo_nfse;
        $newData['situation_description_nfe'] = $xml->situacao_descricao_nfse;
        $newData['link_nfse'] = $xml->link_nfse;
        $newData['authenticity_verifier_code'] = $xml->cod_verificador_autenticidade;

        unlink($archive);

        if(!isset($xml->situacao_codigo_nfse) && !$xml->situacao_codigo_nfse == "1"){
            $this->logs("Ocorreu um erro ao gerar a nota via webservice para o customer id " . $customer->id);
            $this->logs(json_encode($xml) . "\n\n");
            return 0;
        }

        $this->logs("NF-e gerada com sucesso via webservice para o customer " . $customer->id);

        return $abs::store($newData);
    }
    public function createNote($customer){
        $this->logs("Iniciando a geração do xml para o customer id: " . $customer->id);
        $service_detail = [];
        $total_price = 0;

        foreach($customer->services as $service){
            $service_detail[] = $service->name . " R$ " . $service->price;
            $total_price += $service->price;
        }

        $type = $customer->type_of_person == "fisica" ? "F" : "J";
        
        $city = getCityCodeNfe($customer->city);

        $xml = new \SimpleXMLElement('<nfse></nfse>');
        
        if(env('APP_ENV') != "production"){
            $xml->addChild('nfse_teste', 1);
        }

        $initNf = $xml->addChild('nf');
        $initNf->addChild('valor_total', convertBRL($total_price));
        $initNf->addChild('valor_desconto', '0,00');
        $initNf->addChild('valor_inss', '0,00');

        $provider = $xml->addChild('prestador');
        $provider->addChild('cpfcnpj', env('CNPJ_NF_PRESTADOR'));
        $provider->addChild('cidade', env('CIDADE_ID_NFE'));

        $receiver = $xml->addChild('tomador');
        $receiver->addChild('tipo', $type);
        $receiver->addChild('cpfcnpj', $customer->cpf_cnpj);
        $receiver->addChild('sobrenome_nome_fantasia', removeAcento($customer->fantasy_name));
        $receiver->addChild('nome_razao_social', removeAcento($customer->razao_social));
        $receiver->addChild('numero_residencia', $customer->address_number);
        $receiver->addChild('complemento', $customer->complement);
        $receiver->addChild('estado', $customer->state);
        $receiver->addChild('cidade', $city);
        $receiver->addChild('logradouro', $customer->address);
        $receiver->addChild('email', $customer->email);
        $receiver->addChild('bairro', $customer->district);
        $receiver->addChild('cep', $customer->zipcode);
        $itens = $xml->addChild('itens');
        $list = $itens->addChild('lista');
        $list->addChild('codigo_local_prestacao_servico', env('CIDADE_ID_NFE'));
        $list->addChild('codigo_item_lista_servico',  $customer->customerNfeType->code_item_service);
        $list->addChild('codigo_nbs', $customer->customerNfeType->nbs_code);
        $list->addChild('descritivo', implode("\n", $service_detail));
        $list->addChild('aliquota_item_lista_servico', $customer->customerNfeType->aliquota_item_service);
        $list->addChild('situacao_tributaria', '00');
        $list->addChild('valor_tributavel', convertBRL($total_price));
        $list->addChild('valor_deducao', '0,00');
        $list->addChild('valor_issrf', '0,00');
        $list->addChild('tributa_municipio_prestador', 'S');


        $name_archive = $customer->cpf_cnpj . ".xml";

        $path = storage_path() . '/app/nfe/';

        if(!is_dir($path)){
            mkdir($path, 0755, true);
        }

        $archive = $path . $name_archive;


        $xml->saveXML($archive);

       $localResponse = \Response::make($xml->asXML(), 200);

       if($localResponse->getStatusCode() == 200){
            $this->logs("XML Criado com sucesso para o customer id: " . $customer->id);
            return $archive;
        }

        $this->logs("Não foi possível criar o arquivo xml para o customer id: " . $customer->ids);
        return null;
    }
    
    public function edit($id) {}
    
    public function update($data, $id) {
        $abs = AbstractRepository::class;
       return $abs::update($data, $id);
    }

    public function sendNf($nfe_id){
        $find = Nfe::where('id', $nfe_id)->with('customer')->firstOrFail();
        
        $data["month"] = $this->translateMonth(date("F"));

        $title = "Sua nota fiscal de {$data["month"]} chegou";
        $data["email"] = env('APP_ENV') == "production" ? $find->customer->email : env('NOTIFY_SEND_BOLETO');
        $data["title"] = $title;
        $data["customer"] = $find->customer;
        $data["nfe"] = $find;
        $data["services"] = $find->customer->services;
    
    
        Mail::send('Emails.nfe', $data, function($message)use($data) {
            $message->to($data["email"], $data["email"])->bcc(env('NOTIFY_SEND_BOLETO'))
                ->subject($data["title"]);
        });
        return response("Nfes enviado com sucesso", 200);
    }

    public function translateMonth($month){
        switch(strtolower($month)){
            case "january": $month = "Janeiro"; break;
            case "february": $month = "Fevereiro"; break;
            case "march": $month = "Março"; break;
            case "april": $month = "Abril"; break;
            case "may": $month = "Maio"; break;
            case "june": $month = "Junho"; break;
            case "july": $month = "Julho"; break;
            case "august": $month = "Agosto"; break;
            case "september": $month = "Setembro"; break;
            case "october": $month = "Outubro"; break;
            case "november": $month = "Novembro"; break;
            case "december": $month = "Dezembro"; break;
            default: $month = "Unknown"; break;
        }

        return $month;
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-nfe/nfe.log'),
        ])->info($message);
    }

}
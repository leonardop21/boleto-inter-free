<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Boleto;
use App\Models\Service;
use App\Models\Customer;
use App\Services\LnService;
use App\Services\NfeService;
use App\Services\InterService;
use Illuminate\Support\Collection;
use App\Interfaces\BoletoInterface;
use App\Models\Nfe;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Interfaces\NotifishInterface;


class BoletoRepository extends AbstractRepository implements BoletoInterface
{
    protected $model = Boleto::class;
    protected $abs = AbstractRepository::class;

    public function __construct(InterService $interService, NotifishInterface $notifish)
    {
        parent::__construct();
        $this->interService = $interService;
        $this->notifish = $notifish;
    }

    public function store($data)
    {   
        $customer = $this->findCustomer($data['customer_id']);

        $adjusteDueDateBaseHoliday = $this->adjusteDueDateBasedHoliday($customer, $data);
        
        if(isset($data['price_cron'])){
            $data['price'] = $data['price_cron'];
            $data['data_multa']     =   $this->checkDueDateMoraAndJuros($customer->expiration_day_boleto, $customer->data_multa, $adjusteDueDateBaseHoliday);
            $data['valor_multa']    =   $customer->valor_multa;
            $data['taxa_multa']     =   $customer->taxa_multa;
            $data['data_mora']      =   $this->checkDueDateMoraAndJuros($customer->expiration_day_boleto, $customer->data_mora, $adjusteDueDateBaseHoliday);
            $data['taxa_mora']      =   $customer->taxa_mora;
            $data['valor_mora']     =   $customer->valor_mora;
            $data['date_to_pay_after_due_date'] = $customer->date_to_pay_after_due_date;
            unset($data['price_cron']);
        }else {
            $data['price'] = floatval(str_replace(',', '.', str_replace('.', '', $data['price'])));
        }

        $data['customer_id'] = (int) $data['customer_id'];

        // Esperar 6 segundos para evitar erro de "não gerar o boleto por múltiplas requisições"
        sleep(6);

        $response = $this->generatePaymentTicket($customer, $data);
        $getContent = $response;

        if($response->getStatusCode() != 200){
            return [
                'success' => false,
                'status_code' => $response->getStatusCode(),
                'response' => $response
            ];
        }

        $decodeResponse1 = json_decode($getContent->getBody()->getContents());

        $response2 = $this->interService->getCobrança($decodeResponse1->codigoSolicitacao);

       if($response2->getStatusCode() != 200){
           return [
               'success' => false,
               'status_code' => $response2->getStatusCode(),
               'response' => $response2
           ];
       }

       $decode = json_decode($response2->getBody()->getContents());
       
        $this->model = Boleto::class;
        $data['codigo_solicitacao'] = $decode->cobranca->codigoSolicitacao;
        $data['pix_txid'] = $decode->pix->txid;
        $data['pixCopiaEcola'] = $decode->pix->pixCopiaECola;
        $data['my_number'] = $decode->cobranca->seuNumero;
        $data['our_number'] = $decode->boleto->nossoNumero;
        $data['bar_code'] = $decode->boleto->codigoBarras;
        $data['digitable_line'] = $decode->boleto->linhaDigitavel;
        $data['status'] = "EM ABERTO";

        $boleto = $this->abs::store($data);
        return [
            'success' => true,
            'data' => $boleto
        ];
    }

    /**
     * Extrai mensagens de erro da resposta da API Inter
     *
     * @param mixed $response
     * @return array
     */
    public function extractInterErrors($response)
    {
        $payload = $this->decodeInterErrorPayload($response);
        if ($payload !== null) {
            $response = $payload;
        }
        $errors = [];
        
        if (is_object($response) && isset($response->violacoes)) {
            foreach ($response->violacoes as $violacao) {
                $errors[] = trim(($violacao->propriedade ?? '') . ' ' . ($violacao->razao ?? ''));
            }
        } elseif (is_array($response) && isset($response['violacoes'])) {
            foreach ($response['violacoes'] as $violacao) {
                $errors[] = trim(($violacao['propriedade'] ?? '') . ' ' . ($violacao['razao'] ?? ''));
            }
        }
        if (is_object($response) && isset($response->detail)) {
            $errors[] = $response->detail;
        } elseif (is_array($response) && isset($response['detail'])) {
            $errors[] = $response['detail'];
        }
        if (empty($errors) && is_object($response) && isset($response->title)) {
            $errors[] = $response->title;
        } elseif (empty($errors) && is_array($response) && isset($response['title'])) {
            $errors[] = $response['title'];
        }

        return $errors;
    }

    /**
     * Tenta decodificar o payload de erro do Inter para array/objeto.
     * Aceita: string JSON, Illuminate HTTP Response, ou qualquer objeto com getBody().
     *
     * @param mixed $response
     * @return mixed|null
     */
    private function decodeInterErrorPayload($response)
    {
        // String JSON
        if (is_string($response)) {
            $decoded = json_decode($response);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        // Illuminate HTTP client response
        if (is_object($response) && method_exists($response, 'json')) {
            try {
                return $response->json();
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Response com getBody()
        if (is_object($response) && method_exists($response, 'getBody')) {
            try {
                $body = (string) $response->getBody();
                $decoded = json_decode($body);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Trata a resposta do store e retorna formato padronizado
     *
     * @param mixed $result
     * @return array
     */
    public function handleStoreResponse($result)
    {
        if (is_array($result) && isset($result['success'])) {
            return $result;
        }

        // Se for uma resposta HTTP do Laravel
        if (method_exists($result, 'getStatusCode')) {
            $statusCode = $result->getStatusCode();
            
            if ($statusCode == 200) {
                $data = json_decode($result->getContent(), true);
                return [
                    'success' => true,
                    'data' => $data
                ];
            }
            
            return [
                'success' => false,
                'status_code' => $statusCode,
                'response' => $result
            ];
        }

        return [
            'success' => true,
            'data' => $result
        ];
    }

    public function adjusteDueDateBasedHoliday($customer, $data){
        $dueDate = explode( "-", $data['due_date'])[2];
        if($customer->expiration_day_boleto != $dueDate){
            return $dueDate;
        }

        return false;
    }

    public function checkDueDateMoraAndJuros($expiration, $date, $adjusteDueDateBaseHoliday){
        // Se a data de expiração do boleto for maior que a data de juros/mora, adiciona 1 mês.
        $actualDate = date('Y-m-') . $date;

        if($expiration > $date){
            return Carbon::parse($actualDate)->addMonth()->format('Y-m-d');
        }

        if($adjusteDueDateBaseHoliday){
            return Carbon::parse(date('Y-m-' . $adjusteDueDateBaseHoliday))->addDay()->format('Y-m-d');
        }

        return date('Y-m-') . $date;
    }

    public function generatePaymentTicket($customer, $data){
        return $this->interService->generatePaymentTicket($customer, $data);
    }

    public function findCustomer($id){
        $this->model = Customer::class;
        return $this->abs::find($id);
    }

    public function cancel($id)
    {
        $find = $this->abs::find($id);

        if (!$find) {
            return [
                'success' => false,
                'status'  => 404,
                'message' => 'Boleto não encontrado',
                'data'    => null,
            ];
        }

        try {
            $response = $this->interService->cancel($find->codigo_solicitacao);
        } catch (\Throwable $e) {
            return [
                'success'   => false,
                'status'    => 500,
                'message'   => $e->getMessage(),
                'data'      => null,
                'raw_body'  => null,
                'exception' => get_class($e),
            ];
        }

        $statusCode = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null;
        $content = method_exists($response, 'getContent') ? $response->getContent() : (string) $response;
        $decode = is_string($content) ? json_decode($content) : null;

        $success = $decode && isset($decode->status) && (int) $decode->status === 202;
        if ($success) {
            $this->model = Boleto::class;
            $this->abs::update(["status" => "CANCELADO"], $id);
        }

        // Mensagem: sucesso usa message do Inter; erro usa formato Inter (detail, title, violacoes)
        if ($success && $decode && isset($decode->message)) {
            $message = $decode->message;
        } elseif (!$success && ($decode || $content)) {
            $interErrors = $this->extractInterErrors($decode ?: $content);
            $message = !empty($interErrors) ? implode('; ', $interErrors) : ($decode->detail ?? $decode->title ?? 'Erro ao cancelar boleto');
        } else {
            $message = $success ? 'Boleto cancelado com sucesso' : 'Erro ao cancelar boleto';
        }

        $payload = [
            'success'  => $success,
            'status'   => $decode->status ?? $statusCode ?? 500,
            'message'  => $message,
            'data'     => $decode,
            'raw_body' => $content,
        ];
        if ($decode && isset($decode->raw_body)) {
            $payload['raw_body'] = $decode->raw_body;
        }
        return $payload;
    }

    public function withPaginate($with, $customer_id){
        $page = request()->get('page', 1);

        if($customer_id){
            if(!Cache::tags($this->tableCache)->has('_page_get_boleto_' . $customer_id . "_" . $page)){
                $records = $this->model::where('customer_id', $customer_id)->with('customer')->orderBy('id', 'desc')->paginate(20);
                Cache::tags([$this->tableCache])->put('_page_get_boleto_' .  $customer_id . "_" . $page, $records, $this->time_cache);
            }
            return Cache::tags([$this->tableCache])->get('_page_get_boleto_' .  $customer_id . "_" . $page);
        }

        if(!Cache::tags($this->tableCache)->has('_page_with_' . $page)){
            $records = $this->model::with($with)->orderBy('id', 'desc')->paginate(20);
            Cache::tags([$this->tableCache])->put('_page_with_' . $page, $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_page_with_' . $page);
    }

    public function getPdf($find){
        $find = Boleto::where('id', $find)->with('customer')->firstOrFail();
        
        $exists = $this->findPath($find);

        if($exists) return $exists;
        
        $response = $this->interService->getPdf($find->codigo_solicitacao);

        if($response->getStatusCode() == 200){
            $data['path'] = $response->original['path'];
            $data['boleto'] = $response->original['boleto'];
            $response = $this->abs::update($data, $find->id);
            return response()->json(["url_download" => $this->findPath($response), "name_boleto" => $this->namePdf($find->customer)], 200);
        }

        return $response;
    }

    public function findPath($find){
        $boleto = base_path() . "/storage/app/" . $find->path . $find->boleto;

        if(isset($find->path) && isset($find->boleto) && file_exists($boleto)){
            return response()->json(["url_download" => $boleto, "name_boleto" => $this->namePdf($find->customer)], 200);
        }

        return false;
    }

    public function namePdf($customer){
        return $customer->fantasy_name ? $customer->fantasy_name : $customer->name;
    }

    public function sendBoleto($boleto_id, $resend = null){
        $find = Boleto::where('id', $boleto_id)->with('customer')->firstOrFail();
        
        $permission = $this->verifyPermissionSendBoleto($find, 'email_notify');

        if(!$permission){
            return response("O cliente está configurado para não receber boletos por e-mail, verifique o cadastro do cliente", 401);
        }

        $exists = $this->findPath($permission);

        if(!$exists){
            return response("Boleto não enviado, o arquivo não existe", 404);
        }

        $data["month"] = $this->translateMonth(date("F"));

        if($resend  == "vencido"){
            $title = "Fatura de {$data["month"]} vencida, evite a suspensão dos seus serviços";
        }elseif($resend){
            $title = "Lembrete: sua fatura de {$data["month"]} vence hoje";
        } else {
           $title = "Sua fatura de {$data["month"]} chegou";
        }

        $data['due_date'] = Carbon::parse($find->due_date)->format('d/m/Y');
        $data['data_multa'] = Carbon::parse($find->data_multa)->format('d/m/Y');
        $data['date_mora'] = Carbon::parse($find->date_mora)->format('d/m/Y');
        $data["email"] = $find->customer->email;
        $data["title"] = $title;
        $data["customer"] = $find->customer;
        $data["boleto"] = $find;
        $data["services"] = $find->customer->services;
 
        $file = $exists->original['url_download'];
  
        Mail::send('Emails.boleto', $data, function($message)use($data, $file) {
            $message->to($data["email"], $data["email"])->bcc(env('NOTIFY_SEND_BOLETO'))
                ->subject($data["title"]);
 
            $message->attach($file);
        });

        return response("boleto enviado com sucesso", 200);
    }

    public function sendAvulseBoleto($id){
        $find = Boleto::where('id', $id)->with('customer')->firstOrFail();
        
        $permission = $this->verifyPermissionSendBoleto($find, 'email_notify');

        if(!$permission){
            return response("O cliente está configurado para não receber boletos por e-mail, verifique o cadastro do cliente", 401);
        }

        $exists = $this->findPath($permission);

        if(!$exists){
            return response("Boleto não enviado, o arquivo não existe", 404);
        }

        $date = explode("-", $find->due_date);
        $data['due_date'] = explode("-", $find->due_date)[2];
        $data["month"] = $this->convertNumberDateToString($date[1]);
        $data["email"] = $find->customer->email;
        $data["title"] = "Seu boleto de {$data['month']} está disponível";
        $data["customer"] = $find->customer;
        $data["boleto"] = $find;

        $file = $exists->original['url_download'];
  
        Mail::send('Emails.boleto-avulso', $data, function($message)use($data, $file) {
            $message->to($data["email"], $data["email"])->bcc(env('NOTIFY_SEND_BOLETO'))
                ->subject($data["title"]);
 
            $message->attach($file);
        });

        return response("boleto enviado com sucesso", 200);
    }

    public function verifyPermissionSendBoleto($find, $type){
       return $find->customer->$type == 1 ? $find : false;
    }

    public function failedSendBoleto($failed_id){
        $data['customers'] = Boleto::whereIn('id', $failed_id)->with('customer')->get();
        $data['email'] = env('NOTIFY_SEND_BOLETO');
        $data['title'] = "Falha ao enviar boletos";

        Mail::send('Emails.failed-boleto', $data, function($message)use($data) {
            $message->to($data["email"], $data["email"])
                ->subject($data["title"]);
         });

         return response("ok", 200);
    }
    
    public function successSendBoleto($success_id){
        $data['customers'] = Boleto::whereIn('id', $success_id)->with('customer')->get();
        $data['failed_id'] = $success_id;
        $data['email'] = env('NOTIFY_SEND_BOLETO');
        $data['title'] = "Boletos enviados com sucesso";

        Mail::send('Emails.success-boleto', $data, function($message)use($data) {
            $message->to($data["email"], $data["email"])
                ->subject($data["title"]);
         });

         return response("ok", 200);
    }

    public function getValueBoletoMonth($status){
        $date = new Carbon;
        $start = $date->startOfMonth()->format('Y-m-d H:i:s'); // 2000-02-01 00:00:00
        $end = $date->endOfMonth()->format('Y-m-d H:i:s'); // 2000-02-29 23:59:59
        $month = $date->month;
        

        if(!Cache::tags($this->tableCache)->has('valueBoletoPaidMonth_' . $status . $month)){
            $records = Boleto::where('status', $status)->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->get();

            $total_price = 0;
            foreach($records as $record){
                $value = $status == "PAGO" ? $record->price_paid : $record->price;
                $total_price += number_format($value, 2, '.', '');
            }

            Cache::tags([$this->tableCache])->put('valueBoletoPaidMonth_' . $status . $month, $total_price, $this->time_cache);
        }
        
        return Cache::tags([$this->tableCache])->get('valueBoletoPaidMonth_' . $status . $month);
    }

    public function getDueDateBoletoResend(){
        return Boleto::where('status', "EM ABERTO")->where('due_date', '=', date('Y-m-d'))
        ->get();
    }
   
    public function getDueDateBoleto2Resend(){
        $date = new Carbon;

        $start = $date->startOfMonth()->format('Y-m-d H:i:s');
        $end = $date->endOfMonth()->format('Y-m-d H:i:s');

        return Boleto::where('status', "EM ABERTO")->where([
            ['created_at', '>=', $start],
            ['created_at', '<=', $end],
            ['due_date', '<', date('Y-m-d')],
        ])
        ->get();
    }

    public function createWebhook(){
        try {
            $response = $this->interService->createWebhook();
            $decoded = json_decode($response->getContent());
            
            return [
                'success' => $response->getStatusCode() == 204,
                'status' => $response->getStatusCode(),
                'message' => $decoded->message ?? ($response->getStatusCode() == 204 ? 'Webhook criado com sucesso' : 'Erro ao criar webhook'),
                'data' => $decoded
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erro ao criar webhook: ' . $e->getMessage()
            ];
        }
    }
    
    public function getWebhook(){
        try {
            return $this->interService->getWebHook();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    public function callbackInter($callback){

        Storage::disk('local')->put('callback.json', json_encode($callback));

        $payload = $this->normalizeInterCallbackPayload($callback);

        // Localiza o boleto pelo nossoNumero (preferencial) ou codigoSolicitacao (fallback)
        if (!empty($payload['nossoNumero'])) {
            $boleto = Boleto::where('our_number', $payload['nossoNumero'])->with('customer')->firstOrFail();
        } elseif (!empty($payload['codigoSolicitacao'])) {
            $boleto = Boleto::where('codigo_solicitacao', $payload['codigoSolicitacao'])->with('customer')->firstOrFail();
        } else {
            throw new \InvalidArgumentException('Callback Inter inválido: não possui nossoNumero/codigoSolicitacao.');
        }

        $data = $this->mapInterCallbackToBoletoUpdate($payload);
        $boleto->update($data);

        $valorRecebido = (float) ($payload['valorTotalRecebimento'] ?? $payload['valorTotalRecebido'] ?? 0);
        $valorNominal = (float) ($payload['valorNominal'] ?? 0);
        $diferenca = $valorRecebido - $valorNominal;

        if(!empty(env('NOTIFY_SEND_BOLETO_WHATSAPP'))){
            $origem = strtoupper((string) ($payload['origemRecebimento'] ?? ''));
            $message = "*Callback banco inter* \n\n Cliente: *{$boleto['customer']['name']}* \n\n Origem: *{$origem}* \n\n Valor gerado: *R$ {$valorNominal}* \n\n Valor pago: *R$ {$valorRecebido}* \n\n Diferença: *R$ {$diferenca}* \n\n Status: *{$data['status']}*";
            $this->notifish->sendNotificationMessage($message);
        }

        \Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/callback/callback.log'),
        ])->info($callback);

        $this->checkGenerateOtherNfe([$payload], $boleto, $data['status']);

        return response()->json("ok", 200);
    }

    /**
     * Normaliza o payload do callback do Inter.
     * Aceita tanto:
     * - array direto (associativo)
     * - lista de callbacks: [ { ... } ]
     */
    public function normalizeInterCallbackPayload($callback): array
    {
        if (is_array($callback) && array_is_list($callback)) {
            $callback = $callback[0] ?? [];
        }

        if (is_object($callback)) {
            $callback = (array) $callback;
        }

        return is_array($callback) ? $callback : [];
    }

    /**
     * Mapeia o callback do Inter para campos do boleto.
     *
     * Regras importantes:
     * - `situacao=RECEBIDO` (geralmente `origemRecebimento=PIX`) deve marcar o boleto como PAGO
     * - `origemRecebimento` pode ser `BOLETO` ou `PIX`
     * - o campo de valor pode vir como `valorTotalRecebimento` (antigo) ou `valorTotalRecebido` (novo)
     */
    public function mapInterCallbackToBoletoUpdate(array $payload): array
    {
        $rawSituacao = strtoupper((string) ($payload['situacao'] ?? ''));
        $rawSituacao = $rawSituacao === 'EMABERTO' ? 'EM ABERTO' : $rawSituacao;

        $isPaid = in_array($rawSituacao, ['PAGO', 'RECEBIDO', 'LIQUIDADO'], true);
        $isCancelled = in_array($rawSituacao, ['CANCELADO', 'CANCELADA'], true);

        // Normaliza status final do boleto
        if ($isPaid) {
            $status = 'PAGO';
        } elseif ($isCancelled) {
            $status = 'CANCELADO';
        } elseif ($rawSituacao) {
            $status = $rawSituacao;
        } else {
            $status = 'EM ABERTO';
        }

        $dataHora = (string) ($payload['dataHoraSituacao'] ?? '');
        $date = $dataHora ? date('Y-m-d', strtotime($dataHora)) : date('Y-m-d');

        $valorRecebido = (float) ($payload['valorTotalRecebimento'] ?? $payload['valorTotalRecebido'] ?? 0);

        $data = ['status' => $status];

        if ($status === 'PAGO') {
            $data['payment_date'] = $date;
            $data['price_paid'] = $valorRecebido;
        } elseif ($status === 'CANCELADO') {
            $data['cancellation_date'] = $date;
        }

        return $data;
    }

    public function convertNumberDateToString($month){
        switch(strtolower($month)){
            case "01": $month = "Janeiro"; break;
            case "02": $month = "Fevereiro"; break;
            case "03": $month = "Março"; break;
            case "04": $month = "Abril"; break;
            case "05": $month = "Maio"; break;
            case "06": $month = "Junho"; break;
            case "07": $month = "Julho"; break;
            case "08": $month = "Agosto"; break;
            case "09": $month = "Setembro"; break;
            case "10": $month = "Outubro"; break;
            case "11": $month = "Novembro"; break;
            case "12": $month = "Dezembro"; break;
            default: $month = "Unknown"; break;
        }

        return $month;
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

    public function checkGenerateOtherNfe($callback, $boleto, $situacao){
        if($situacao == "PAGO" && $callback['valorTotalRecebimento'] > $callback['valorNominal']){
            $diferencaValor = (Float) $callback['valorTotalRecebimento'] - (Float) $callback['valorNominal'];
            

            $checkExistNfeBoletos = $this->checkExistNfeBoleto($boleto);

            if($checkExistNfeBoletos){
                $message = "*Já existe uma nota fiscal de juros e multa gerada para o customer {$boleto->customer->name}, abortando*";
                $this->logs($message);
                $this->notifish->sendNotificationMessage($message);
                return;
            }

           // Recria o objeto Customer
            $customer = new Customer;
            $customer = $boleto->customer;
            $customer->boleto_id = $boleto->id;

            // Recria a coleção de objetos Service
            $services = new Collection();

            // Cria um novo objeto Service com nome e preço atualizados
            $newService = new Service();
            $newService->name = "Juros+Mora";
            $newService->price = $diferencaValor;

            // Adiciona o objeto Service à coleção de serviços
            $services->push($newService);

            // Atribui a coleção de serviços à relação services no objeto Customer
            $customer->services = $services;
            $lnService = new LnService();
            $customerRepository = new CustomerRepository();
            $nfeService = new NfeService($lnService);

            $nfe = new NfeRepository($customerRepository, $nfeService);

            // Cria a NFe
            $response = $nfe->store($customer);

            // Envia por e-mail
            $nfe->sendNf($response->id);

            $message = "*Nova nota fiscal gerada devido a atraso de pagamento para o cliente {$customer->name}*\n\nO cliente acima pagou após o vencimento e houve incidência de juros+mora, uma nova nota fiscal no valor de R$ {$diferencaValor} foi gerada\n\nConfira a nota em {$response->link_nfse} \n\n";
            $this->logs($message);
            $this->notifish->sendNotificationMessage($message);
        }
        return;
    }

    private function checkExistNfeBoleto($boleto){
        $this->model = Nfe::class;
        return Nfe::where('boleto_id', $boleto->id)->first() ? true : false;
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-nfe/nfe.log'),
        ])->info($message);
    }
}
<?php
namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Pix;
use Illuminate\Support\Str;
use App\Services\InterService;
use App\Events\PaymentReceived;
use App\Interfaces\PixInterface;
use Illuminate\Support\Facades\Mail;
use App\Interfaces\NotifishInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Repositories\AbstractRepository;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class PixRepository extends AbstractRepository implements PixInterface {

    protected $model = Pix::class;
    protected $abs = AbstractRepository::class;

    public function __construct(InterService $interSerivce, NotifishInterface $notifish)
    {
        parent::__construct();
        $this->interService = $interSerivce;
        $this->notifish = $notifish;
    }

    public function whereFirstOrFail($where, $search){
        if(!Cache::tags($this->tableCache)->has('_record_id_' . $search)){
            $records = Pix::where($where, $search)->with("customer")->firstOrFail();
            Cache::tags([$this->tableCache])->put('_record_id_' . $search, $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_record_id_' . $search);
    }

    public function storePix($customer, $data, $geracaoAutomtica)
    {
        $originalData = $data;
        $due_date = $this->calculateSecondsDueDate($data['due_date'], $geracaoAutomtica);
        $typeOfPerson = $customer->type_of_person == 'juridica' ? 'cnpj' : 'cpf' ;

        $formated_price = sprintf("%.2f", $data['price']);

        $data = [
            "calendario" => ["expiracao" => $due_date],
            "devedor" => [
                $typeOfPerson => $customer->cpf_cnpj,
                "nome" => $customer->fantasy_name
            ],
            "tipoCob" => [
                "tipoCob" => "cob"
            ],
            "valor" => [
                "original" => $formated_price,
                "modalidadeAlteracao" => 0
            ],
            "solicitacaoPagador" => "{$customer->fantasy_name} - {$customer->name}",
            "chave" => preg_replace("/[^0-9]/", "", env('CHAVE_PIX'))
        ];


        if($geracaoAutomtica){
            $services = [];
            foreach($customer->services as $key => $service){
                $services[$key]['service_name'] = $service->name;
                $services[$key]['price'] = convertBRL($service->price);
            }
            $services = json_encode($services);
        }else {
            $services = null;
        }

        $response = $this->interService->generatePaymentPix($data);

        $statusCode = $response->getStatusCode();

        if ($statusCode != 201) {
            $rawBody = method_exists($response, 'getBody') ? (string) $response->getBody() : (method_exists($response, 'getContent') ? (string) $response->getContent() : '');
            $decode = is_string($rawBody) ? json_decode($rawBody) : null;
            $interErrors = $this->extractInterErrors($decode ?: $rawBody);
            $message = !empty($interErrors) ? implode('; ', $interErrors) : (($decode && (isset($decode->detail) || isset($decode->title))) ? ($decode->detail ?? $decode->title) : 'Erro ao gerar PIX.');
            return [
                'success'      => false,
                'status_code'  => $statusCode,
                'message'      => $message,
                'response'     => $response,
                'raw_body'     => $rawBody,
                'data'         => $decode,
            ];
        }

        // 201: sucesso – resposta no formato da API (calendario, txid, loc, location, status, valor, chave, etc.)
        $body = method_exists($response, 'json') ? $response->json() : json_decode(method_exists($response, 'getBody') ? $response->getBody()->getContents() : '{}', true);
        if (!is_array($body)) {
            $body = [];
        }
        $loc = $body['loc'] ?? [];
        $calendario = $body['calendario'] ?? [];
        $valor = $body['valor'] ?? [];

        $data = [
            "customer_id"       => $customer->id,
            "my_number"         => $originalData['my_number'],
            "key"               => $body['chave'] ?? (string) ($body['key'] ?? ''),
            "pix_copy_and_paste"=> $body['pixCopiaECola'] ?? $body['pix_copy_and_paste'] ?? '',
            "loc_id"            => (string) ($loc['id'] ?? $body['loc_id'] ?? '0'),
            "location"          => $loc['location'] ?? $body['location'] ?? '',
            "tipoCob"           => $loc['tipoCob'] ?? $loc['tipo_cob'] ?? 'cob',
            "create_date_pix"   => $calendario['criacao'] ?? $body['create_date_pix'] ?? now()->toIso8601String(),
            "expiration_pix"    => $calendario['expiracao'] ?? $body['expiration_pix'] ?? 86400,
            "due_date"          => $this->normalizeDueDateForStore($originalData['due_date'], $geracaoAutomtica),
            "status"            => $body['status'] ?? 'ATIVA',
            "original_price"    => $valor['original'] ?? $body['original_price'] ?? 0,
            "amount_paid"       => null,
            "modalidadeAlteracao" => (string) ($valor['modalidadeAlteracao'] ?? $body['modalidadeAlteracao'] ?? 0),
            "txid"              => $body['txid'] ?? '',
            "revision"           => $body['revisao'] ?? $body['revision'] ?? 0,
            "uuid"               => Str::uuid(),
            "services"           => $services,
            "path"               => $this->path(),
        ];


        $pix = $this->abs::store($data);
        $this->generateQRcode($pix->id);
        
        return [
            'success' => true,
            'data' => $pix
        ];
    }

    /**
     * Extrai mensagens de erro da resposta da API PIX/Inter (400, 403, 503).
     * Formato: title, detail, violacoes (propriedade + razao).
     *
     * @param mixed $response Resposta HTTP (getBody/getContent), string JSON ou objeto decodificado
     * @return array
     */
    public function extractInterErrors($response)
    {
        $payload = $this->decodePixErrorPayload($response);
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
     * Decodifica o body de erro da API PIX/Inter (string JSON ou resposta com getBody/getContent).
     *
     * @param mixed $response
     * @return object|array|null
     */
    private function decodePixErrorPayload($response)
    {
        if ($response === null) {
            return null;
        }
        if (is_string($response)) {
            $decoded = json_decode($response);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        if (is_object($response) && method_exists($response, 'getBody')) {
            try {
                $body = (string) $response->getBody();
                $decoded = json_decode($body);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            } catch (\Throwable $e) {
                return null;
            }
        }
        if (is_object($response) && method_exists($response, 'getContent')) {
            try {
                $body = (string) $response->getContent();
                $decoded = json_decode($body);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Regenera um novo PIX quando o anterior está vencido
     *
     * @param int $pixId
     * @return array
     */
    public function regeneratePixByCustomer($pixId)
    {
        $record = $this->findByUuid($pixId);
        
        if (!$record) {
            return [
                'success' => false,
                'message' => 'PIX não encontrado'
            ];
        }

        $price = (float) $record->original_price;
        
        $data = [
            "customer_id" => $record->customer->id,
            "price" => $price + 12,
            "description" => $record->description,
            "due_date" => date('Y/m/d', strtotime('+1 day')),
        ];

        $result = $this->storePix($record->customer, $data, false);
        
        if (!$result['success']) {
            return $result;
        }

        // Atualiza o pix antigo
        $this->update([
            'status' => 'VENCIDO', 
            'replace_for_uuid' => $result['data']->uuid
        ], $record->id);

        // Atualiza o novo pix copiando os serviços do antigo
        $this->update([
            'services' => $record->services
        ], $result['data']->id);

        return [
            'success' => true,
            'data' => $result['data']
        ];
    }

    public function findByUuid($uuid)
    {
        return Pix::where('uuid', $uuid)->with('customer')->first();
    }

    /**
     * Normaliza due_date para gravar no banco: aceita Y-m-d (resolvido pelo comando) ou dia (1-31).
     */
    private function normalizeDueDateForStore($due_date, $geracaoAutomtica)
    {
        if (!$geracaoAutomtica) {
            return $due_date;
        }
        if (is_string($due_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date)) {
            return $due_date;
        }
        return date('Y-m-') . $due_date;
    }

    /**
     * Calcula os segundos até a data de vencimento (para expiração do PIX na API).
     * Quando geração automática: aceita dia (ex.: 10) ou data completa Y-m-d (ex.: 2026-02-10).
     * Mesma lógica de data do boleto: dia passado → próximo mês já vem resolvido no comando.
     */
    public function calculateSecondsDueDate($day_due, $geracaoAutomtica)
    {
        if ($geracaoAutomtica && is_string($day_due) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $day_due)) {
            $date = Carbon::parse($day_due)->format('d/m/Y');
        } elseif ($geracaoAutomtica) {
            $date = $day_due . date('/m/Y');
        } else {
            $date = date('d/m/Y', strtotime($day_due));
        }
        $actual_date = Carbon::now();
        $due_date = Carbon::createFromFormat('d/m/Y', $date)->endOfDay();

        if ($due_date->lt($actual_date)) {
            return 86400;
        }
        return $due_date->diffInSeconds($actual_date);
    }

    public function generateQRcode($id = null, $uuid = null){
        
        $find = Pix::when($id, function ($query, $id) {
                        return $query->where('id', $id);
                    })->when($uuid, function ($query, $uuid) {
                    return $query->where('uuid', $uuid);
                })->first();

        $path =  storage_path() . $this->path() . "{$find->uuid}.png";
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        QrCode::format('png')
        ->merge(public_path('logo_pix.png'), 0.35, true)
        ->size(300)
        ->errorCorrection('H')
        ->generate($find->pix_copy_and_paste, $path);
        
        if(file_exists($path)){
           return response("QRCODE Criado com sucesso", 200);
        }

        return response("Falha ao criar QRCODE", 500);
    }

    public function withPaginate($with, $customer_id){
        $page = request()->get('page', 1);

        if($customer_id){
            if(!Cache::tags($this->tableCache)->has('_page_get_pix_' . $customer_id . "_" . $page)){
                $records = $this->model::where('customer_id', $customer_id)->with('customer')->orderBy('id', 'desc')->paginate(20);
                Cache::tags([$this->tableCache])->put('_page_get_pix_' .  $customer_id . "_" . $page, $records, $this->time_cache);
            }
            return Cache::tags([$this->tableCache])->get('_page_get_pix_' .  $customer_id . "_" . $page);
        }

        if(!Cache::tags($this->tableCache)->has('_page_with_' . $page)){
            $records = $this->model::with($with)->orderBy('status', 'asc')->orderBy('id', 'desc')->paginate(20);
            Cache::tags([$this->tableCache])->put('_page_with_' . $page, $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_page_with_' . $page);
    }

    public function failedSendEmail(){
    }

    public function path(){
        return "/app/pixes/" . date('Y/m/');
    }

    public function cancel($id){
        
    }

    public function createWebhook(){
        try {
            $response = $this->interService->createWebhook(true);
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
            return $this->interService->getWebHook(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    public function callbackInter($callback){

        Storage::disk('local')->put('callback-pix.json', json_encode($callback));


        $callback = $callback["pix"][0];
        $pix = Pix::where('txid', $callback['txid'])->firstOrFail();

        $pix->update(
            ["status" => "CONCLUIDA",
             "datetime_situation" => $callback['horario'],
             "amount_paid" => $callback['valor'],
             "payment_date" => date('Y-m-d', strtotime($callback['horario']))
            ]
        );

        \Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/callback/callbackPix.log'),
        ])->info($callback);

        $msg = ['uuid' => $pix->uuid, 
                'redirect_url' => route('frontend.get.recibo.pix', ['uuid' => $pix->uuid])
               ];

        event(new PaymentReceived($msg));

        if(!empty(env('NOTIFY_SEND_BOLETO_WHATSAPP'))){
            $message = "*Callback banco inter PIX* \n\n Cliente: *{$pix['customer']['name']}* \n\n Valor gerado: *R$ {$callback['valor']}* \n\n Valor pago: *{$callback['valor']}*";
            $this->notifish->sendNotificationMessage($message);
        }

        return ['data' => $callback, 'record' => $pix];
    }

    public function getDueDatePixResend(){
        return Pix::where('status', "ATIVA")->where('due_date', '=', date('Y-m-d'))
        ->get();
    }

    public function getDueDateExpiredPixResend()
    {
        $date = new Carbon;

        $start = $date->startOfMonth()->format('Y-m-d H:i:s');
        $end = $date->endOfMonth()->format('Y-m-d H:i:s');

        return Pix::where('status', "ATIVA")->where([
            ['created_at', '>=', $start],
            ['created_at', '<=', $end],
            ['due_date', '<', date('Y-m-d')],
        ])->whereNull('replace_for_uuid')->get();
    }

    public function failedSendPix($failed_id){
        $data['customers'] = Pix::whereIn('id', $failed_id)->with('customer')->get();
        $data['email'] = env('NOTIFY_SEND_BOLETO');
        $data['title'] = "Falha ao enviar Pix";

        Mail::send('Emails.failed-boleto', $data, function($message)use($data) {
            $message->to($data["email"], $data["email"])
                ->subject($data["title"]);
         });

         return response("ok", 200);
    }
    
    public function successSendPix($success_id){
        $data['customers'] = Pix::whereIn('id', $success_id)->with('customer')->get();
        $data['failed_id'] = $success_id;
        $data['email'] = env('NOTIFY_SEND_BOLETO');
        $data['title'] = "Pix enviados com sucesso";

        Mail::send('Emails.success-boleto', $data, function($message)use($data) {
            $message->to($data["email"], $data["email"])
                ->subject($data["title"]);
         });

        return response("ok", 200);
    }

    public function getValuePixMonth($status){
        $date = new Carbon;
        $start = $date->startOfMonth()->format('Y-m-d H:i:s'); // 2000-02-01 00:00:00
        $end = $date->endOfMonth()->format('Y-m-d H:i:s'); // 2000-02-29 23:59:59
        $month = $date->month;


        if(!Cache::tags($this->tableCache)->has('valuePixPaidMonth_' . $status . $month)){
            $records = Pix::where('status', $status)->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->get();

            $total_price = 0;
            foreach($records as $record){
                $value = $status == "concluida" ? $record->amount_paid : $record->original_price;
                $total_price += number_format($value, 2, '.', '');
            }

            Cache::tags([$this->tableCache])->put('valuePixPaidMonth_' . $status . $month, $total_price, $this->time_cache);
        }

        return Cache::tags([$this->tableCache])->get('valuePixPaidMonth_' . $status . $month);
    }
}
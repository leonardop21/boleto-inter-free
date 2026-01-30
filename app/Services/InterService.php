<?php

namespace App\Services;

use App\Models\Customer;
use App\Exceptions\InterCertificateException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class InterService
{
    private $lnService;
    private $url;
    private $client_id;
    private $client_secret;
    private $client_scope;

    public function __construct(LnService $lnService){
        $this->lnService = $lnService;
        $this->url = env('INTER_BASE_URL');
        $this->client_id = env('INTER_CLIENT_ID');
        $this->client_secret = env('INTER_CLIENT_SECRET');
        $this->client_scope = env('INTER_CLIENT_SCOPE');
    }

    private function certKey(){
        $certPath = $this->normalizePath(env('INTER_PATH_CRT'));
        $keyPath  = $this->normalizePath(env('INTER_PATH_KEY'));
        
        return [
            'cert' => [$certPath, ''],
            'ssl_key' => [$keyPath]
        ];
    }

    /**
     * Garante que os arquivos de certificado/ssl existem.
     *
     * @throws InterCertificateException
     */
    private function ensureValidCert(): void
    {
        $certPath = $this->normalizePath(env('INTER_PATH_CRT'));
        $keyPath  = $this->normalizePath(env('INTER_PATH_KEY'));

        if (!$certPath || !$keyPath) {
            $this->logs("Certificado Banco Inter: caminhos não configurados. Verifique INTER_PATH_CRT e INTER_PATH_KEY no .env");
            throw new InterCertificateException(
                'Certificado SSL do Banco Inter não foi configurado. Verifique INTER_PATH_CRT e INTER_PATH_KEY no .env.'
            );
        }

        if (!file_exists($certPath)) {
            $this->logs("Certificado Banco Inter não encontrado (INTER_PATH_CRT). Arquivo não existe.");
            throw new InterCertificateException(
                "Certificado SSL não encontrado. Verifique INTER_PATH_CRT no .env e se o arquivo existe."
            );
        }

        if (!file_exists($keyPath)) {
            $this->logs("Chave SSL do Banco Inter não encontrada (INTER_PATH_KEY). Arquivo não existe.");
            throw new InterCertificateException(
                "Chave SSL não encontrada. Verifique INTER_PATH_KEY no .env e se o arquivo existe."
            );
        }
    }

    /**
     * Normaliza o caminho do arquivo removendo aspas e convertendo barras.
     *
     * @param string|null $path
     * @return string|null
     */
    private function normalizePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Remove aspas do início e fim se houver
        $path = trim($path, '"\'');
        
        // Converte barras invertidas para barras normais (Windows)
        $path = str_replace('\\', '/', $path);
        
        // Remove barras duplas
        $path = str_replace('//', '/', $path);
        
        // Tenta obter o caminho absoluto real
        if (file_exists($path)) {
            return realpath($path) ?: $path;
        }

        return $path;
    }

    private function checkOAuthLogin(){
        if(Cache::has('token_inter_oauth')){
            return Cache::get('token_inter_oauth');
        }
        
        return $this->login();
    }

    private function login(){
        $data = [
            'grant_type' => 'client_credentials',
            'scope' => $this->client_scope,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ];

        $headers = [
             'Accept' => 'application/json',
             "Content-Type" => "application/x-www-form-urlencoded"
            ];
  
        $response = $this->lnService->post(
            $this->url . 'oauth/v2/token', 
            $headers, 
            $data, 
            $this->certKey(), 
            true
        );

        if($response->getStatusCode() == 200){
            $response = json_decode($response->getBody()->getContents());
            Cache::put('token_inter_oauth', $response->access_token, $response->expires_in - 100);
            return Cache::get('token_inter_oauth');
        }

        return $response->getStatusCode();
        
    }

    public function generatePaymentTicket($customer, $boleto){

        $oauth = $this->checkOAuthLogin();

        $data = [
            "valorAbatimento" => 0,
                "pagador" => [
                    "cpfCnpj" => $customer->cpf_cnpj,
                    "tipoPessoa" => strtoupper($customer->type_of_person),
                    "nome" => $customer->name,
                    "endereco" => $customer->address,
                    "numero" => $customer->address_number,
                    "complemento" => $customer->complement,
                    "bairro" => $customer->district,
                    "cidade" => $customer->city,
                    "uf" => $customer->state,
                    "cep" => $customer->zipcode,
                    "email" => $customer->email,
                    "ddd" => substr($customer->phone, 0,2),
                    "telefone" => substr($customer->phone, 2)
                ],
                "multa" => [
                    "codigo" => $customer->code_multa,
                    "data" => $boleto['data_multa'],
                    "valor" => $boleto['valor_multa'],
                    "taxa" => $boleto['taxa_multa']
                ],
                "mora" => [
                    "codigo" => $customer->code_mora,
                    "data" => $boleto['data_mora'],
                    "taxa" => $boleto['taxa_mora'],
                    "valor" => $boleto['valor_mora']
                ],

                "formasRecebimento" => ['BOLETO', 'PIX'],

                "seuNumero" => $boleto['my_number'],
                "valorNominal" => $boleto['price'],
                "dataVencimento" => $boleto['due_date'],
                "numDiasAgenda" => $boleto['date_to_pay_after_due_date']
            ];

        $headers =  [
            'Authorization' => 'Bearer ' . $oauth,
            "Content-Type" => "application/json"
        ];


       $response = $this->lnService->post(
            $this->url . 'cobranca/v3/cobrancas',
            $headers, 
            $data,
            $this->certKey(), 
            false);

        return $response;
    }

    public function getPdf($codigoSolicitacao){

        $headers =  [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            "Content-Type" => "application/json"
        ];

        $response = $this->lnService->get(
            $this->url . "cobranca/v3/cobrancas/{$codigoSolicitacao}/pdf", 
            $headers,
            $this->certKey()
        );

        if($response->getStatusCode() == 200){
            $body = $response->getBody();
            $status = json_decode($body);
            $path = "boleto/pdf/" . date('Y/m/');
            $name = md5($codigoSolicitacao . date('D-H:i:s')) . '.pdf';
            Storage::disk('local')->put($path . $name, base64_decode($status->pdf));
            return response()->json(["status" => 200, "path" => $path, "boleto" => $name]);
        }

        return $response;

    }

    public function cancel($codigoSolicitacao){
        $oauth = $this->checkOAuthLogin();

        $data = ["motivoCancelamento" => "APEDIDODOCLIENTE"];

        $headers =  [
            'Authorization' => 'Bearer ' . $oauth,
            "Content-Type" => "application/json"
        ];

       $response = $this->lnService->post(
            $this->url . "cobranca/v3/cobrancas/{$codigoSolicitacao}/cancelar",
            $headers,
            $data,
            $this->certKey(),
            false);

        $prefixLog = "cancelar-boleto";
        $statusCode = $response->getStatusCode();
        $rawBody = (string) $response->getBody();
        $this->logs("{$prefixLog} {$statusCode} - {$rawBody}\n\n");

        if ($statusCode == 202) {
            return response()->json([
                'message' => "Boleto cancelado. Confira no aplicativo se o mesmo foi cancelado",
                'status' => 202
            ], 202);
        }

        return response()->json([
            'message' => "Ocorreu um problema ao cancelar o boleto, confira mais detalhes no log",
            'status' => $statusCode,
            'raw_body' => $rawBody
        ], $statusCode >= 400 ? $statusCode : 500);
    }

    public function getSaldo(){
        // Garante que o certificado está configurado; senão lança InterCertificateException
        $this->ensureValidCert();

        $headers =  [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            "Content-Type" => "application/json"
        ];

        $response = $this->lnService->get(
            $this->url . "banking/v3/saldo", 
            $headers,
            $this->certKey()
        );

        if($response->getStatusCode() == 200){
            return $response->getBody();
        }

        $this->logs("Falha ao buscar saldo Inter. HTTP Code: " . $response->getStatusCode());

        return 0;
    }

    public function createWebhook($pix = null){
        $headers =  [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            "Content-Type" => "application/json"
        ];

        if($pix){
            $pix =  preg_replace('/[.\-\s]/', '', env('CHAVE_PIX'));
            $weboohUrl = env('INTER_WEBHOOK_URL_PIX');
            $url = "pix/v3/webhook/{$pix}";
        }else {
            $weboohUrl = env('INTER_WEBHOOK_URL');
            $url = "cobranca/v3/boletos/webhook";
        }


        $data = [
            "webhookUrl" => $weboohUrl
        ];


        $response = $this->lnService->put(
            $this->url . $url,
            $headers, 
            $data,
            $this->certKey(), 
            false);
        if($response->getStatusCode() == 204){
            return response()->json([
                'message' => "Webhook criado com sucesso",
                'status' => 204
                ],204
            );
        }

        return response()->json([
            'message' => "Ocorreu um problema ao criar o Webhook, confira o log para mais detalhes",
            'status' => 500
            ],500
        );
    }

    public function getWebHook($pix = false){
        // Garante que o certificado está configurado; senão lança InterCertificateException
        $this->ensureValidCert();

        $headers =  [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            "Content-Type" => "application/json"
        ];

        if($pix){
            $pix =  preg_replace('/[.\-\s]/', '', env('CHAVE_PIX'));
            $url = "pix/v3/webhook/{$pix}";
        }else {
            $url = "cobranca/v3/boletos/webhook";
        }

        return $this->lnService->get(
            $this->url . $url, 
            $headers,
            $this->certKey()
        );
    }

    public function generatePaymentPix($data)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            'Content-Type'  => 'application/json',
        ];

        $response = $this->lnService->post(
            $this->url . 'pix/v2/cob',
            $headers,
            $data,
            $this->certKey()
        );

        if ($response->getStatusCode() != 201) {
            $this->logs("gerar-pix " . $response->getStatusCode() . " - ver resposta em log do controller/Bugsnag\n\n");
        }

        return $response;
    }


    public function testeGet(){

        $headers =  [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            "Content-Type" => "application/json"
        ];

        $response = $this->lnService->get(
            $this->url . "cobranca/v3/boletos/#", 
            $headers,
            $this->certKey()
        );

            $body = $response->getBody()->getContents();
            $status = json_decode($body);

        dd($status);

    }

    public function generateReportExtractPdf($dataInicio, $dataFim){
        // Garante que o certificado está configurado antes de chamar a API
        $this->ensureValidCert();

        $headers =  [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            "Content-Type" => "application/json"
        ];

        $response = $this->lnService->get(
            $this->url . "banking/v3/extrato/exportar?dataInicio={$dataInicio}&dataFim={$dataFim}", 
            $headers,
            $this->certKey()
        );

        return $response;
    }

    public function getCobrança($codigo_solitacao){
        $headers =  [
            'Authorization' => 'Bearer ' . $this->checkOAuthLogin(),
            "Content-Type" => "application/json"
        ];

        $response = $this->lnService->get(
            $this->url . "cobranca/v3/cobrancas/{$codigo_solitacao}", 
            $headers,
            $this->certKey()
        );

        return $response;
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-boleto/boleto.log'),
        ])->info($message);
    }
}

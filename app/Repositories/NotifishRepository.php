<?php 

namespace App\Repositories;

use App\Interfaces\NotifishInterface;
use App\Services\LnService;
use App\Models\Boleto;
use App\Models\Pix;
use App\Models\Nfe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class NotifishRepository implements NotifishInterface {

   protected $lnService;

   public function __construct(LnService $lnService)
   {
        $this->lnService = $lnService;
   }

   /**
    * Inicia sessão (login) e retorna o QRCode / status.
    * 
    * @return array ['success' => bool, 'status' => int, 'data' => array, 'message' => string|null]
    */
   public function login($instance_uuid)
   {
      $url = $this->buildUrl($instance_uuid, 'login');
      $response = $this->lnService->post($url, $this->headers());
      
      $statusCode = $response->getStatusCode();
      $body = json_decode($response->getBody()->getContents(), true);
      
      return [
         'success' => $statusCode >= 200 && $statusCode < 300,
         'status'  => $statusCode,
         'data'    => $body,
         'message' => $statusCode >= 400 ? ($body['message'] ?? 'Erro ao realizar login') : null,
      ];
   }

   /**
    * Retorna o status formatado para o Vue (qrcode e status).
    * 
    * @return array ['qrcode' => string|null, 'status' => string]
    */
   public function getLoginStatus($instance_uuid)
   {
      $result = $this->login($instance_uuid);
      
      if (!$result['success']) {
         return [
            'qrcode' => null,
            'status' => 'error',
         ];
      }

      $data = $result['data'];
      $response = [
         'qrcode' => null,
         'status' => 'qrcode', // padrão
      ];

      $state = $data['data']['instance']['state'] ?? null;

      // Se já está conectado (state === 'open')
      if ($state === 'open') {
         $response['status'] = 'open';
      } 
      // Se não está conectado e tem QRCode em base64
      elseif (isset($data['data']['base64'])) {
         $response['qrcode'] = $data['data']['base64'];
         $response['status'] = 'qrcode';
      }
      // Se tem QRCode direto na resposta
      elseif (isset($data['data']['qrcode'])) {
         $response['qrcode'] = $data['data']['qrcode'];
         $response['status'] = 'qrcode';
      }
      // Outros status possíveis
      elseif ($state) {
         $response['status'] = strtolower($state);
      }

      

      return $response;
   }

   /**
    * Verifica se a instância está conectada (state === 'open').
    * 
    * @return bool
    */
   public function isConnected($instance_uuid)
   {
      $result = $this->login($instance_uuid);
      
      if (!$result['success']) {
         return false;
      }

      $data = $result['data'];
      
      return isset($data['data']['instance']['state']) && $data['data']['instance']['state'] === 'open';
   }

   /**
    * Envia mensagem de texto.
    */
   public function sendMessage($instance_uuid, $message, $phone, $identifier = null)
   {
      $url = $this->buildUrl($instance_uuid, 'message');
      $phone = preg_replace('/^55/', '', (string) $phone);
      
      $data = [
         'identifier'      => $identifier,
         'recipient_phone' => '55' . $phone,
         'message'         => $message,
      ];


      $response = $this->lnService->post($url, $this->headers(), $data);
      return json_decode($response->getBody()->getContents(), true);
   }

   
   /**
    * Envia mídia (PDF, imagem, etc.) via Notifish.
    * Payload no padrão: identifier, recipient_phone, message, delay, media[ { type, archive, filename, caption? } ].
    */
   public function sendArchive($instance_uuid, $archive_path, $phone, $identifier = null, $mediaType = null, $filename = null, $caption = '')
   {
      $url = $this->buildUrl($instance_uuid, 'send-media');

      if ($mediaType !== null) {
         $type = $mediaType;
      } else {
         $ext  = strtolower(pathinfo($archive_path, PATHINFO_EXTENSION));
         $type = $ext ?: 'pdf';
      }

      if ($filename === null || $filename === '') {
         $path = parse_url($archive_path, PHP_URL_PATH);
         $filename = $path ? basename($path) : ('document.' . $type);
      }
      if ($type === 'pdf' && strtolower(substr($filename, -4)) !== '.pdf') {
         $filename .= '.pdf';
      }

      $phone = preg_replace('/^55/', '', (string) $phone);
      $data = [
         'identifier'      => is_int($identifier) ? (string) $identifier : ($identifier ?? 'boleto'),
         'recipient_phone' => '55' . $phone,
         'message'        => '',
         'delay'           => 0,
         'media'           => [
            [
               'type'    => $type,
               'archive' => $archive_path,
               'filename' => $filename,
               'caption' => (string) $caption,
            ],
         ],
      ];

      $response = $this->lnService->post($url, $this->headers(), $data);
      return json_decode($response->getBody()->getContents(), true);
   }

   /**
    * Logout da sessão WhatsApp.
    */
   public function logout($instance_uuid)
   {
      $url = $this->buildUrl($instance_uuid, 'logout');

      $response = $this->lnService->post($url, $this->headers());
      return json_decode($response->getBody()->getContents(), true);
   }

   /**
    * Reinicia a sessão (equivalente ao endpoint /restart).
    */
   public function reconnect($instance_uuid)
   {
      $url = $this->buildUrl($instance_uuid, 'restart');

      $response = $this->lnService->get($url, $this->headers());
      return json_decode($response->getBody()->getContents(), true);
   }

   /**
    * Reinicia o WhatsApp (endpoint /restart).
    * 
    * @return array ['success' => bool, 'status' => int, 'data' => array, 'message' => string|null]
    */
   public function restart($instance_uuid)
   {
      $url = $this->buildUrl($instance_uuid, 'restart');

      $response = $this->lnService->get($url, $this->headers());
      
      $statusCode = $response->getStatusCode();
      $body = json_decode($response->getBody()->getContents(), true);
      
      return [
         'success' => $statusCode === 200,
         'status'  => $statusCode,
         'data'    => $body,
         'message' => $statusCode === 200 ? ($body['message'] ?? 'success') : ($body['message'] ?? 'Erro ao reiniciar WhatsApp'),
      ];
   }

   /**
    * Envia mensagem de cobrança de boleto via Notifish (texto semelhante ao antigo WhatsApp).
    *
    * @param int $boleto_id
    * @return object
    */
   public function sendMessageBeforeBoleto($boleto_id)
   {
      $prefixLog = 'Notifish send - ';

      $find = Boleto::where('id', $boleto_id)->with('customer')->firstOrFail();

      // Mesmo critério de permissão do WhatsApp antigo
      if (!$this->verifyPermissionSendBoleto($find, 'whatsapp_notify')) {
         $this->logs("{$prefixLog} boleto id {$boleto_id} não enviado. Cliente configurado para não receber boletos via WhatsApp. " . $find->customer . "\n\n");
         return (object)[
            'status' => 'error',
            'message' => 'O cliente está configurado para não receber boletos por WhatsApp, verifique o cadastro do cliente',
         ];
      }

      $this->logs("{$prefixLog} Iniciando o envio de mensagem de cobrança do boleto id {$boleto_id} para o cliente " . $find->customer);

      $value    = convertBRL($find->price);
      $due_date = date('d/m/Y', strtotime($find->due_date));
      $data_multa = date('d/m/Y', strtotime($find->data_multa));

      $string = "*Seu boleto chegou!* \n\n
        *Olá, {$find->customer->name} - {$find->customer->fantasy_name}*
        O seu boleto no valor de *R$ {$value} com vencimento no dia {$due_date}* está em anexo e disponível para pagamento. 
        Lembramos que caso não seja quitado até o *dia {$due_date}, haverá aplicação de multa e mora a partir do dia {$data_multa}*. 
        *Linha digitável: {$find->digitable_line}*
        *Chave pix: " . env('CHAVE_PIX') . "*
        *A sua nota fiscal chegará em breve por email.*
        *Para mais detalhes, confira seu e-mail.*
        *Mensagem enviada automaticamente em* " . date('d/m/Y \à\\s H:i') . ".";

      $words = preg_replace('/\s\s+/', "\n\n", $string);

      $result = $this->sendMessage(
         env('NOTIFISH_UUID'),
         $words,
         $find->customer->whatsapp
      );

      $this->logs("{$prefixLog} Resposta sendMessage: " . json_encode($result));

      // Verifica sucesso pela resposta da API (pode ter 'success', 'status' ou status HTTP)
      $isSuccess = isset($result['success']) && $result['success'] === true
         || isset($result['status']) && (strtolower($result['status']) === 'success' || $result['status'] === 200)
         || (isset($result['message']) && strtolower($result['message']) === 'success')
         || (empty($result) === false && !isset($result['error']) && !isset($result['message'])); // Se não tem erro, assume sucesso

      $this->logs("{$prefixLog} isSuccess: " . ($isSuccess ? 'true' : 'false'));

      if ($isSuccess) {
         $this->logs("{$prefixLog} Mensagem de cobrança enviada com sucesso para o cliente. " . $find->customer);

         // Após a mensagem, envia o arquivo do boleto (mesmo comportamento do WhatsApp antigo)
         $sendBoletoResult = $this->sendBoleto($find->customer, $find);

         if ($sendBoletoResult && (isset($sendBoletoResult['success']) || isset($sendBoletoResult['status']))) {
            $this->logs("{$prefixLog} Boleto enviado com sucesso para o cliente. " . $find->customer);
         } else {
            $this->logs("{$prefixLog} Aviso: Mensagem enviada mas arquivo do boleto pode não ter sido enviado. " . json_encode($sendBoletoResult));
         }

         return (object)[
            'status' => 'success',
            'message' => 'Mensagem e boleto enviados com sucesso',
         ];
      }

      $this->logs("{$prefixLog} ocorreu um erro ao enviar o boleto para o cliente " . $find->customer . ' - ' . json_encode($result));

      return (object)[
         'status' => 'error',
         'message' => $result['message'] ?? 'Erro ao enviar mensagem via WhatsApp',
      ];
   }

   /**
    * Envia mensagem de cobrança PIX + imagem do QR Code via Notifish.
    */
   public function sendMessageBeforePix($pix_id)
   {
      $prefixLog = 'Notifish send PIX - ';
      $find = Pix::where('id', $pix_id)->with('customer')->firstOrFail();

      if (!$this->verifyPermissionSendBoleto($find, 'whatsapp_notify')) {
         $this->logs("{$prefixLog} Pix id {$pix_id} não enviado. Cliente configurado para não receber cobranças via WhatsApp.\n\n");
         return (object)[
            'status' => 'error',
            'message' => 'O cliente está configurado para não receber cobranças por WhatsApp',
         ];
      }

      $this->logs("{$prefixLog} Iniciando envio de cobrança PIX id {$pix_id} para " . $find->customer->name);

      $invoiceRoute = route('frontend.get.pix', ['token' => $find->uuid]);
      $value = convertBRL($find->original_price);
      $due_date = date('d/m/Y \à\s H:i', strtotime($find->create_date_pix) + $find->expiration_pix - 60);
      $string = "*Sua fatura chegou!* \n\n
        *Olá, {$find->customer->name} - {$find->customer->fantasy_name}*
        A sua fatura no valor de R$ {$value} com vencimento no dia {$due_date} está em anexo e disponível para pagamento.
        Lembramos que caso não seja quitado até o dia {$due_date}, haverá aplicação de multa e mora a partir do dia seguinte ao vencimento.
        Pix Copia e cola: {$find->pix_copy_and_paste}
        A sua nota fiscal chegará em breve por email.
        Para mais detalhes, confira seu e-mail.
        Visualize sua fatura em: {$invoiceRoute}
        Mensagem enviada automaticamente em: " . date('d/m/Y \à\s H:i') . ".";

      $words = preg_replace('/\s\s+/', "\n\n", $string);

      $result = $this->sendMessage(env('NOTIFISH_UUID'), $words, $find->customer->whatsapp, $pix_id);

      $isSuccess = isset($result['success']) && $result['success'] === true
         || isset($result['status']) && (strtolower($result['status']) === 'success' || $result['status'] === 200)
         || (isset($result['message']) && strtolower($result['message']) === 'success');

      if ($isSuccess) {
         $this->logs("{$prefixLog} Mensagem enviada. Enviando imagem do QR Code.");
         $imageUrl = url(route('frontend.get.image.pix', ['uuid' => $find->uuid]));
         $sendImageResult = $this->sendArchive(env('NOTIFISH_UUID'), $imageUrl, $find->customer->whatsapp, $pix_id, 'png');
         if (isset($sendImageResult['success']) && $sendImageResult['success']) {
            $find->update(['whatsapp_notify_send' => date('Y-m-d H:i:s')]);
         }
         return (object)['status' => 'success', 'message' => 'PIX enviado com sucesso'];
      }

      $this->logs("{$prefixLog} Erro ao enviar PIX: " . json_encode($result));
      return (object)[
         'status' => 'error',
         'message' => $result['message'] ?? 'Erro ao enviar mensagem via WhatsApp',
      ];
   }

   /**
    * Envia mensagem de notificação para o número configurado (ex: NOTIFY_SEND_BOLETO_WHATSAPP).
    */
   public function sendNotificationMessage($message)
   {
      $phone = env('NOTIFY_SEND_BOLETO_WHATSAPP');
      if (empty($phone)) {
         return;
      }
      $phone = preg_replace('/^55/', '', $phone);
      $this->sendMessage(env('NOTIFISH_UUID'), $message, $phone);
   }

   /**
    * Notifica sucesso/falha de envio de boletos ou PIX para o número de alerta.
    */
   public function notifySendBoletoWhatsApp($ids, $success, $pix = false)
   {
      $phone = env('NOTIFY_SEND_BOLETO_WHATSAPP');
      if (empty($phone)) {
         return;
      }
      $phone = preg_replace('/^55/', '', $phone);

      if ($pix) {
         $items = Pix::whereIn('id', $ids)->with('customer')->get();
      } else {
         $items = Boleto::whereIn('id', $ids)->with('customer')->get();
      }

      $names = $items->map(function ($item) {
         return $item->customer->name . ' - ' . $item->customer->fantasy_name;
      })->implode(" \n");
      $title = $success
         ? '*Boleto/PIX enviado com sucesso para os clientes*'
         : '*Falha ao enviar boleto/PIX para os clientes*';
      $content = $title . "\n\n" . $names;
      $this->sendMessage(env('NOTIFISH_UUID'), $content, $phone);
      $this->logs('Notifish send - alertado: ' . $title . "\n");
   }

   /**
    * Notifica sucesso/falha de envio de NFes para o número de alerta.
    */
   public function notifySendNfeWhatsApp($ids, $success)
   {
      $phone = env('NOTIFY_SEND_BOLETO_WHATSAPP');
      if (empty($phone)) {
         return;
      }
      $phone = preg_replace('/^55/', '', $phone);

      $nfes = Nfe::whereIn('id', $ids)->with('customer')->get();
      $names = $nfes->map(function ($nfe) {
         return $nfe->customer->name . ' - ' . $nfe->customer->fantasy_name;
      })->implode(" \n");
      $title = $success ? '*NFes enviado com sucesso para os clientes*' : '*Falha ao enviar NFes para os clientes*';
      $content = $title . "\n\n" . $names;
      $this->sendMessage(env('NOTIFISH_UUID'), $content, $phone);
      $this->logs('Notifish send - alertado NFe: ' . $title . "\n");
   }

   private function headers(){
      return [
          'Authorization' => 'Bearer ' . env('NOTIFISH_API_KEY'),
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
       ];
  }

  /**
   * Monta a URL completa do endpoint v2 WhatsApp da Notifish.
   *
   * Ex.: {BASE}/api/v2/{instance_uuid}/{path}
   */
  private function buildUrl($instance_uuid, string $path): string
  {
      return rtrim(env('NOTIFISH_BASE_URL'), '/')
          . '/' . $instance_uuid
          . '/whatsapp/' . ltrim($path, '/');
  }

   /**
   * Envia o arquivo PDF do boleto via Notifish.
   * Usa URL assinada pública (expira em 10 min), identificada por our_number do Inter.
   */
  private function sendBoleto($customer, $boleto)
  {
      $prefixLog = 'Notifish send - ';
      $this->logs("{$prefixLog} Iniciando o envio do arquivo do boleto");

      if (empty($boleto->our_number)) {
         $this->logs("{$prefixLog} Boleto sem our_number (Inter); não é possível gerar URL assinada.");
         return ['success' => false, 'message' => 'Boleto sem identificador do Inter'];
      }

      // Verifica se o arquivo existe localmente
      $archive = base_path() . "/storage/app/" . $boleto->path . $boleto->boleto;

      if (!file_exists($archive)) {
         $this->logs("{$prefixLog} Arquivo do boleto não encontrado: {$archive}");
         return ['success' => false, 'message' => 'Arquivo do boleto não encontrado'];
      }

      // URL assinada pública (expira em 10 min); usa our_number do Inter, não o ID interno
      $archiveUrl = URL::temporarySignedRoute('frontend.boleto.pdf', now()->addMinutes(10), ['our_number' => $boleto->our_number]);

      $this->logs("{$prefixLog} Enviando boleto via URL assinada (expira em 10 min): {$archiveUrl}");

      // Nome do arquivo: fantasy_name do customer, sem caracteres especiais, sem espaço no final, sempre .pdf
      $nomeFantasia = $customer->fantasy_name ?: $customer->name;
      $filename = preg_replace('/[^\p{L}\p{N}\s]/u', '', $nomeFantasia);
      $filename = trim($filename);
      $filename = preg_replace('/\s+/', ' ', $filename);
      $filename = $filename !== '' ? $filename . '.pdf' : 'boleto.pdf';

      // Padrão Notifish: identifier "boleto", media com type, archive (URL), filename, caption
      // Data de vencimento da tabela boletos (coluna due_date)
      $dueDate = $boleto->due_date ? date('d/m/Y', strtotime($boleto->due_date)) : '--/--/----';
      $caption = "Boleto de {$nomeFantasia}, com vencimento para {$dueDate}. Após o vencimento haverá incidência de juros e multa.";

      $response = $this->sendArchive(
          env('NOTIFISH_UUID'),
          $archiveUrl,
          $customer->whatsapp,
          'boleto',
          'pdf',
          $filename,
          $caption
      );

      $this->logs("{$prefixLog} Resposta envio arquivo boleto: " . json_encode($response));

      if (!isset($response['success']) || (isset($response['success']) && $response['success'] !== true)) {
         $this->logs("{$prefixLog} Envio do boleto via Notifish falhou.");
      } else {
         // Atualiza timestamp de envio se sucesso
         $boleto->update(['whatsapp_notify_send' => date('Y-m-d H:i:s')]);
         $this->logs("{$prefixLog} Boleto enviado com sucesso para o cliente " . $customer->name);
      }

      return $response;
  }

  /**
   * Mesma lógica de permissão do WhatsAppRepository.
   */
  private function verifyPermissionSendBoleto($find, $type){
      return $find->customer->$type == 1 ? $find : false;
  }

  private function logs($message){
      Log::build([
          'driver' => 'daily',
          'path' => storage_path('logs/gerar-boleto/boleto.log'),
      ])->info($message);
  }
}


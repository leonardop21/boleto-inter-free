<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Interfaces\NotifishInterface;
use App\Http\Responses\ApiResponse;

class NotifishController extends Controller
{
    public function __construct(NotifishInterface $notifish)
    {
        $this->notifish = $notifish;
    }

    public function index()
    {
        $result = $this->notifish->login(env('NOTIFISH_UUID'));
        
        if (!$result['success']) {
            return redirect()->back()
                ->with(['message' => $result['message'] ?? 'Erro ao conectar com WhatsApp', 'alert-type' => 'error']);
        }

        $response = $this->notifish->getLoginStatus(env('NOTIFISH_UUID'));
        
        return view('Backend.WhatsApp.index', ['response' => $response]);
    }

    public function qrcodeUpdate()
    {
        $response = $this->notifish->getLoginStatus(env('NOTIFISH_UUID'));
        return ApiResponse::success($response, 'Status do QR Code atualizado');
    }

    public function getStatusConnection()
    {
        $isConnected = $this->notifish->isConnected(env('NOTIFISH_UUID'));
        
        return ApiResponse::success([
            'status' => $isConnected ? 'open' : 'closed',
            'connected' => $isConnected
        ], 'Status da conexão obtido com sucesso');
    }

    public function logout()
    {
        $result = $this->notifish->logout(env('NOTIFISH_UUID'));
        
        if (isset($result['message']) && $result['message'] === 'success') {
            return redirect()->route('backend.whatsapp.index')
                ->with(['message' => 'Sessão WhatsApp encerrada com sucesso', 'alert-type' => 'success']);
        }

        return redirect()->back()
            ->with(['message' => 'Erro ao encerrar sessão WhatsApp', 'alert-type' => 'error']);
    }

    public function restart()
    {
        $result = $this->notifish->restart(env('NOTIFISH_UUID'));
        
        if ($result['success'] && $result['status'] === 200) {
            return redirect()->back()
                ->with(['message' => 'WhatsApp reiniciado com sucesso', 'alert-type' => 'success']);
        }

        return redirect()->back()
            ->with(['message' => $result['message'] ?? 'Erro ao reiniciar WhatsApp', 'alert-type' => 'error']);
    }

    /**
     * Envia cobrança (boleto ou pix) avulsa via WhatsApp.
     *
     * porém utilizando o padrão de repository (SendRepository).
     *
     * @param int  $id   ID do boleto/pix
     * @param bool $pix  Se true, trata como pix; caso contrário, boleto
     */
    public function sendAvulse($id, $pix = false)
    {
        // Envio de PIX avulso via Notifish (WhatsApp)
        if ($pix) {
            $response = $this->notifish->sendMessageBeforePix($id);
            $response = is_string($response) ? json_decode($response) : $response;

            if ($response && strtolower($response->status ?? '') === 'success') {
                return redirect()->route('backend.fatura.pix.index')
                    ->with(['message' => 'Pix enviado com sucesso', 'alert-type' => 'success']);
            }

            return redirect()->route('backend.fatura.pix.index')
                ->with(['message' => 'Ocorreu um erro ao tentar enviar o Pix. Verifique se está conectado e a sessão iniciada', 'alert-type' => 'danger']);
        }

        // Envio de BOLETO avulso via Notifish (WhatsApp)
        $response = $this->notifish->sendMessageBeforeBoleto($id);

        $response = is_string($response) ? json_decode($response) : $response;

        if ($response && strtolower($response->status ?? '') === 'success') {
            return redirect()->route('backend.fatura.boleto.index')
                ->with(['message' => 'Boleto enviado com sucesso', 'alert-type' => 'success']);
        }

        return redirect()->route('backend.fatura.boleto.index')
            ->with(['message' => 'Ocorreu um erro ao tentar enviar o boleto. Verifique se está conectado e a sessão iniciada', 'alert-type' => 'danger']);
    }
}

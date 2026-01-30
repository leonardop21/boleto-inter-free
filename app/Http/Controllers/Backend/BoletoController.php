<?php

namespace App\Http\Controllers\Backend;

use App\Models\Boleto;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Interfaces\BoletoInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\BoletoRequest;
use App\Interfaces\CustomerInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Exceptions\InterCertificateException;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class BoletoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(BoletoInterface $boleto, CustomerInterface $customer)
    {
        $this->boleto = $boleto;
        $this->customer = $customer;
    }
    public function index(Request $request)
    {
        $customer_id = $request->customer_id ? $request->customer_id : null;
        $records = $this->boleto->withPaginate(['customer'], $customer_id);
        return view('Backend.Fatura.Boleto.index', compact('records', 'customer_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = $this->customer->get(true);
        return view('Backend.Fatura.Boleto.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BoletoRequest $request)
    {
       $result = $this->boleto->store($request->all());
       $result = $this->boleto->handleStoreResponse($result);

       
       

       if (!$result['success']) {
           $errors = $this->boleto->extractInterErrors($result['response'] ?? null);
           
           if (empty($errors)) {
               $errors = ['Erro ao gerar boleto. Tente novamente.'];
           }

           return redirect()->back()
            ->withErrors($errors);
       }

        return redirect()->route('backend.fatura.boleto.index')
            ->with(['message' => 'Boleto gerado com sucesso!', 'alert-type' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendEmail($id)
    {
        \Artisan::call("ln:send-boleto", ["boletos_id" => [0 => $id]]);

        $this->boleto->update(["email_notify_send" => date("Y-m-d H:i:s")], $id);

        return redirect()->route('backend.fatura.boleto.index')
            ->with(['message' => 'Boleto enviado com sucesso!', 'alert-type' => 'success']);
        
    }

    public function sendAvulseEmail($id){
        $this->boleto->sendAvulseBoleto($id);

        $this->boleto->update(["email_notify_send" => date("Y-m-d H:i:s")], $id);

        return redirect()->route('backend.fatura.boleto.index')
            ->with(['message' => 'Boleto enviado com sucesso!', 'alert-type' => 'success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBoletoRequest $request, $id)
    {
        $this->boleto->update([
            "status" => $request->status, 
            'payment_date' => $request->payment_date
        ], $id);

        return redirect()->back()
            ->with(['message' => 'Status do boleto ' . $id . ' atualizado com sucesso', 'alert-type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $response = $this->boleto->cancel($id);

        $success = isset($response['success']) && $response['success'] === true;
        $status = $response['status'] ?? 500;
        $message = $response['message'] ?? ($success ? 'Boleto cancelado com sucesso' : 'Erro ao cancelar boleto');
        $type = $success ? 'success' : 'error';

        if (!$success) {
            Log::channel('single')->error('Falha ao cancelar boleto', [
                'boleto_id' => $id,
                'response'  => $response,
            ]);
            Bugsnag::notify(
                new \Exception("Falha ao cancelar boleto: {$message}"),
                function ($report) use ($id, $response) {
                    $report->setMetaData([
                        'cancelar_boleto' => [
                            'boleto_id' => $id,
                            'response'  => $response,
                            'raw_body'  => $response['raw_body'] ?? null,
                        ],
                    ]);
                }
            );
        }

        return redirect()->back()
            ->with(['message' => $message, 'alert-type' => $type]);
    }

    public function downloadPdf($boleto){
        $response = $this->boleto->getPdf($boleto);

        if($response->getStatusCode() != 200){
            $decode = json_decode($response);
            $mesesage =  "Falha ao baixar boleto: {$decode->detail} código da resposta:  {$response->getStatusCode()}";
             
         return redirect()->back()
         ->with(['message' => $mesesage, 'alert-type' => 'error']);
        }

        $response = json_decode($response->getContent());

        if(isset($response->url_download->headers)){
            $urlDownload = $response->url_download->original->url_download;
        }else {
           $urlDownload = $response->url_download;
        }

        $nameBoleto = preg_replace('/[^a-z0-9]/i', '_', $response->name_boleto);
        return response()->download($urlDownload, $nameBoleto . ".pdf");
    }

    /**
     * Download do PDF do boleto via URL assinada (pública, sem auth).
     * Usado pelo Notifish para enviar o arquivo via WhatsApp.
     * Identificador: our_number do Inter (não expõe ID interno); expira em 10 min.
     */
    public function downloadPdfSigned(Request $request)
    {
        $ourNumber = $request->query('our_number');
        if (empty($ourNumber)) {
            abort(404);
        }

        $boleto = Boleto::where('our_number', $ourNumber)->firstOrFail();
        $response = $this->boleto->getPdf($boleto->id);

        if ($response->getStatusCode() != 200) {
            abort(404);
        }

        $content = json_decode($response->getContent());
        $urlDownload = isset($content->url_download->original) ? $content->url_download->original->url_download : $content->url_download;
        $nameBoleto = preg_replace('/[^a-z0-9]/i', '_', $content->name_boleto);

        return response()->download($urlDownload, $nameBoleto . '.pdf');
    }

    public function createWebhook(){
        $result = $this->boleto->createWebhook();

        $message = $result['message'] ?? 'Operação realizada';
        $type = $result['success'] ? "success" : "error";
 
        return redirect()->route('backend.fatura.boleto.get.webhook')
            ->with(['message' => $message, 'alert-type' => $type]);
    }

    public function getWebhook(){
       try{
           $response = $this->boleto->getWebhook();

           $record['status'] = $response->getStatusCode();
           $record['body']   = json_decode($response->getBody(), true);
       }catch(InterCertificateException $e){
           $record['status'] = 500;
           $record['body']   = ['message' => $e->getMessage()];

           session()->flash('message', $e->getMessage());
           session()->flash('alert-type', 'error');
       }

       return view('Backend.Fatura.Boleto.webhook', compact('record'));
    }

    public function callbackInter(Request $request){
        $result = $this->boleto->callbackInter($request->all());
        return response()->json(['status' => 'ok'], 200);
    }
}

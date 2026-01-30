<?php

namespace App\Http\Controllers\Backend;

use App\Models\Pix;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Events\PaymentReceived;
use App\Interfaces\PixInterface;
use App\Interfaces\SendInterface;
use App\Http\Controllers\Controller;
use App\Interfaces\CustomerInterface;
use App\Exceptions\InterCertificateException;
use App\Http\Requests\StorePixRequest;
use App\Http\Requests\UpdatePixRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class PixController extends Controller
{
    public function __construct(PixInterface $pix, CustomerInterface $customer, SendInterface $send)
    {
        $this->pix = $pix;
        $this->customer = $customer;
        $this->send = $send;
    }

    public function index(Request $request)
    {
        $customer_id = $request->customer_id ? $request->customer_id : null;
        $records = $this->pix->withPaginate(['customer'], $customer_id);
        return view('Backend.Fatura.Pix.index', compact('records', 'customer_id'));
    }

    public function create(){
        $customers = $this->customer->get(true);
        return view('Backend.Fatura.Pix.create', compact('customers'));
    }

    public function PixVencidOGerarNovoByCustomer($id_fatura){
        $result = $this->pix->regeneratePixByCustomer($id_fatura);

        if (!$result['success']) {
            return redirect()->back()
                ->with(['message' => $result['message'] ?? 'Erro ao regenerar PIX', 'alert-type' => 'error']);
        }

        return redirect()->route('frontend.get.pix', ['token' => $result['data']->uuid])
            ->with(['message' => 'Novo PIX gerado com sucesso!', 'alert-type' => 'success']);
    }

    public function store(StorePixRequest $request){
        $customer = $this->customer->find($request->customer_id);

        if (!$customer) {
            return redirect()->back()
                ->withErrors(['customer_id' => 'Cliente não encontrado']);
        }

        $data = [
            "price" => $request->price, 
            "description" => $request->description,
            "my_number" => $request->my_number,
            "due_date" => $request->due_date
        ];
        
        $result = $this->pix->storePix($customer, $data, false);

        if (!$result['success']) {
            $message = $result['message'] ?? 'Erro ao gerar PIX. Tente novamente.';
            Log::channel('single')->error('Falha ao gerar PIX', [
                'customer_id' => $customer->id,
                'result'      => array_diff_key($result, ['response' => 1]), // evita serializar objeto response
                'raw_body'    => $result['raw_body'] ?? null,
            ]);
            Bugsnag::notify(
                new \Exception("Falha ao gerar PIX: {$message}"),
                function ($report) use ($customer, $result) {
                    $report->setMetaData([
                        'gerar_pix' => [
                            'customer_id'  => $customer->id,
                            'customer_name' => $customer->name ?? null,
                            'status_code'  => $result['status_code'] ?? null,
                            'message'      => $result['message'] ?? null,
                            'raw_body'     => $result['raw_body'] ?? null,
                            'data'         => $result['data'] ?? null,
                        ],
                    ]);
                }
            );
            return redirect()->back()
                ->withErrors(['gerar_pix' => $message])
                ->with(['message' => $message, 'alert-type' => 'error']);
        }

        return redirect()->route('backend.fatura.pix.index')
            ->with(['message' => 'Pix gerado com sucesso!', 'alert-type' => 'success']);
    }

    public function update(UpdatePixRequest $request, $id){
        $this->pix->update([
            "status" => $request->status, 
            'payment_date' => $request->payment_date
        ], $id);

        return redirect()->back()
            ->with(['message' => 'Status do pix ' . $id . ' atualizado com sucesso', 'alert-type' => 'success']);
    }

    public function sendAvulseEmail($id){
        $this->send->sendEmailPix($id);

        $this->pix->update(["email_notify_send" => date("Y-m-d H:i:s")], $id);

        return redirect()->route('backend.fatura.pix.index')
            ->with(['message' => 'Pix enviado por e-mail com sucesso!', 'alert-type' => 'success']);
    }

    public function createWebhook(){
        $result = $this->pix->createWebhook();
 
        $message = $result['message'] ?? 'Operação realizada';
        $type = $result['success'] ? "success" : "error";
 
        return redirect()->route('backend.fatura.pix.get.webhook')
             ->with(['message' => $message, 'alert-type' => $type]);
    }
 
    public function getWebhook(){
        try{
            $response = $this->pix->getWebhook();
            $record['status'] = $response->getStatusCode();
            $record['body']   = json_decode($response->getBody(), true);
        }catch(InterCertificateException $e){
            $record['status'] = 500;
            $record['body']   = ['message' => $e->getMessage()];

            session()->flash('message', $e->getMessage());
            session()->flash('alert-type', 'error');
        }

        return view('Backend.Fatura.Pix.webhook', compact('record'));
    }

    public function callbackInter(Request $request){
        $this->pix->callbackInter($request->all());
        return response()->json(['status' => 'ok'], 200);
    }
}

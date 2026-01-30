<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\NfeInterface;
use App\Http\Controllers\Controller;
use App\Interfaces\CustomerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class NfeController extends Controller
{
    public function __construct(NfeInterface $nfe, CustomerInterface $customer)
    {
        $this->nfe = $nfe;
        $this->customer = $customer;
    }

    public function index()
    {
        $records = $this->nfe->withPaginate(['customer'], null);
        return view('Backend.Nfe.index', compact('records'));
    }

    public function create($id){
        $record = $this->customer->find($id);

        $total_price = 0;

        foreach($record->services as $service){
            $service_detail[] = $service->name . " R$ " . $service->price;
            $total_price += $service->price;
        }

        return view('Backend.Nfe.create-nfe', compact('record', 'total_price'));
    }
    
    public function emitir($id){
        $customer = $this->customer->find($id);
        $return = $this->nfe->store($customer);

        if($return){
            Cache::tags(['nfes'])->flush();
            return redirect()->route('backend.nfe.index')
            ->with(['message' => 'Nota fiscal gerada com sucesso', 'alert-type' => 'success']);
        }
        return redirect()->route('backend.nfe.emitir', $id)
        ->with(['message' => 'Ocorreu um erro ao gerar a nota fiscal, confira os logs', 'alert-type' => 'error']);
    }

    public function sendAvulseEmailNfe($id){
        $this->nfe->sendNf($id);
        return redirect()->route('backend.nfe.index')
        ->with(['message' => 'Nota fiscal reenviada com sucesso', 'alert-type' => 'success']);
    }
}

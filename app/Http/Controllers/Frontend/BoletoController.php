<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Boleto;
use Illuminate\Http\Request;
use App\Interfaces\PixInterface;
use App\Interfaces\BoletoInterface;
use App\Http\Controllers\Controller;
use App\Services\InterService;

class BoletoController extends Controller
{
    public function __construct(PixInterface $pix, BoletoInterface $boleto, InterService $interService)
    {
        $this->pix = $pix;
        $this->boleto = $boleto;
        $this->interService = $interService;
    }
    
    public function getBoleto($uuid){
        $record = $this->pix->whereFirstOrFail("uuid", $uuid);
        $boleto = new Boleto();
  
        $data = [
           "customer_id" => $record->customer->id,
           "description" => "Boleto gerado a partir do Frontend",
           "my_number" => $record->my_number,
           "price" => convertBRL($record->original_price),
           "due_date" => date('Y-m-') . "08",
           "date_to_pay_after_due_date" => "10",
           "code_multa" => "VALORFIXO",
           "data_multa" => date('Y-m-') . "09",
           "taxa_multa" => 0,
           "valor_multa" => 12,
           "code_mora" => "TAXAMENSAL",
           "data_mora" => date('Y-m-') . "09",
           "taxa_mora" => 2.5,
           "valor_mora" => 0,
       ];
       
       
       if(\Cache::get('boleto')){
           $responseGeracaoBoleto = \Cache::get('boleto');
        }else {
            $responseGeracaoBoleto = $this->boleto->store($data);
            \Cache::put('boleto', $responseGeracaoBoleto, 3600);
       }
       

       if($responseGeracaoBoleto->getStatusCode()){
            $response = $this->interService->getPdf($responseGeracaoBoleto->original->our_number);

            $exists = $this->findPath($response->original);

            if($exists) return $exists;

            if($response->getStatusCode() == 200){
                $data['path'] = $response->original['path'];
                $data['boleto'] = $response->original['boleto'];
                // $response = $this->abs::update($data, $find->id);
                $download = response()->json(["url_download" => $this->findPath($response), "name_boleto" => $this->namePdf($find->customer)], 200);
                return $this->download($download);
            }

            return $response;
        }
        
        return view('Frontend.Pix.boleto', compact('record'));
     }

     public function findPath($find){
         $find = (Object) $find;

         $boleto = base_path() . "/storage/app/" . $find->path . $find->boleto;
        
        if(isset($find->path) && isset($find->boleto) && file_exists($boleto)){
            $download = response()->json(["url_download" => $boleto, "name_boleto" => 'boleto'], 200);
            $this->download($download);
        }

        return false;
    }

    public function download($download){
        $response = json_decode($download->getContent());

        if(isset($response->url_download->headers)){
            $urlDownload = $response->url_download->original->url_download;
        }else {
           $urlDownload = $response->url_download;
        }

        $nameBoleto = preg_replace('/[^a-z0-9]/i', '_', $response->name_boleto);
        return response()->download($urlDownload, $nameBoleto . ".pdf");
    }
}

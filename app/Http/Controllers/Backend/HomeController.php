<?php

namespace App\Http\Controllers\Backend;

use App\Models\Pix;
use Illuminate\Http\Request;
use App\Services\InterService;
use App\Repositories\PixRepository;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BoletoRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\AbstractRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Response;
use App\Exceptions\InterCertificateException;
use App\Http\Responses\ApiResponse;
use Carbon\Carbon;

class HomeController extends Controller
{

    public function __construct(CustomerRepository $customer, ServiceRepository $service, BoletoRepository $boleto, InterService $inter, PixRepository $pix){
        $this->customer = $customer;
        $this->service = $service;
        $this->boleto = $boleto;
        $this->inter = $inter;
        $this->pix = $pix;
    }
    
    public function index(){
        $getServices = $this->service->get(true);
        $qtdCustomer = $this->customer->get()->count();
        $qtdService = $getServices->count();
        $qtdBoleto = $this->boleto->get()->count();
        $saldoObject = $this->getSaldoCache();
        $saldo = isset($saldoObject->disponivel) ? $saldoObject->disponivel : 0;
        $saldoErrorMessage = isset($saldoObject->erro_saldo_inter) ? $saldoObject->erro_saldo_inter : null;
        $valueBoletoPaidMonth = $this->boleto->getValueBoletoMonth('PAGO');
        $valueBoletoOpenMonth = $this->boleto->getValueBoletoMonth('EM ABERTO');
        $valueBoletoNotPaidMonth = $this->boleto->getValueBoletoMonth('VENCIDO');

        $valuePixPaidMonth = $this->pix->getValuePixMonth('CONCLUIDA');
        $valuePixOpenMonth = $this->pix->getValuePixMonth('ATIVA');

        $totalBoletoAndPix = $valueBoletoPaidMonth + $valuePixPaidMonth;
        
        $predictionPriceAllService = 0;

        foreach($getServices as $services){
            $predictionPriceAllService += number_format($services->price, 2, '.', '');
        }

        if(!Cache::has('previsao_valor_total_servicos')){
            Cache::put('previsao_valor_total_servicos', $predictionPriceAllService, 86400);
        }

        $predictionPriceAllService = Cache::get('previsao_valor_total_servicos');

        return view('Backend.Home.index', compact(
            'qtdCustomer',
            'qtdService',
            'qtdBoleto',
            'saldo',
            'saldoErrorMessage',
            'valueBoletoPaidMonth',
            'valueBoletoOpenMonth',
            'valueBoletoNotPaidMonth',
            'predictionPriceAllService',
            'valuePixPaidMonth',
            'valuePixOpenMonth',
            'totalBoletoAndPix'
        ));
    }

    protected function getSaldoCache(){
        if(Cache::has('saldo-conta-inter')){
            return Cache::get('saldo-conta-inter');
        }

        $response = $this->getSaldo();
        Cache::put('saldo-conta-inter', $response, 86400);

        return Cache::get('saldo-conta-inter');
    }

    protected function getSaldo(){
        try{
            $decode = $this->inter->getSaldo();
            $obj = json_decode($decode);
        }catch(InterCertificateException $e){
            // Mensagem amigável para o dashboard e flash vermelho (toastr)
            $obj = (object) [
                'disponivel' => 0,
                'erro_saldo_inter' => $e->getMessage(),
            ];

            session()->flash('message', $e->getMessage());
            session()->flash('alert-type', 'error');
        }

        Cache::put('saldo-conta-inter', $obj, 86400);
        return $obj;
    }

    /**
     * Retorna o saldo em formato JSON para requisições AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaldoJson(){
        try {
            $saldo = $this->getSaldoCache();
            
            // Se houver erro, retorna no formato de erro
            if (isset($saldo->erro_saldo_inter)) {
                return ApiResponse::error(
                    $saldo->erro_saldo_inter,
                    null,
                    500
                );
            }

            // Retorna sucesso com os dados do saldo
            return ApiResponse::success([
                'disponivel' => $saldo->disponivel ?? 0,
                'erro_saldo_inter' => $saldo->erro_saldo_inter ?? null
            ], 'Saldo consultado com sucesso');
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Erro ao consultar saldo: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    public function cacheClear(){
        \Artisan::call('cache:clear');

        return redirect()->back()
            ->with(['message' => 'Cache limpo com sucesso', 'alert-type' => 'success']);
    }

    public function getMonthReport(){
       try {
            // Obter a data atual
            $currentDate = Carbon::now();

            // Definir o último dia do mês anterior
            $lastDayLastMonth = $currentDate->subMonth()->endOfMonth()->format('Y-m-d');

            // Definir o primeiro dia do mês anterior
            $firstDayLastMonth = Carbon::createFromFormat('Y-m-d', $lastDayLastMonth)->startOfMonth()->format('Y-m-d');

            $response = $this->inter->generateReportExtractPdf($firstDayLastMonth, $lastDayLastMonth);

            $pdfBinary = base64_decode($response['pdf']);

            // Define o nome do arquivo PDF
            $filename = "extrato-inter-$firstDayLastMonth-$lastDayLastMonth.pdf";

            // Define os headers da resposta HTTP
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            // Retorna uma resposta HTTP com o PDF anexado
            return Response::make($pdfBinary, 200, $headers);
       } catch (InterCertificateException $e) {
            return redirect()->back()
                ->with([
                    'message' => $e->getMessage(),
                    'alert-type' => 'error'
                ]);
       }
    }
}

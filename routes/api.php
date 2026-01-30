<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\BoletoController;
use App\Http\Controllers\Backend\PixController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/v1/ln/inter/athletico-pr/callback/boleto/cobranca', [BoletoController::class, 'callbackInter'])->name('webhook.callback');
Route::post('/v1/ln/inter/athletico-pr/callback/pix/pagamento', [PixController::class, 'callbackInter'])->name('webhook.callback.pix');


Route::get('pix/vencido/gerar-novo/{id_fatura}', [PixController::class, "PixVencidOGerarNovoByCustomer"])->name('gerar.novo.pix.by.customer');
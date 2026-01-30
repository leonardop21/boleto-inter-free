<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\NfeController;
use App\Http\Controllers\Backend\HomeController;
use App\Http\Controllers\Backend\BoletoController;
use App\Http\Controllers\Backend\PixController;
use App\Http\Controllers\Backend\ServiceController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\CustomerTypeNfeController;
use App\Http\Middleware\RedirectAcessCentral;
use App\Http\Controllers\Backend\NotifishController;

// Frontend 

use App\Http\Controllers\Frontend\PixController as FrontendPixController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => ['web']], function(){

    Auth::routes(['reset' => false, 'register' => false, 'login' => false]);

    Route::get('/ln-singin-system', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/ln-singin-system', [LoginController::class, 'login']);

    Route::middleware([RedirectAcessCentral::class])->group(function () {
        Route::get('/', function () {
            // Lógica da rota '/'
        })->name('nulo');

        Route::get('/login', function () {
            // Lógica da rota '/login'
        });

        Route::get('/register', function () {
            // Lógica da rota '/register'
        });

        Route::get('/registro', function () {
            // Lógica da rota '/registro'
        });

        Route::get('/reset-password', function () {
            // Lógica da rota '/reset-password'
        });

        Route::get('/recuperar-senha', function () {
            // Lógica da rota '/recuperar-senha'
        });

        Route::get('/home', function () {
            // Lógica da rota '/home'
        });

        Route::get('/404', function () {
            // Lógica da rota '/404'
        });
    });



    Route::group(['prefix' => 'dashboard', 'namespace' => 'App\Http\Controllers\Backend', 'middleware' => 'auth', 'as' => 'backend.'], function()
	{
        Route::get('/', [HomeController::class, 'index'])->name('index');
        Route::get('/get-saldo',  [HomeController::class, 'getSaldoJson'])->name('get-saldo');
        Route::get('/limpar-cache', [HomeController::class, 'cacheClear'])->name('cache-clear');
        Route::group(['prefix' => 'cliente', 'as' => 'customer.'], function(){
            Route::get('listar', [CustomerController::class, 'index'])->name('index');
            Route::get('adicionar', [CustomerController::class, 'create'])->name('create');
            Route::post('adicionar', [CustomerController::class, 'store'])->name('store');
            Route::get('editar/{id}', [CustomerController::class, 'edit'])->name('edit');
            Route::put('editar/{id}', [CustomerController::class, 'update'])->name('update');
            Route::get('boletos/listar/{id}', [CustomerController::class, 'getBoletos'])->name('boletos');
            Route::get('pesquisar/cliente/', [CustomerController::class, 'search'])->name('search');
        });

        Route::group(['prefix' => 'servico', 'as' => 'service.'], function(){
            Route::get('listar', [ServiceController::class, 'index'])->name('index');
            Route::get('adicionar', [ServiceController::class, 'create'])->name('create');
            Route::post('adicionar', [ServiceController::class, 'store'])->name('store');
            Route::get('editar/{id}', [ServiceController::class, 'edit'])->name('edit');
            Route::put('editar/{id}', [ServiceController::class, 'update'])->name('update');
            Route::get('pesquisar/servico/', [ServiceController::class, 'search'])->name('search');
        });
        
        Route::group(['prefix' => 'fatura', 'as' => 'fatura.'], function(){
            //  Pix
            Route::group(['prefix' => 'pix', 'as' => 'pix.'], function(){
                Route::get('listar/{cliente?}/{customer_id?}', [PixController::class, 'index'])->name('index');
                Route::get('gerar', [PixController::class, 'create'])->name('create');
                Route::post('gerar', [PixController::class, 'store'])->name('store');
                Route::put('editar/{id}', [PixController::class, 'update'])->name('update');
                Route::get('enviar-email/avulse/{id}', [PixController::class, 'sendAvulseEmail'])->name('send-avulse-email');
                Route::get('webhook', [PixController::class, 'createWebhook'])->name('create.webhook');
                Route::get('webhook/consultar', [PixController::class, 'getWebhook'])->name('get.webhook');
            });
            // Boleto
            Route::group(['prefix' => 'boleto', 'as' => 'boleto.'], function(){
                Route::get('listar/{cliente?}/{customer_id?}', [BoletoController::class, 'index'])->name('index');
                Route::get('gerar', [BoletoController::class, 'create'])->name('create');
                Route::post('gerar', [BoletoController::class, 'store'])->name('store');
                Route::post('auto-gerar', [BoletoController::class, 'autoGenerate'])->name('auto-generate');
                Route::get('status', [BoletoController::class, 'status'])->name('status');
                Route::get('cancelar/{id}', [BoletoController::class, 'cancel'])->name('cancel');
                Route::get('enviar-email/{id}', [BoletoController::class, 'sendEmail'])->name('send-email');
                Route::get('enviar-email/avulse/{id}', [BoletoController::class, 'sendAvulseEmail'])->name('send-avulse-email');
                Route::get('download-pdf/{id}', [BoletoController::class, 'downloadPdf'])->name('download');
                Route::put('editar/{id}', [BoletoController::class, 'update'])->name('update');
                Route::get('webhook', [BoletoController::class, 'createWebhook'])->name('create.webhook');
                Route::get('webhook/consultar', [BoletoController::class, 'getWebhook'])->name('get.webhook');
            });
        });

        Route::group(['prefix' => 'whatsapp', 'as' => 'whatsapp.'], function(){
            // Start WhatsApp
            Route::get('/', [NotifishController::class, 'index'])->name('index');
            Route::get('close-session', [NotifishController::class, 'logout'])->name('logout');
            Route::get('restart', [NotifishController::class, 'restart'])->name('restart');
            Route::get('qrcode-login-update', [NotifishController::class, 'qrcodeUpdate']);
            Route::get('check-status', [NotifishController::class, 'getStatusConnection'])->name('whats.check.status');
            Route::get('send/avulse/whatsapp/{id}/{pix?}', [NotifishController::class, 'sendAvulse'])->name('send.avulse');
            // End WhatsApp
        });

        Route::resource('/feriado', \HolidayController::class);
        
        Route::group(['prefix' => 'nota-fiscal', 'as' => 'nfe.'], function(){
            // Start Nota Fiscal
            Route::get('/', [NfeController::class, 'index'])->name('index');
            Route::get('/emitir/{id}', [NfeController::class, 'create'])->name('emitir');
            Route::post('/emitir/{id}', [NfeController::class, 'emitir'])->name('avulsa.store');
            Route::get('enviar-nfe/email/avulse/{id}', [NfeController::class, 'sendAvulseEmailNfe'])->name('send-avulse-email');
            Route::get('/tipo-nota/listar', [CustomerTypeNfeController::class, 'index'])->name('indexTypeNote');
            Route::get('/tipo-nota/adicionar', [CustomerTypeNfeController::class, 'create'])->name('create');
            Route::post('/tipo-nota/adicionar', [CustomerTypeNfeController::class, 'store'])->name('store');
            Route::delete('/tipo-nota/deletar/{id}', [CustomerTypeNfeController::class, 'destroy'])->name('destroy');
            // End Nota Fiscal
        });

        Route::get('/mes/relatorio-gastos/{ano}/{mes}', [HomeController::class, 'getMonthReport'])->name('get-month-report');
    });
});

Route::group(['prefix' => 'ln', 'as' => 'frontend.'], function(){
    Route::get("pagamento/pix/{token}", [FrontendPixController::class, "getPix"])->name('get.pix');
    Route::get("pagamento/pix/imagem/{uuid}", [FrontendPixController::class, "getImagePix"])->name('get.image.pix');
    Route::get("pagamento/pix/recibo/{uuid}", [FrontendPixController::class, "getReciboPix"])->name('get.recibo.pix');
    // Download PDF do boleto via URL assinada (expira em 10 min); identificador: our_number do Inter (não expõe ID interno)
    Route::get('boleto/pdf', [BoletoController::class, 'downloadPdfSigned'])->name('boleto.pdf')->middleware('signed');
});

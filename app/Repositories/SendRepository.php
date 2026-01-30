<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Pix;
use App\Services\LnService;
use App\Interfaces\SendInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRepository implements SendInterface
{
    protected $model;
    protected $tableCache;

    public function __construct(LnService $lnService)
    {   // Intância o model
        $this->model = $this->resolveModel();
        $this->lnService = $lnService;
    }

    public function sendEmailPix($id, $resend = null){
        $find = Pix::where('id', $id)->with('customer')->first();
        
        $file = storage_path("{$find->path}{$find->uuid}.png");

        $data["month"] = $this->translateMonth(date("F"));

        if($resend == 'vencido'){
            $title = "Fatura de {$data["month"]} vencida, evite a suspensão dos seus serviços";
        }elseif($resend == 'today'){
            $title = "Lembrete: sua fatura de {$data["month"]} vence hoje";
        } else {
           $title = "Sua fatura de {$data["month"]} chegou";
        }

        $data['due_date'] = date('d/m/Y \à\s H:i', strtotime($find->create_date_pix) + $find->expiration_pix - 60);
        $data['data_multa'] = Carbon::parse($find->due_date)->addDay(1)->format('d/m/Y');
        $data['date_mora'] = Carbon::parse($find->date_mora)->addDay(1)->format('d/m/Y');
        $data["email"] = $find->customer->email;
        $data["title"] = $title;
        $data["customer"] = $find->customer;
        $data["pix"] = $find;
        $data["services"] = $find->customer->services;

        Mail::send('Emails.Pix.pix', $data, function($message)use($data, $file) {
            $message->to($data["email"], $data["email"])->bcc(env('NOTIFY_SEND_BOLETO'))
                ->subject($data["title"]);
 
            $message->attach($file);
        });

        return response("pixes enviado com sucesso", 200);
    }

    public function sendNotificationSucessOrFailed($model, $title, $view, $id){
        $data['customers'] = $model::whereIn('id', $id)->with('customer')->get();
        $data['id'] = $id;
        $data['email'] = env('NOTIFY_SEND_BOLETO');
        $data['title'] = $title;

        Mail::send("Emails.{$view}", $data, function($message)use($data) {
            $message->to($data["email"], $data["email"])
                ->subject($data["title"]);
         });

         return response("ok", 200);
    }

    public function translateMonth($month){
        switch(strtolower($month)){
            case "january": $month = "Janeiro"; break;
            case "february": $month = "Fevereiro"; break;
            case "march": $month = "Março"; break;
            case "april": $month = "Abril"; break;
            case "may": $month = "Maio"; break;
            case "june": $month = "Junho"; break;
            case "july": $month = "Julho"; break;
            case "august": $month = "Agosto"; break;
            case "september": $month = "Setembro"; break;
            case "october": $month = "Outubro"; break;
            case "november": $month = "Novembro"; break;
            case "december": $month = "Dezembro"; break;
            default: $month = "Unknown"; break;
        }

        return $month;
    }

    public function resolveModel(){
        return app($this->model);
    }

    public function logs($message){
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/gerar-pix/pix.log'),
        ])->info($message);
    }
}
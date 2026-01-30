<?php

namespace App\Http\Controllers\Frontend;

use Carbon\Carbon;
use App\Interfaces\PixInterface;
use App\Interfaces\NotifishInterface;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;

class PixController extends Controller
{
   public function __construct(PixInterface $pix, NotifishInterface $notifish)
   {
      $this->pix = $pix;
      $this->notifish = $notifish;
   }

   public function getPix($token){
      $record = $this->pix->whereFirstOrFail("uuid", $token);

      if(strtolower($record->status) == "concluida"){
         return redirect()->route('frontend.get.recibo.pix', ['uuid' => $record->uuid]);
      }
      
      $createDatePix = Carbon::parse($record->create_date_pix);
      $expirationPix = $record->expiration_pix;

      // Calcula a data de vencimento
      $vencimento = $createDatePix->copy()->addSeconds($expirationPix - 60);
      $dataAtual = Carbon::now();

      if ($dataAtual > $vencimento) {
         $faturaVencida = 1;
      } else {
         $faturaVencida = 0;
      }

      $this->ClienteAcessouFatura($record);

      return view('Frontend.Pix.index', compact('record', 'faturaVencida'));
   }

   public function clienteAcessouFatura($record){

      if(Session::has('pix'. $record->id)){
         return false;
      }
      Session::put('pix' . $record->id, true, now()->addMinutes(10));
      return true;
   }

   public function getImagePix($uuid){
      $record = $this->pix->whereFirstOrFail("uuid", $uuid);
      $storagePath = storage_path("{$record->path}{$record->uuid}.png");
      return Image::make($storagePath)->response();
   }

   public function getReciboPix($uuid){
      $record = $this->pix->whereFirstOrFail("uuid", $uuid);

      if(strtolower($record->status) != "concluida"){
         return redirect()->route('frontend.get.pix', ['token' => $record->uuid]);
      }

      return view('Frontend.Pix.paid', compact('record'));
   }
}
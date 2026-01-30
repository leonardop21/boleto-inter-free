@extends('Frontend.Layouts.frontend')
@section('title')Fatura - {{$record->customer->fantasy_name}} - {{$record->uuid}}@endsection
@section('css')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<style>
  html {
  scroll-behavior: smooth;
}
body{
  font-family: 'Open Sans', sans-serif;
  background:#dbd5d5;    
}
.card-body {
  background-color: #eee;
}
</style>
@endsection

@section('content')

<div class="bg-dark">
  <div class="container">
      <div class="row">
          <div class="col-12">
               <p class="text-warning text-center p-2"><strong>ATENÇÃO: Para garantir a segurança dos nossos usuários, não solicitamos informações pessoais ou dados bancários. <br>Certifique-se de que está navegando em: {{env('APP_URL')}}</strong></p>
          </div>
      </div>
  </div>
</div>

<div class="container mt-4 mb-4 text-center text-lg-left">
  <div class="row">
    @if($faturaVencida)
      @include('Frontend.Includes.Pix.vencida')
    @else
      @include('Frontend.Includes.Pix.nao-vencida')
    @endif
  </div>
</div>
@endsection

@section('js')
<script>
(function() {
  function copyPixCode() {
    var el = document.getElementById("px-cp_ps");
    if (!el || !el.textContent) {
      alert("Não foi possível copiar: código PIX não encontrado.");
      return;
    }
    var text = el.textContent.trim();
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(function() {
        alert("Código copiado com sucesso! Abra o aplicativo do seu banco, vá em Pix Copia e cola, cole o código e confira os dados antes de pagar.");
      }).catch(function() {
        fallbackCopy(text);
      });
    } else {
      fallbackCopy(text);
    }
  }
  function fallbackCopy(text) {
    var ta = document.createElement("textarea");
    ta.value = text;
    ta.style.position = "fixed";
    ta.style.left = "-9999px";
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
      var ok = document.execCommand("copy");
      alert(ok ? "Código copiado com sucesso! Abra o aplicativo do seu banco, vá em Pix Copia e cola, cole o código e confira os dados antes de pagar." : "Não foi possível copiar.");
    } catch (e) {
      alert("Não foi possível copiar. Selecione e copie o código manualmente.");
    }
    document.body.removeChild(ta);
  }
  document.addEventListener("DOMContentLoaded", function() {
    var btns = document.querySelectorAll(".copy_pix");
    btns.forEach(function(btn) {
      btn.addEventListener("click", function(e) {
        e.preventDefault();
        copyPixCode();
      });
    });
    if (typeof Pusher !== "undefined") {
      var token = window.location.pathname.split("/").pop();
      var pusherKey = "{{ config('broadcasting.connections.pusher.key') }}";
      var pusherCluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
      if (pusherKey && pusherCluster) {
        var pusher = new Pusher(pusherKey, { cluster: pusherCluster });
        var channel = pusher.subscribe("payment-channel-" + token);
        channel.bind("payment-received-" + token, function(data) {
          if (data.redirect_url) window.location.href = data.redirect_url;
        });
      }
    }
  });
})();
</script>
@endsection
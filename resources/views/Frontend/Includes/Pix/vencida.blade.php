<div class="col-12 text-center mb-4">
    @if(!$record->replace_for_uuid)
        <a class="btn btn-info" href="{{route('gerar.novo.pix.by.customer', ['id_fatura' => $record->uuid])}}">GERAR NOVO PIX</a>
    @endif
</div>
    <div class="col-lg-12">
        <div class="card card-default">
            <div class="card-body">
            <div class="row">
                <div class="col-2">
                    <div class="invoice-logo">
                    <img width="150" class="mx-auto img-fluid mt-2 mb-3 logo" src="{{secure_asset('logo.png')}}" alt="Logo da Empresa" class="img-fluid">
                </div>
                </div>
                <div class="col-lg-9 text-center">
                <h3 class="mb-3">Fatura vencida!</h3>
                <h6>A fatura de número <strong>#{{$record->uuid}}</strong> venceu em <strong>{{date('d/m/Y', strtotime($record->create_date_pix) + $record->expiration_pix - 60)}}</strong> e não está mais disponível para pagamento.</strong></h6>
                @if($record->replace_for_uuid)
                    <h6 class="mt-3">Você já gerou um novo pix, clique no botão abaixo para acessar a nova fatura.</h6>
                    <a class="btn btn-info mt-3" href="{{route('frontend.get.pix', ['token' => $record->replace_for_uuid])}}">Acessar nova fatura</a>
                @else
                    <h6 class="mt-3">Por favor, gere uma nova fatura clicando no botão abaixo:</h6>
                    <a class="btn btn-info mt-3" href="{{route('gerar.novo.pix.by.customer', ['id_fatura' => $record->uuid])}}">GERAR NOVO PIX</a>
                @endif
            </div>
            <hr>
        </div>
    <div class="col-12 text-center mt-4">
        <hr>
    <h6>Desenvolvido por <a href="https://lnsistemas.com.br" target="_blank" rel="noopener">LN SISTEMAS</a></h6>
</div>
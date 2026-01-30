<div class="col-12 text-center mb-4">
    <a class="btn btn-success" href="#pay-pix">EFETUAR PAGAMENTO</a>
</div>
<div class="row invoice row-printable">
    <div class="col-lg-12">
    <div class="card card-default">
        <div class="card-body">
        <div class="row">
            <div class="col-lg-6">
            <div class="invoice-logo">
                <img width="210" class="mx-auto img-fluid mt-2 mb-3 logo" src="{{secure_asset('logo.png')}}" alt="Logo da Empresa" class="img-fluid">
            </div>
            <ul class="list-unstyled">
                <li><strong>Fatura</strong> #{{$record->uuid}}</li>
                <li><strong>Vencimento:</strong> {{date('d/m/Y \à\s H:i', strtotime($record->create_date_pix) + $record->expiration_pix - 60)}}</li>
                @if($faturaVencida)
                <li><strong>Status:</strong> <span class="badge badge-danger">VENCIDA</span></li>
                @elseif(strtolower($record->status) == "ativa")
                <li><strong>Status:</strong> <span class="badge badge-info">{{$record->status}}</span></li>
                @elseif(strtolower($record->status) == "concluida")
                <li><strong>Status:</strong> <span class="badge badge-success">PAGO</span></li>
                @else
                <li><strong>Status:</strong> <span class="badge badge-danger">Cancelada</span></li>
                @endif
                <li><strong>Valor:</strong> R$ {{convertBrl($record->original_price)}}</li>
            </ul>
            </div>
            <div class="col-lg-6 text-center">
            <p><a class="copy_pix btn btn-success">Copiar código pix</a></p>
            <p class="d-none" id="px-cp_ps">{{$record->pix_copy_and_paste}}</p>
            <img width="250" class="mb-2 img-fluid" src="{{route('frontend.get.image.pix', ['uuid' => $record->uuid])}}" alt="QRCODE para pagamento via pix">
            <h6>Para efetuar o pagamento, basta escanear o QR Code com o seu smartphone, ou copie o código pix clicando no botão acima</h6>
            </div>
        </div>
        <hr>
        <div class="row">
        <div class="col-lg-6">
            <h5><strong>Beneficiário</strong></h4>
            <ul class="list-unstyled">
                <li>{{env('BENEFICIARIO_PIX')}}</li>
                <li>{{env('APP_NAME')}}</li>
                <li>{{env('CNPJ_EMPRESA')}}</li>
                <li>{{env('CIDADE_EMPRESA')}}</li>
                <li>BANCO INTER</li>
            </ul>
            </div>
        <div class="col-lg-6">
            <h5><strong>Pagador</strong></h5>
            <ul class="list-unstyled">
            <li>{{$record->customer->name}}</li>
            <li>{{$record->customer->fantasy_name}}</li>
            <li>{{formatCpfCnpj($record->customer->cpf_cnpj)}}</li>
            <li>{{$record->customer->address}}, {{$record->customer->city}} - {{$record->customer->state}}</li>
            </ul>
        </div>
        </div>
        <hr/>
        <div class="row">
        <div id="pay-pix" class="col-12 text-center">
            <h5>Para efetuar o pagamento, basta escanear o QR Code com o seu smartphone. É simples e rápido!</h6>
            <p class="mt-3"><a class="copy_pix btn btn-success">Copiar código pix</a></p>
            <img class="img-fluid" src="{{route('frontend.get.image.pix', ['uuid' => $record->uuid])}}" alt="QRCODE para pagamento via pix">
            <!-- <h6 class="mt-2">Status: Aguardando pagamento</h6> -->
            <h6 class="mt-2">Aguardando pagamento...</h6>
            <h6 class="mt-2">Após a conclusão do pagamento, você será redirecionado</h6>
        </div>
        </div>
        <hr>
    </div>
    </div>
    <div class="col-12 text-center mt-4">
    <h6>Desenvolvido por <a href="https://lnsistemas.com.br" target="_blank" rel="noopener">LN SISTEMAS</a></h6>
    </div>
</div>
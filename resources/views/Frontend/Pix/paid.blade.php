<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <title>Comprovante de pagamento {{$record->customer->fantasy_name}} - {{$record->uuid}}</title>
  <style>
    .vertical-stripe {
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background-color: #158edc;
    }
    .logo {
      max-width: 100px;
      margin-top: -10px;
    }
    .card {
      border: none;
      border-radius: 20px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }
    .print-btn {
      background-color: #007bff;
      border-color: #007bff;
    }
    .print-btn:hover {
      background-color: #0056b3;
      border-color: #0056b3;
    }
    .bg {
      background-color: #f8f9fa;
    }
    .header-icon {
      font-size: 28px;
    }
    .footer {
      font-size: 14px;
    }
  </style>
</head>
<body class="bg text-dark">
  <div class="container mt-5">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body position-relative">
            <div class="vertical-stripe"></div>
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div class="d-flex align-items-center">
                <img src="{{secure_asset('logo.png')}}" alt="Logo do Portal" class="logo mr-3">
                <h4 class="card-title m-0">Comprovante de Pagamento -</h4>
              </div>
              <p class="text-success ml-2 mb-n1"><strong>PAGO <i class="fas fa-check-circle"></i></strong></p>
              <button class="btn btn-primary print-btn d-print-none ml-auto" onclick="window.print()">
                <i class="fas fa-print mr-2"></i> Imprimir Comprovante
              </button>
            </div>
            <div class="row">
              <div class="col-md-6">
                <h6 class="mb-3"><i class="fas fa-user header-icon"></i> Informações do Pagador:</h6>
                <p>Nome: {{$record->customer->name}} | {{$record->customer->corporate_name}}</p>
                <p>Endereço: {{$record->customer->address}} {{$record->customer->address_number}} {{$record->customer->city}}</p>
                <p>Telefone: {{$record->customer->phone}}</p>
                <p>E-mail: {{$record->customer->email}}</p>
              </div>
              <div class="col-md-6">
                <h6 class="mb-3"><i class="fas fa-building header-icon"></i> Informações do Recebedor:</h6>
                <p>Nome: {{env('BENEFICIARIO_PIX')}}</p>
                <p>Endereço: {{env('CIDADE_EMPRESA')}}</p>
                <p>E-mail: {{env('EMAIL_RECIBO')}}</p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-md-12 text-center">
                <h6 class="mb-3"><i class="fas fa-credit-card header-icon"></i> Detalhes do Pagamento:</h6>
                <p>Fatura #{{$record->uuid}}</p>
                <p>Data: {{date('d/m/Y \à\s H:i', strtotime($record->datetime_situation))}}</p>
                <p>Método de Pagamento: Pix</p>
                <p>Valor Recebido: R$ {{convertBRL($record->amount_paid)}}</p>
              </div>
              @php /*
              @if($record->services)
              <div class="col-md-6">
                <h6 class="mb-3"><i class="fas fa-wrench header-icon"></i> Informações do Serviço Prestado:</h6>
                <ul>
                    @foreach (json_decode($record->services) as  $service)
                        <li>{{$service->service_name}} R$ {{$service->price}}</li>
                    @endforeach
                </ul>
              </div>
              @endif
              */ @endphp
            </div>
          </div>
          <div class="text-center mt-4 mb-4">
              <h5 class="text-success"><strong>PAGO <i class="fas fa-check-circle"></i></strong></h5>
            </div>
          <div class="card-footer text-center footer">
            <h6>© {{date('Y')}}  Desenvolvido por <a href="https://lnsistemas.com.br" target="_blank" rel="noopener">LN SISTEMAS</a></h6>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>

@extends('Backend.Layouts.panel')

@if(!empty($customer_id))
    @section('title', 'Listando todos os boletos de: ' . $records[0]->customer->name)
@else
    @section('title','Lista de boletos')
@endif

@section('content')

<div class="row">
    <div class="col-12">
        <a href="{{route('backend.fatura.boleto.create')}}" class="btn btn-info mb-3 float-right">Novo boleto</a>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                @if(!empty($customer_id))
                    <h3 class="card-title">Listando todos os boletos de: {{$records[0]->customer->name}}</h3>
                @else
                    <h3 class="card-title">Lista de boletos</h3>
                @endif
            </div>
            <div class="card card-primary">
                <div class="card-body">
                  <div class="row">
                    @foreach ($records as $record)
                    @switch($record->status)
                        @case("EM ABERTO")
                            @php $bg = "bg-info" @endphp
                        @break
                        @case("PAGO")
                            @php $bg = "bg-success" @endphp
                        @break
                        @case("CANCELADO")
                            @php $bg = "bg-warning" @endphp
                        @break
                        @case("VENCIDO")
                            @php $bg = "bg-danger" @endphp
                        @break
                        @case("EXPIRADO")
                            @php $bg = "bg-light" @endphp
                        @break
                        @default
                            @php $bg = "bge" @endphp
                    @endswitch
                      <div class="col-sm-6 pb-4">
                          <div class="position-relative p-3 bg-gray" style="height: 220px">
                            <div class="ribbon-wrapper ribbon-xl">
                              <div class="ribbon {{$bg}} text-lg">
                               {{$record->status}}
                              </div>
                            </div>
                             <ul class="list-unstyled">
                                 <li>ID Boleto: {{$record->id}}</li>
                                 <li>Nome: {{$record->customer->name}}</li>
                                 <li>Nome Fantasia: {{$record->customer->fantasy_name}}</li>
                                 <li>Vencimento: {{$record->due_date}}</li>
                                 <li>Valor: {{'R$ '. number_format($record->price, 2, ',', '.') }}</li>
                                 <li>Valor Pago: {{'R$ '. number_format($record->price_paid, 2, ',', '.') }}</li>
                                 <li>Meu número: {{$record->my_number}}</li>
                                 @if(!empty($record->payment_date))
                                    <li>Data Pagamento: {{$record->payment_date}}</li>
                                 @elseif(!empty($record->cancellation_date))
                                    <li>Data Cancelamento: {{$record->cancellation_date}}</li>
                                 @endif
                             </ul>
                             <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-lg-{{$record->id}}">Info</button>
                             <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-lg-edit-{{$record->id}}">Editar</button>
                                @if($record->status == "EM ABERTO")
                                  <a onclick="return confirm('Tem certeza que deseja enviar uma cobrança avulsa via e-mail?')" class="btn btn-warning" href="{{route('backend.fatura.boleto.send-avulse-email', ['id' => $record->id])}}">Enviar e-mail</a>
                                  <a onclick="return confirm('Tem certeza que deseja enviar uma cobrança avulsa pelo WhatsApp?')" class="btn btn-dark" href="{{route('backend.whatsapp.send.avulse', ['id' => $record->id])}}">Enviar WhatsApp</a>
                                  <a onclick="return confirm('Tem certeza que deseja cancelar o boleto?')" class="btn btn-danger" href="{{route('backend.fatura.boleto.cancel', ['id' => $record->id])}}">Cancelar</a>
                                @endif
                          </div>
                        </div>
                        <div class="modal fade show" id="modal-lg-{{$record->id}}" style="display: none;" aria-modal="true" role="dialog">
                            <div class="modal-dialog modal-lg">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h4 class="modal-title">Detalhes boleto id: {{$record->id}}</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">×</span>
                                  </button>
                                </div>
                                <div class="modal-body">
                                  <ul class="list-unstyled">
                                      <li>Nome: {{$record->customer->name}}</li>
                                      <li>Nome fantasia: {{$record->customer->fantasy_name}}</li>
                                      <li>Descrição: {{$record->description}}</li>
                                      <li>Meu número: {{$record->my_number}}</li>
                                      <li>Nosso número (banco): {{$record->our_number}}</li>
                                      <li>código de barras: {{$record->bar_code}}</li>
                                      <li>Linha digitável: {{$record->digitable_line}}</li>
                                      <li>Valor boleto: {{'R$ '. number_format($record->price, 2, ',', '.') }}</li>
                                      <li>Valor pago: {{'R$ '. number_format($record->price_paid, 2, ',', '.') }}</li>
                                      <li>Data de vencimento: {{$record->due_date}}</li>
                                      <li>Dias para pagar após vencimento: {{$record->date_to_pay_after_due_date}}</li>
                                      <li>Status: <span class="badge {{$bg}}">{{$record->status}}</span></li>
                                      <li>Data de pagamento: {{$record->payment_date}}</li>
                                      <li>Data de cancelamento: {{$record->cancellation_date}}</li>
                                      <li>Notificado por Email: {{$record->email_notify_send}}</li>
                                      <li>Notificado por WhatsApp: {{$record->whatsapp_notify_send}}</li>
                                      <li>Notificado por Telegram: {{$record->telegram_notify_send}}</li>
                                  </ul>
                                <a class="btn btn-dark" href="{{route('backend.fatura.boleto.download', ['id' => $record->id])}}">Baixar PDF</a>
                                </div>
                                <div class="modal-footer justify-content-between">
                                  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar </button>
                                </div>
                              </div>
                            </div>
                        </div>

                        {{-- Editar  --}}

                        <div class="modal fade show" id="modal-lg-edit-{{$record->id}}" style="display: none;" aria-modal="true" role="dialog">
                            <div class="modal-dialog modal-lg">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h4 class="modal-title">Editar boleto id: {{$record->id}}</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">×</span>
                                  </button>
                                </div>
                                <div class="modal-body">
                                  <ul class="list-unstyled">
                                      <form action="{{route('backend.fatura.boleto.update', ['id' => $record->id])}}" method="post">
                                        @csrf
                                        @method('put')
                                        
                                            <select name="status" id="status" class="form-control" required>
                                                <option value="">Selecione o status</option>
                                                <option value="PAGO" {{$record->status == "PAGO" ? 'selected' : ''}}>PAGO</option>
                                                <option value="EM ABERTO" {{$record->status == "EM ABERTO" ? 'selected' : ''}}>EM ABERTO</option>
                                                <option value="CANCELADO" {{$record->status == "CANCELADO" ? 'selected' : ''}}>CANCELADO</option>
                                                <option value="VENCIDO" {{$record->status == "VENCIDO" ? 'selected' : ''}}>VENCIDO</option>
                                                <option value="EXPIRADO" {{$record->status == "EXPIRADO" ? 'selected' : ''}}>EXPIRADO</option>
                                            </select>
                                            
                                            <div class="form-group mt-4">
                                              <label for="payment_date">Data de pagamento</small></label>
                                              <input type="date" class="form-control" name="payment_date" id="payment_date" value="{{old('payment_date')}}" placeholder="Data de vencimento">
                                          </div>

                                        <button type="submit" class="btn btn-success mt-4">Atualizar {{$record->id}}</button>
                                      </form>
                                  </ul>
                                </div>
                                <div class="modal-footer justify-content-between">
                                  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar </button>
                                </div>
                              </div>
                            </div>
                        </div>
                    @endforeach
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            {!! $records->links('pagination::bootstrap-4') !!}
        </div>
    </div>
</div>
@endsection

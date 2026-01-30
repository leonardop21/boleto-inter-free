@extends('Backend.Layouts.panel')
@section('title','Emitir Nota fiscal')
@section('content')

<div class="row">
    <div class="col-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Emitir nota fiscal para {{$record->fantasy_name}}</h3>
          </div>
          <form method="post" action="{{route('backend.nfe.avulsa.store', ['id' => $record->id])}}">
           @csrf
            <div class="card-body">
              <div class="form-row">
                    <div class="form-group col-md-6">
                        <h5>Cliente: <strong>{{$record->name}}</strong></h5>
                        <h5>Tipo de pessoa: <strong>{{$record->type_of_person}}</strong></h5>
                        <h5>Razão social: <strong>{{$record->razao_social}}</strong></h5>
                        <h5>Nome fantasia: <strong>{{$record->fantasy_name}}</strong></h5>
                    </div>
                    <div class="form-group col-md-6">
                        <h5>Serviços:</h5>
                        <ul>
                            @foreach($record->services as $service)
                                <li>{{$service->name}} - R$ {{convertBrl($service->price)}}</li>
                            @endforeach
                        </ul>
                        <h5>Valor total dos serviços: {{convertBrl($total_price)}}</h5>
                    </div>
                    <div class="col-md-12 mb-4">
                        <strong>Ao clicar no botão abaixo, uma nota fiscal será gerada no <span style="color: darkgoldenrod;">valor total de R$ {{convertBrl($total_price)}}</span></strong>
                    </div>
              </div>
              @if($total_price > 0)
                <button type="submit" class="btn btn-primary" onclick="return confirm('Tem certeza que deseja emitir a nota fiscal?')">Emitir Nota Fiscal</button>
              @else 
                <p>Não é possível gerar uma NF para o valor R$ {{$total_price}}</p>
              @endif
            </div>
          </form>
        </div>
    </div>
</div>
@endsection

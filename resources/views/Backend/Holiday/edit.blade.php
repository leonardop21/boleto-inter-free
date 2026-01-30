@extends('Backend.Layouts.panel')
@section('title','Editar Feriado')
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
            <h3 class="card-title">Editar feriado {{$record->description}}</h3>
          </div>
          <form method="post" action="{{route('backend.feriado.update', ['feriado' => $record->id])}}">
           @csrf
           @method('PUT')
            <div class="card-body">
              <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="date_holiday">Dia e mês do feriado*</label>
                        <input type="text" class="form-control" name="date_holiday" id="date_holiday" value="{{$record->date_holiday}}" placeholder="Ex: 25/12" maxlength="5">
                        <small>Preencha no formato dd/mm</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="description">Descrição</label>
                        <input type="text" class="form-control" name="description" id="description" value="{{$record->description}}" placeholder="Ex: Natal" maxlength="255">
                    </div>
              </div>
              <button type="submit" class="btn btn-primary">Editar feriado</button>
            </div>
          </form>
        </div>
    </div>
</div>
@endsection
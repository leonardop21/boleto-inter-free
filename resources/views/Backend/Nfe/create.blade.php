@extends('Backend.Layouts.panel')
@section('title','Adicionar Tipo de serviço e nota')
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
            <h3 class="card-title">Adicionar tipo de serviço e nota fiscal</h3>
          </div>
          <form method="post" action="{{route('backend.nfe.store')}}">
           @csrf
            <div class="card-body">
              <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Nome*</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" placeholder="Desenvolvimento de loja virtual" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="code_item_service">Código do item de serviço</label>
                        <input type="text" class="form-control" name="code_item_service" id="code_item_service" value="{{old('code_item_service')}}" placeholder="Código item lista de serviço Ex: 126">
                    </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="aliquota_item_service">Alíquota do item de serviço*</label>
                    <input type="text" class="form-control" name="aliquota_item_service" id="aliquota_item_service" value="{{old('aliquota_item_service')}}" placeholder="Ex: 2,1700" required>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Adicionar tipo de nota</button>
            </div>
          </form>
        </div>
    </div>
</div>
@endsection

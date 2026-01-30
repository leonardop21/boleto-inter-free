@extends('Backend.Layouts.panel')
@section('title','Clientes')
@section('content')

@php 
    $search = \Request::input('search');
    $type = \Request::input('type_search');
@endphp

<div class="row">
    <div class="col-12">
        <a href="{{route('backend.customer.create')}}" class="btn btn-info mb-3 float-right">Adicionar</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
      <form method="get" action="{{route('backend.customer.search')}}">
        <div class="input-group">
          <input type="search" name="search" value="{{$search}}" class="form-control form-control-lg" placeholder="Pesquisar cliente" required>
          <select name="type_search" id="type_search" class="form-control-lg">
            <option value="name" {{$type == "name" ? 'selected' : ''}}>Nome</option>
            <option value="email" {{$type == "email" ? 'selected' : ''}}>E-mail</option>
            <option value="cpf_cnpj" {{$type == "cpf_cnpj" ? 'selected' : ''}}>Cpf/Cnpj</option>
            <option value="corporate_name" {{$type == "corporate_name" ? 'selected' : ''}}>Razão Social</option>
            <option value="fantasy_name" {{$type == "fantasy_name" ? 'selected' : ''}}>Nome Fantasia</option>
            <option value="city" {{$type == "city" ? 'selected' : ''}}>Cidade</option>
          </select>
          <div class="input-group-append">
            <button type="submit" class="btn btn-lg btn-default">
            <i class="fa fa-search"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
    @if($search)
        <div class="col-12">
            <div class="mt-4 text-center">
                Exibindo resultado para a Pesquisa {{$search}} 
                <a class="btn btn-info btn-xs" href="{{route('backend.customer.index')}}">Limpar pesquisa</a>
            </div>
        </div>
        @endif
</div>

<div class="row">
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de clientes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 10px">Id</th>
                            <th>Nome</th>
                            <th>Nome Fantasia</th>
                            <th>CPF/CNPJ</th>
                            <th>Status</th>
                            <th>Valor serviços</th>
                            <td>Editar</td>
                            <td>Serviços</td>
                            <td>Boleto</td>
                            <td>Emitir NF-e</td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr class="{{$record->status  == '1' ? 'table' : 'bg-danger'}}">
                                <td>{{$record->id}}</td>
                                <td>{{$record->name}}</td>
                                <td>{{$record->fantasy_name}}</td>
                                <td>{{$record->cpf_cnpj}}</td>
                                <td>{{$record->status == 1 ? 'Ativo' : 'Desativado'}}</td>
                                <td>R$ {{checkCustomerValueService($record)}}</td>
                                <td><a class="btn btn-warning" href="{{route('backend.customer.edit', ['id' => $record->id])}}">Editar</a></td>
                                <td width="130"><a href="{{route('backend.fatura.boleto.index', ['cliente' => 'cliente', 'customer_id' => $record->id])}}" class="btn btn-info">Serviços</a></td>
                                <td><a href="{{route('backend.fatura.boleto.index', ['cliente' => 'cliente', 'customer_id' => $record->id])}}" class="btn btn-success">Boletos</a></td>
                                <td width="140"><a href="{{route('backend.nfe.emitir', ['id' => $record->id])}}" class="btn btn-danger">NF-e</a></td>
                            </tr>
                        @empty
                            <h5 class="w-100 text-center mt-4 mb-4">Não encontramos nenhum resultado para a sua pesquisa. Por favor, tente novamente</h5>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('Backend.Includes.pagination', ['paginator' => $records])
@endsection

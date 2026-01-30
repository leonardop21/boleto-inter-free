@extends('Backend.Layouts.panel')
@section('title','Serviços')
@section('content')

@php 
    $search = \Request::input('search');
    $type = \Request::input('type_search');
@endphp

<div class="row">
    <div class="col-12">
        <a href="{{route('backend.service.create')}}" class="btn btn-info mb-3 float-right">Adicionar</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2 mb-4">
      <form method="get" action="{{route('backend.service.search')}}">
        <div class="input-group">
          <input type="search" name="search" value="{{$search}}" class="form-control form-control-lg" placeholder="Pesquisar Serviços" required>
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
            <div class="mb-3 text-center">
                Exibindo resultado para a Pesquisa {{$search}} 
                <a class="btn btn-info btn-xs" href="{{route('backend.service.index')}}">Limpar pesquisa</a>
            </div>
        </div>
        @endif
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Serviços</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 10px">Id</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr class="{{$record->status  == '1' ? 'table' : 'bg-danger'}}">
                                <td>{{$record->id}}</td>
                                <td>{{$record->name}}</td>
                                <td>{{$record->description}}</td>
                                <td>{{'R$ '. number_format($record->price, 2, ',', '.') }}</td>
                                <td>{{$record->status == 1 ? 'Ativo' : 'Desativado'}}</td>
                                <td><a class="btn btn-info" href="{{route('backend.service.edit', ['id' => $record->id])}}">Editar</a></td>
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

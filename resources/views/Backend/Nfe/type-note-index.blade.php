@extends('Backend.Layouts.panel')
@section('title','Nota Fiscal')
@section('content')

<div class="row">
    <div class="col-12">
        <a href="{{route('backend.nfe.create')}}" class="btn btn-info mb-3 float-right">Novo tipo de NFe</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Tipo de notas</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 10px">Id</th>
                            <th>Nome</th>
                            <th>Código item de serviço</th>
                            <th>Nbs Código</th>
                            <th>Nbs Descrição</th>
                            <th>Alíquota item de serviço</th>
                            <th>Valor da dedução</th>
                            <th>Valor issr</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>{{$record->id}}</td>
                                <td>{{$record->name}}</td>
                                <td>{{$record->code_item_service}}</td>
                                <td>{{$record->nbs_code}}</td>
                                <td>{{$record->nbs_description}}</td>
                                <td>{{$record->aliquota_item_service}}</td>
                                <td>{{$record->deduction_value}}</td>
                                <td>{{$record->issr_value}}</td>
                                <td>
                                    <a href="{{route('backend.nfe.destroy', [$record->id])}}" class="btn btn-danger" onclick="
                                        var result = confirm('Tem certeza que deseja deletar?');
                                        
                                        if(result){
                                            event.preventDefault();
                                            document.getElementById('delete-form').submit();
                                        }">
                                        Deletar
                                    </a>
                        
                                    <form method="post" id="delete-form" action="{{route('backend.nfe.destroy', [$record->id])}}">
                                        @csrf
                                        @method('delete')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

@extends('Backend.Layouts.panel')
@section('title','Feriados')
@section('content')

<div class="row">
    <div class="col-12">
        <a href="{{route('backend.feriado.create')}}" class="btn btn-info mb-3 float-right">Adicionar</a>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de feriados</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 10px">Id</th>
                            <th>Data</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>{{$record->id}}</td>
                                <td>{{$record->date_holiday}}</td>
                                <td>{{$record->description}}</td>
                                <td><a class="btn btn-info" href="{{route('backend.feriado.edit', ['feriado' => $record->id])}}">Editar</a></td>
                                <td>
                                    <form method="POST" action="{{route('backend.feriado.destroy', ['feriado' => $record->id])}}">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-danger delete-user" value="Excluir">
                                    </div>
                                    </form>
                                </td>
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

@section('js')
<script>
    $('.delete-user').click(function(e){
        e.preventDefault()
        if (confirm('Tem certeza que deseja remover o feriado?')) {
            $(e.target).closest('form').submit()
        }
    });
</script>

@endsection

@endsection

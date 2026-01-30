@extends('Backend.Layouts.panel')
@section('title','Nota Fiscal')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Notas fiscais</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 10px">Id</th>
                            <th>Empresa</th>
                            <th>Número da NFE</th>
                            <th>Data e hora da geração</th>
                            <th>Situação</th>
                            <th>Notificado por e-mail</th>
                            <th>Link NFe</th>
                            <th>Reenviar NFe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>{{$record->id}}</td>
                                <td>{{$record->customer->name}}</td>
                                <td>{{$record->nfe_number}}</td>
                                <td>{{$record->date_time}}</td>
                                <td>{{$record->situation_description_nfe}}</td>
                                <td>{{$record->email_notify_send}}</td>
                                <td><a target="_blank" rel="noopener" class="btn btn-info" href="{{$record->link_nfse}}">Ver NFe</a></td>
                                <td><a  onclick="return confirm('Tem certeza que deseja enviar a NF avulsa via e-mail?')" class="btn btn-info" href="{{route('backend.nfe.send-avulse-email', ['id' => $record->id])}}">Reenviar NFe</a></td>
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

@extends('Backend.Layouts.panel')
@section('title','Lista de Webhook')
@section('content')

<div class="row">
    <div class="col-12">
        <a href="{{route('backend.fatura.boleto.create.webhook')}}" class="btn btn-info mb-3 float-right">Criar Webhook</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de WeebHooks</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data criação</th>
                            <th>WeebHook</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @if($record['status'] != 200)
                                <td colspan="2">{{ $record['body']['title'] ?? ($record['body']['message'] ?? 'Erro ao consultar webhook') }}</td>
                            @else 
                                <td>{{ $record['body']['webhookUrl'] ?? '-' }}</td>
                                <td>
                                    @if(!empty($record['body']['criacao']))
                                        {{ date('d/m/Y \à\s H:i', strtotime($record['body']['criacao'])) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>
    </div>
</div>
@endsection

@extends('Backend.Layouts.panel')
@section('title','Login no WhatsApp')
@section('content')

<div class="row">
    <div class="col-12">
        <login-whats qrcode-or-status='{!! json_encode($response) !!}'></login-whats>
    </div>
</div>

@endsection

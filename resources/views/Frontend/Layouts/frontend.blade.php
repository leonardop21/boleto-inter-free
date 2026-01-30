<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') | {{env('APP_NAME')}}</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  @yield('css')
</head>
<body>
    @yield('content')

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    @yield('js')    
</body>
</html>

<?php $html = ob_get_clean(); ?>

{{-- Remove os espaços em branco apenas no ambiente de produção --}}
@if(strtolower(env('APP_ENV')) == 'production')
    {!! preg_replace('/\s+/', ' ', $html) !!}
@else
    {!! $html !!}
@endif
<!DOCTYPE html>
<html>
<head>
    <title>Falha ao enviar cobrança</title>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Ocorreu uma falha ao enviar as cobranças para os clientes:</p>
        <ul>
            @foreach($customers as $customer)
                <li>Nome: {{$customer->customer->name}}</li>
                <li>id: {{$customer->customer->id}} 
                <li>Nome fantasia: {{$customer->customer->fantasy_name}}</li>
                <li>-------------------------------------------------------</li>
                <br><br>
            @endforeach
        </ul>
     
    <p>Enviado automaticamente!</p>
</body>
</html>
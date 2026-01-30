<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title>

    </title>
    <!--[if !mso]><!-- -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        .bg-background {
            background-color: #090324;
        }
        #outlook a {
            padding: 0;
        }

        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }

        .ExternalClass * {
            line-height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        p {
            display: block;
            margin: 13px 0;
        }

        .btn-view {
            background: #17a2b8;
            color: #fff !important;
            font-weight: bold;
            border-radius: 11px;
            text-transform: uppercase;
            text-align: center;
            display: block;
            margin-top: 30px;
            padding: 10px;
        }
    </style>
    <!--[if !mso]><!-->
    <style type="text/css">
        @media only screen and (max-width:480px) {
            @-ms-viewport {
                width: 320px;
            }
            @viewport {
                width: 320px;
            }
        }
    </style>
    <style type="text/css">
        @media only screen and (min-width:480px) {
            .mj-column-per-100 {
                width: 100% !important;
            }
        }
    </style>


    <style type="text/css">
    </style>

</head>

<body style="background-color:#f9f9f9;">


    <div style="background-color:#f9f9f9;">
        <div style="background:#f9f9f9;background-color:#f9f9f9;Margin:0px auto;max-width:600px;">

            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#f9f9f9;background-color:#f9f9f9;width:100%;">
                <tbody>
                    <tr>
                        <td style="border-bottom:#333957 solid 5px;direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div style="background:#fff;background-color:#fff;Margin:0px auto;max-width:600px;">

            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
                <tbody>
                    <tr>
                        <td style="border:#dddddd solid 1px;border-top:0px;direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                            <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:bottom;width:100%;">

                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:bottom;" width="100%">

                                    <tr>
                                        <td class="bg-background" align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                <tbody>
                                                    <tr>
                                                    <td style="width:250px;">
                                                        <img height="auto" src="https://i.imgur.com/PjuRhTU.png" style="border:0;display:block;outline:none;text-decoration:none;width:100%;" width="64" />
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="font-size:0px;padding:20px 0px 10px 0px;word-break:break-word;">

                                            <div style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:24px;font-weight:bold;line-height:22px;text-align:center;color:#525252;">
                                                Sua fatura de {{$month}} chegou!
                                            </div>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                            <div style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;text-align:left;color:#525252;">
                                                <p><strong>Olá, {{$customer->name}} - {{$customer->fantasy_name ?: $customer->fantasy_name}}</strong></p>

                                                <p>A sua fatura referente ao mês de {{$month}}  <strong>com vencimento no dia {{$due_date}}</strong> está em anexo e disponível para pagamento.</p>
                                                <p>Antes de realizar o pagamento, confira os valores e serviços descritos abaixo.</p>
                                                <p>Lembramos que caso não seja quitado até o dia
                                                <strong>{{$due_date}}, haverá aplicação de multa e mora a partir do dia seguinte.</strong></p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                            <table 0="[object Object]" 1="[object Object]" 2="[object Object]" border="0" style="cellspacing:0;color:#000;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;">
                                                <tr style="border-bottom:1px solid #ecedee;text-align:left;">
                                                    <th style="padding: 0 15px 10px 0;">Serviço</th>
                                                    <td style="padding: 0 15px;"></td>
                                                    <th style="padding: 0 0 0 15px; text-align: right" align="right">Valor</th>
                                                </tr>
                                                @foreach($services as $service)
                                                    <tr>
                                                        <td style="padding: 5px 15px 5px 0;">{{$service->name}}</td>
                                                        <td style="padding: 0 15px;"></td>
                                                        <td style="padding: 0 0 0 15px;" align="right">{{'R$ '. number_format($service->price, 2, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr style="border-bottom:2px solid #ecedee;text-align:left;padding:15px 0;">
                                                    <td style="padding: 5px 15px 5px 0; font-weight:bold">TOTAL</td>
                                                    <td style="padding: 0 15px;"></td>
                                                    <td style="padding: 0 0 0 15px; font-weight:bold" align="right">{{'R$ '. number_format($pix->original_price, 2, ',', '.') }}</td>
                                                </tr>
                                            </table>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                            <div style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;text-align:left;color:#525252;">
                                                <p><strong>Pix copia e cola: {{$pix->pix_copy_and_paste}}</strong></p>
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- @if(!empty(env('CHAVE_PIX'))) --}}
                                        <tr>
                                            <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                <div style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;text-align:left;color:#525252;">
                                                    <p><a class="btn-view" href="{{route('frontend.get.pix', ['token' => $pix->uuid])}}" target="_blank" rel="noopener noreferrer">PAGAR FATURA</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                    {{-- @endif --}}
                                    <tr>
                                        <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                            <div style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;text-align:left;color:#525252;">
                                                <p>Não consegue visualizar?</p>
                                                <p><a href="{{route('frontend.get.pix', ['token' => $pix->uuid])}}" target="_blank">{{route('frontend.get.pix', ['token' => $pix->uuid])}}</a></p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                            <div style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:12px;line-height:16px;text-align:left;color:#a2a2a2;">
                                                <p>Em caso de dúvidas, favor entrar em contato através do e-mail {{env('EMAIL_DUVIDA')}}.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                            <div style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:20px;text-align:left;color:#525252;">
                                                Atenciosamente, <br>
                                                 {{env('NAME_SIGNATURE_MAIL')}} <br>
                                                <a href="{{env('SITE_URL_REDIRECT_EMAIL')}}" style="color:#2F67F6">
                                                    {{env('SITE_URL_REDIRECT_EMAIL')}}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div style="Margin:0px auto;max-width:600px;">

            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                <tbody>
                    <tr>
                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</body>
</html>
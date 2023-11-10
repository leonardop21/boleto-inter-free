
# Gerador de boleto Inter

Sistema para geração e envio de boleto automaticamente através do banco inter para conta PJ (Não funciona para MEI)

# A versão pró agora inclui a capacidade de gerar, enviar e baixar automaticamente, bem como manualmente, boletos ou Pix.

## Versão Pro
- Cadastro de clientes
- Cadastro de Serviços
- Atrelar serviços a clientes
- Cadastro de feriados
- Geração automática de boleto, identifica feriados e finais de semana, adianta ou atrasa o vencimento do boleto (1 ~ 2 dias)
- Geração de boleto avulso
- Envio automático de boleto via E-mail
- Envio automático de boleto via WhatsApp
- Envio de boleto avulso via E-mail
- Envio de boleto avulso via WhatsApp
- Geração automática de Pix
- Geração de pix avulso
- Envio automático de pix por e-mail e WhatsApp
- Envio de pix avulso por e-mail e WhatsApp
- Consulta de saldo na conta do Inter
- Saldo em aberto de boletos não pagos no mês
- Saldo de boletos vencidos no mês
- Saldo de pix recebido
- Saldo total de boleto + pix recebido
- E-mail de lembrete de vencimento de fatura
- Baixa automática de boleto
- Baixa automática de Pix
- Alerta no WhatsApp sobre baixas automática no boleto
- Integração com o Bugsnag (Acomanhar erros na aplicação)


# Versão Free

- Cadastro de clientes
- Cadastro de Serviços
- Atrelar serviços a clientes
- Geração automática de boleto
- Geração de boleto avulso
- Envio automático de boleto via E-mail
- Envio automático de boleto via WhatsApp
- Envio de boleto avulso via E-mail
- Envio de boleto avulso via WhatsApp
- Consulta de saldo na conta do Inter



## Requerimentos

O projeto não irá funcionar sem um banco de dados em memória, recomendo o Redis

- PHP Version >= 7.3
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Redis ou Memcached (Recomendo o Redis)

## Instalação

Clone o projeto

```
  git clone https://github.com/leonardop21/boleto-inter
```

Execute o composer

```
composer install
```

Renomeie o .env.example para .env e preencha as variáveis. Exemplo de preenchimento das variáveis do projeto

```
DARK_MODE= HABILITA O TEMA ESCURO - TRUE or False"
PAGINATION_LIMIT= Limite de paginação no sistema ex: 10
TIME_CACHE_IN_SECONDS= Cache do sistema em segundos, ex: 604800

EMAIL_DUVIDA= E-mail que o cliente poderá responder
NOTIFY_SEND_BOLETO= Este e-mail receberá uma notificação após o envio ou falha nos envios
NAME_SIGNATURE_MAIL= Nome do email
SITE_URL_REDIRECT_EMAIL= Site que aparecerá no corpo do e-mail

#Inter
INTER_PATH_CRT=caminho_arquivo.crt
INTER_PATH_KEY=caminho_arquvivo.key
INTER_BASE_URL="https://cdpj.partners.bancointer.com.br/"
INTER_CLIENT_ID="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
INTER_CLIENT_SECRET="xxxxxxxxxxxxxxxxxxxxx"
INTER_CLIENT_SCOPE="extrato.read boleto-cobranca.read boleto-cobranca.write"


#PIX
CHAVE_PIX=

# Necessário preencher para envio de mensagens via WhastApp
WHATSAPP_BASE_URL= Url do projeto no WhatsAppp
WHATSAPP_SECRET_KEY= Chave Secreta
WHATSAPP_SESSION="Nome da sessão gerada no servidor do WhastApp"
NOTIFY_SEND_BOLETO_WHATSAPP="Número que irá receber uma mensgem, quando o envio pelo whats for finalziado"
```

Rode as migrations

```
php artisan migrate
```

Crie seu usuário através do Tinker

Faça login no sistema
- Cadastre um serviço
- Cadastre um cliente
- Atrele o serviço ao cliente

## Gerar boleto automaticamente

Para gerar o boleto automaticamente, rode o comando

```
php artisan ln:auto_generate_boleto
```

## Remover boletos e diretórios antigos
Para remover todos o boletos e um diretório antigo rode o comando abaixo, passando o ano e mês

```
php artisan ln:auto_generate_boleto 2022/09
```

Ou deixe o argumento vazio, para remover do mês passado.

```
php artisan ln:auto_generate_boleto
```

## Para saber como enviar através do WhatsApp, consulte a sessão "créditos"

O sistema irá procurar clientes com o status "ativo" e com serviços atrelados, irá gerar o boleto, enviar por e-mail e se o WhatsApp estiver configurado, também será enviado pelo WhatsApp.


### Logs
Você pode conferir os logs da geração do boleto e do envio na pasta /storage/logs/gerar-boleto/boleto-ano-mes-dia.log


## Screenshots


### Página inicial
![Página inicial](https://i.imgur.com/RsC5KcU.png)



### Cadastro de serviços
![Cadastro de serviços](https://i.imgur.com/SaXMvOf.png)


### Cadastro de Cliente
![Cadastro de serviços](https://i.imgur.com/7clMNgp.png)

### Geração de boleto avulso

![Boleto Avulso](https://i.imgur.com/TUyDNKh.png)

### Login no WhatsApp
![Login no WhatsApp](https://i.imgur.com/XWadznx.png)

### Envio via E-mail
![Envio de boleto por e-mail](https://i.imgur.com/YGAAf3z.png)

### Envio via WhatsApp 
![Envio via WhatsApp](https://i.imgur.com/uAS1xCl.png)

### Envio Via WhatsApp Anexo
![Envio anexo](https://i.imgur.com/oZzvVtl.png)


## Suporte pago
### Precisa de ajuda para instalar e configurar? Estou disponível para ajudar! Orçamento por hora e demanda.


## Créditos
- [Template para envio de e-mails - ckissi](https://github.com/ckissi/responsive-html-email-templates)
- [Lib envio de mensagem no WhatsApp - WPPCONNECT-TEAM](https://github.com/wppconnect-team)
- [Painel administrativo, template - ColorlibHQ](https://github.com/ColorlibHQ/AdminLTE)

## Este projeto é útil para você? Aceito um café ☕

Este projeto te auxiliou de alguma forma? Então, que tal me pagar um café? ☕☕
## Chave pix: 9b9835e9-7161-4f07-baba-d4a0e7c8430b

<img src="https://i.imgur.com/NxyfW7K.jpg" height="400">
## License

[Mozilla Public License 2.0](https://choosealicense.com/licenses/mpl-2.0/)



<h3 align="left">Connect with me:</h3>
<p align="left">
</p>

<h3 align="left">Languages and Tools:</h3>
<p align="left"> <a href="https://getbootstrap.com" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/bootstrap/bootstrap-plain-wordmark.svg" alt="bootstrap" width="40" height="40"/> </a> <a href="https://www.w3schools.com/css/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original-wordmark.svg" alt="css3" width="40" height="40"/> </a> <a href="https://git-scm.com/" target="_blank" rel="noreferrer"> <img src="https://www.vectorlogo.zone/logos/git-scm/git-scm-icon.svg" alt="git" width="40" height="40"/> </a> <a href="https://www.w3.org/html/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original-wordmark.svg" alt="html5" width="40" height="40"/> </a> <a href="https://laravel.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/laravel/laravel-plain-wordmark.svg" alt="laravel" width="40" height="40"/> </a> <a href="https://www.linux.org/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/linux/linux-original.svg" alt="linux" width="40" height="40"/> </a> <a href="https://www.mysql.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original-wordmark.svg" alt="mysql" width="40" height="40"/> </a> <a href="https://www.php.net" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" alt="php" width="40" height="40"/> </a> <a href="https://redis.io" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/redis/redis-original-wordmark.svg" alt="redis" width="40" height="40"/> </a> <a href="https://vuejs.org/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/vuejs/vuejs-original-wordmark.svg" alt="vuejs" width="40" height="40"/> </a> <a href="https://webpack.js.org" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/d00d0969292a6569d45b06d3f350f463a0107b0d/icons/webpack/webpack-original-wordmark.svg" alt="webpack" width="40" height="40"/> </a> </p>

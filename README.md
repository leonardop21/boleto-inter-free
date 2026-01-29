
## Gerador de boleto Inter / Pix / NFe

Sistema em Laravel para **geração, envio e gestão de boletos, Pix e NF-e** integrado ao **Banco Inter** e a provedores de NF-e, com recursos de automação, cache e notificações (e-mail, sms, ligação e WhatsApp via **Notifish**).

### Sumário

- [Licença](#licença)
- [FAQ – Boleto Inter Pro](#faq--boleto-inter-pro)
- [Principais funcionalidades](#principais-funcionalidades)
- [Requisitos](#requisitos)
- [Arquitetura do sistema](#arquitetura-do-sistema)
- [Instalação](#instalação)
  - [Configurações gerais](#configurações-gerais)
  - [Configurações visuais do e-mail (não padrão Laravel)](#configurações-visuais-do-e-mail-não-padrão-laravel)
  - [E-mail](#e-mail)
  - [Banco Inter (Boletos / Pix / Webhooks)](#banco-inter-boletos--pix--webhooks)
  - [Pix](#pix)
  - [WhatsApp (Notifish)](#whatsapp-notifish)
  - [Pusher (atualização em tempo real na página Pix)](#pusher-atualização-em-tempo-real-na-página-pix)
  - [Bugsnag (monitoramento de erros)](#bugsnag-monitoramento-de-erros)
  - [NF-e (serviço externo)](#nf-e-serviço-externo)
  - [Contabilidade (extrato em PDF)](#contabilidade-extrato-em-pdf)
- [Docker (produção)](#docker-produção)
  - [Subir em produção](#subir-em-produção)
  - [Rodar migrations](#rodar-migrations)
  - [Certificados do Banco Inter no Docker](#certificados-do-banco-inter-no-docker)
  - [Requisito de imagem do Pix no Docker (GD/"imagekick")](#requisito-de-imagem-do-pix-no-docker-gdimagekick)
- [Geração automática de boletos](#geração-automática-de-boletos)
  - [Comando de geração automática](#comando-de-geração-automática)
  - [Falha na geração de boleto](#falha-na-geração-de-boleto)
  - [Geração automática de Pix](#geração-automática-de-pix)
  - [Logs](#logs)
  - [Extrato para contabilidade](#extrato-para-contabilidade)
- [Comandos Artisan (detalhado)](#comandos-artisan-detalhado)
- [Screenshots](#screenshots)
  - [Página inicial](#página-inicial)
  - [Cadastro de serviços](#cadastro-de-serviços)
  - [Cadastro de Cliente](#cadastro-de-cliente)
  - [Geração de boleto avulso](#geração-de-boleto-avulso)
  - [Login no WhatsApp (Notifish)](#login-no-whatsapp-notifish)
  - [Envio via E-mail](#envio-via-e-mail)
  - [Envio via WhatsApp](#envio-via-whatsapp)
  - [Envio Via WhatsApp Anexo](#envio-via-whatsapp-anexo)
- [Créditos](#créditos)
- [Este projeto é útil para você? Aceito um café ☕](#este-projeto-é-útil-para-você-aceito-um-café-)

---

## Licença

Este projeto é disponibilizado sob uma **licença proprietária de uso não comercial**.

**Você pode:**

- Usar o código
- Estudar e modificar o código
- Utilizar em projetos pessoais, educacionais ou internos

**Você NÃO pode:**

- Vender o software, no todo ou em parte
- Sublicenciar o código
- Oferecer o software ou versões derivadas como serviço pago (SaaS, suporte, revenda, etc.)
- Comercializar versões modificadas sem autorização expressa do autor

**Uso comercial**

Qualquer uso comercial, direto ou indireto, exige licenciamento comercial prévio.

Para licenciamento comercial, entre em contato

**Observação importante**

Este projeto é fornecido "como está", sem garantias. O autor não se responsabiliza por danos diretos ou indiretos decorrentes do uso do software.

---

## FAQ – Boleto Inter Pro

---

### O projeto vai funcionar para mim?

Este projeto foi desenvolvido para atender às minhas próprias necessidades, com base em cenários reais de uso em produção. Embora seja funcional e estável, não há garantia de que ele atenderá a todos os casos de uso ou perfis de negócio.

**Recomendação:** avalie se a solução se encaixa no seu contexto antes de utilizá-la.

---

### Por que você decidiu tornar o projeto público? Ele ainda é utilizado?

**Sim.** Este projeto é utilizado em produção há mais de 5 anos. A decisão de torná-lo público foi tomada para ajudar outros desenvolvedores e prestadores de serviços, especialmente quem está iniciando e precisa de uma base confiável para integrações com o Boleto Inter.

Existe a intenção de lançar uma nova versão, com melhorias arquiteturais, novas funcionalidades e compatibilidade com a última versão do Laravel, porém ainda não há uma data definida para esse lançamento.

---

### Está prevista a utilização de Inteligência Artificial em uma nova versão?

É uma possibilidade. A ideia de incorporar recursos de IA existe, mas não há cronograma definido para o início do desenvolvimento da nova versão.

---

### O projeto continuará recebendo atualizações?

Não de forma pública. Este repositório representa uma versão estável e funcional. As evoluções futuras estão planejadas para uma nova versão do sistema, com outro ciclo de desenvolvimento e melhorias mais amplas.

---

### Não consegui instalar o projeto. Você oferece suporte?

**Sim.** É possível contratar suporte técnico pago, que pode incluir:

| Serviço | Descrição |
|--------|------------|
| Instalação completa | Configuração do sistema do zero |
| Configuração e validação | Ajustes e validação de funcionamento |
| Configuração de servidor | Opcional, com custo adicional |

Os valores variam conforme a complexidade do ambiente e do serviço solicitado.

---

### A Nota Fiscal pode ser gerada para qualquer prefeitura?

**Não.** Atualmente, a geração de Nota Fiscal está disponível apenas para prefeituras que utilizam o sistema **IPM Fiscal**.

---

### Preciso contratar um serviço de WhatsApp para envio de mensagens e boletos?

**Sim.** O projeto utiliza a API do **Notifish**. É necessário contratar o serviço em:

→ <a href="https://notifish.com" target="_blank" rel="noopener noreferrer">notifish.com</a>

---

### Qual sistema de SMS é utilizado para o envio das mensagens de cobrança?

O envio de SMS é realizado através do serviço:

→ <a href="https://smsdev.com.br" target="_blank" rel="noopener noreferrer">smsdev.com.br</a>

---

### Qual sistema de voz (voice/torpedo de voz) é utilizado para as mensagens de cobrança?

O envio de mensagens por voz utiliza o serviço:

→ <a href="https://www.kingsms.com.br/torpedo-de-voz" target="_blank" rel="noopener noreferrer">kingsms.com.br – Torpedo de voz</a>

---

### Este projeto foi útil para você ou gerou algum faturamento?

Se este projeto te ajudou de alguma forma, considere apoiar o trabalho.

Contribuições como um **café** ☕, uma **cerveja** 🍺 ou um **almoço** 🍔 ajudam a manter projetos como este ativos e evoluindo.

---

### Principais funcionalidades

- **Clientes e serviços**
  - Cadastro de clientes (`Customer`)
  - Cadastro de serviços (`Service`)
  - Vínculo de serviços a clientes
  - Tipos de nota fiscal por cliente (`CustomerNfeType`)
- **Boletos (Banco Inter)**
  - Geração de boleto automático (recorrente) e avulso
  - Envio automático de boleto via **e-mail**
  - Envio automático de boleto via **WhatsApp (Notifish)**
  - Reenvio de boletos vencidos / lembretes
  - Cancelamento de boleto direto no Inter
  - Download de PDF do boleto (cacheado em `storage/app/boleto/pdf/ano/mes/`)
  - **URL assinada** para download do PDF do boleto (uso pelo Notifish): rota pública `/ln/boleto/pdf` com parâmetro `our_number` (identificador do Inter), expira em 10 minutos; não expõe o ID interno
  - Atualização automática de status via **webhook** do Inter
- **Pix**
  - Geração de cobranças Pix com integração Inter (`InterService::generatePaymentPix`)
  - **Geração automática de Pix** (comando `ln:auto_generate_pix`): mesmo critério de data de vencimento que boletos (próximo mês, último dia do mês, feriados); logs e Bugsnag em caso de falha
  - Página pública de pagamento (`/ln/pagamento/pix/...`) com botão "Copiar código Pix" e atualização em tempo real do status (Pusher, opcional)
  - Geração de QRCode Pix e recibo
  - Webhook Pix (Banco Inter) para atualização de pagamentos
- **NF-e (Nota Fiscal de Serviço)**
  - Geração de XML de NF-e com base nos serviços do cliente (`NfeRepository::createNote`)
  - Envio do XML para o webservice configurado (`NfeService`)
  - Persistência dos dados da NF-e e link de consulta
  - Envio automático da NF-e por e-mail para o cliente
  - Geração automática de NF-e complementar de juros/mora quando o valor pago > valor nominal do boleto
- **WhatsApp**
  - Login e gestão de sessão via **Notifish** (instância WhatsApp)
  - Envio de:
    - Boleto por WhatsApp (anexo): payload Notifish com `identifier: "boleto"`, `media` com `type`, `archive` (URL assinada), `filename` (baseado no nome fantasia do cliente, sem caracteres especiais, sempre `.pdf`) e `caption` com data de vencimento (coluna `due_date` da tabela boletos), ex.: *"Boleto de {nome fantasia} com vencimento para dd/mm/YYYY. Após o vencimento haverá incidência de juros e multa."*
    - Avisos de falha/sucesso de envio de boletos
    - Notificações de callback do Inter (pagamentos, cancelamentos, etc.)
    - Aviso interno no WhatsApp quando o cliente paga (boletos e Pix), via callbacks do Banco Inter
- **Relatórios e dashboard**
  - Consulta de saldo em conta (`InterService::getSaldo`)
  - Relatório mensal de receitas/gastos (`HomeController::getMonthReport`)
  - Cálculo de valor total de boletos pagos/em aberto no mês atual
  - **Extrato para contabilidade**: comando que gera o extrato em PDF do **mês anterior** (Banco Inter), envia automaticamente ao contador por **e-mail** e **WhatsApp**, e registra cada envio no banco (`contabilidades`). Se o seu contador aceita receber o extrato em PDF, basta configurar as variáveis de contabilidade no `.env` e ativar o comando (inclusive via agendamento).
- **Agenda e feriados**
  - Cadastro de feriados (`Holiday`)
  - Ajuste automático de vencimento, juros e mora considerando feriados (`HolidayRepository`, regras em `BoletoRepository`)

---

## Requisitos

  **PHP**: >= 7.3 e <= 8.1  (Testado nessas versões)
- **Extensões**:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
- **Processamento de imagem (Pix QRCode)**:
  - O endpoint de imagem do Pix (`/ln/pagamento/pix/imagem/{uuid}`) usa a biblioteca `intervention/image`.
  - Para funcionar, o PHP precisa ter suporte a imagens via **GD** (mais comum) ou **Imagick/ImageMagick** (“imagekick”).
- **Banco de dados**: MySQL/MariaDB
- **Cache / Sessão**:
  - Redis (recomendado) ou Memcached  
  O projeto faz uso intenso de cache (via `Cache::tags`) em repositórios como `BoletoRepository`, `NfeRepository` etc., portanto **é obrigatório ter um cache em memória configurado**.

---

## Arquitetura do sistema

- **Framework**: Laravel 8 (`laravel/framework` ^8.75)
- **Padrão de projeto**:
  - Controllers finos (`app/Http/Controllers`)
  - Repositórios (`app/Repositories`) implementando interfaces (`app/Interfaces`)
  - Serviços externos em `app/Services` (`InterService`, `LnService`, `NfeService`)
  - Observers para eventos de modelo (`app/Observers`)
  - Jobs/rotinas via comandos Artisan em `app/Console/Commands`
- **Principais componentes**:
  - `InterService`: integração com Banco Inter (boletos, Pix, saldo, extrato, webhooks, PDF)
  - `LnService`: camada fina sobre `Http`/cURL para requisições HTTP/REST
  - `NfeService` + `NfeRepository`: integração com webservice de NF-e
  - `BoletoRepository`: lógica de negócio de boletos (geração, cancelamento, PDF, e-mail, webhook, NF-e de juros)
  - `PixRepository`: lógica de negócio de Pix
  - `NotifishRepository`: comunicação com WhatsApp via Notifish
  - `HolidayRepository`: feriados que impactam vencimentos
  - Controllers backend (prefixo `/dashboard`) e frontend (prefixo `/ln`)

Rotas principais (resumo):

- Backend (`middleware auth`, prefixo `/dashboard`):
  - `/` – Dashboard (`HomeController@index`)
  - `/cliente/...` – CRUD de clientes
  - `/servico/...` – CRUD de serviços
  - `/fatura/boleto/...` – Boletos (listar, gerar, cancelar, enviar e-mail, webhook)
  - `/fatura/pix/...` – Pix (listar, gerar, webhook)
  - `/whatsapp/...` – Login, status e envios WhatsApp
  - `/nota-fiscal/...` – Listagem, emissão e envio de NF-e
  - `/feriado/...` – CRUD de feriados
- Frontend (prefixo `/ln`):
  - `/ln/pagamento/pix/{token}` – Página de pagamento Pix
  - `/ln/pagamento/pix/imagem/{uuid}` – Imagem QRCode Pix
  - `/ln/pagamento/pix/recibo/{uuid}` – Recibo Pix
  - `/ln/boleto/pdf` – Download do PDF do boleto via **URL assinada** (query: `our_number`, `signature`, `expires`); usada pelo Notifish para enviar o anexo; expira em 10 min

---

## Instalação

1. **Clonar o projeto**

```bash
git clone https://github.com/leonardop21/boleto-inter.git
cd boleto-inter
```

2. **Instalar dependências PHP**

```bash
composer install
```

3. **Configurar o `.env`**

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

Preencha as variáveis abaixo de acordo com o seu ambiente.

### Configurações gerais

```env
APP_NAME="Gerador Boleto Inter"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database
DB_USERNAME=user
DB_PASSWORD=pass

CACHE_DRIVER=redis
SESSION_DRIVER=redis
TIME_CACHE_IN_SECONDS=604800   # Cache em segundos (ex: 7 dias)

DARK_MODE=1                    # 1 para tema escuro habilitado
PAGINATION_LIMIT=10            # Limite de paginação padrão
```

### Configurações visuais do e-mail (não padrão Laravel)

```env
LOGO_SITE_EMAIL="https://exemplo.com/logo.png"
BACKGROUND_EMAIL="#090324"
```

### E-mail

```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

EMAIL_DUVIDA=                  # E-mail de resposta do cliente
NOTIFY_SEND_BOLETO=            # E-mail que recebe notificações de sucesso/erro de envio
NAME_SIGNATURE_MAIL=           # Nome que aparecerá na assinatura
SITE_URL_REDIRECT_EMAIL=       # URL exibida no corpo do e-mail
```

### Banco Inter (Boletos / Pix / Webhooks)

```env
# Caminhos para certificados (arquivo físico .crt e .key)
INTER_PATH_CRT=/caminho/para/arquivo.crt
INTER_PATH_KEY=/caminho/para/arquivo.key

# URL base do parceiro PJ
INTER_BASE_URL="https://cdpj.partners.bancointer.com.br/"

# Credenciais OAuth2
INTER_CLIENT_ID="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
INTER_CLIENT_SECRET="xxxxxxxxxxxxxxxxxxxxx"
INTER_CLIENT_SCOPE="extrato.read boleto-cobranca.read boleto-cobranca.write"

# Webhooks
INTER_WEBHOOK_URL="https://seu-sistema.com/api/inter/webhook/boleto"
INTER_WEBHOOK_URL_PIX="https://seu-sistema.com/api/inter/webhook/pix"   # se utilizado
```

### Pix

```env
CHAVE_PIX=seu_cnpj_ou_chave_pix
BENEFICIARIO_PIX="NOME DO BENEFICIÁRIO"
CNPJ_EMPRESA="00.000.000/0000-00"
CIDADE_EMPRESA="SUA CIDADE"
EMAIL_RECIBO="contato@empresa.com.br"
```

### WhatsApp (Notifish)

```env
# Base URL da API Notifish (v2)
NOTIFISH_BASE_URL="https://seu-subdominio.notifish.com/api/v2/"
# API Key Notifish
NOTIFISH_API_KEY="sua_api_key"
# UUID da instância (sessão WhatsApp)
NOTIFISH_UUID="uuid-da-instancia"
# Número que recebe alertas internos (DDI+DDD+Número)
NOTIFY_SEND_BOLETO_WHATSAPP="5599999999999"
```

### Pusher (atualização em tempo real na página Pix)

A página pública de pagamento Pix (`/ln/pagamento/pix/...`) pode exibir a confirmação de pagamento em tempo real via Pusher. Opcional.

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
```

### Bugsnag (monitoramento de erros)

Falhas na geração automática de boletos e Pix (e outros erros da aplicação) podem ser enviadas ao Bugsnag quando configurado.

```env
BUGSNAG_API_KEY=
```

### NF-e (serviço externo)

```env
NFE_BASE_URL="https://url-do-webservice-de-nfe"
AUTHORIZATION_NFE="token_ou_credencial_em_base64"

# Prestador
CNPJ_NF_PRESTADOR="00000000000000"
CIDADE_ID_NFE="0000000"        # Código do município para o provedor de NF-e
```

### Contabilidade (extrato em PDF)

Opcional. Usado pelo comando `ln:process-pdf-report-and-sent-email-e-whats-app`, que gera o extrato em PDF do mês anterior e envia ao contador por e-mail e WhatsApp (além de salvar no banco). Se o seu contador permite receber o extrato em PDF, preencha as variáveis abaixo e agende o comando conforme necessário.

```env
# E-mail do contador (contabilidade)
CONTABILIDADE_EMAIL=
# WhatsApp do contador (DDI+DDD+Número)
CONTABILIDADE_WHATSAPP=
# Nome da contabilidade (para registro no banco e logs)
CONTABILIDADE_NAME=
```

4. **Gerar key da aplicação**

```bash
php artisan key:generate
```

5. **Rodar as migrations**

```bash
php artisan migrate
```

6. **Criar usuário**

Crie seu usuário via Tinker ou usando o seeder/registro manual:

```bash
php artisan tinker
>>> \App\Models\User::factory()->create();
```

7. **Acessar o sistema**

- Faça login no sistema
- Cadastre um serviço
- Cadastre um cliente
- Vincule o serviço ao cliente

---

## Docker (produção)

O projeto possui um setup de produção com:

- **Nginx**
- **PHP-FPM (Laravel)**
- **MySQL**
- **Redis** (cache e sessão)
- **GD/Imagick** (requisito para gerar/servir imagem do Pix QRCode)

Arquivos:

- `docker-compose.prod.yml`
- `docker/php/Dockerfile`
- `docker/nginx/default.conf`

### Subir em produção

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

### Rodar migrations

```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### Certificados do Banco Inter no Docker

Para o Inter funcionar, a pasta `inter-certf/` deve existir no projeto e conter:

- `inter_api_certificado.crt`
- `inter_api_chave.key`

No Docker, esses arquivos são montados em `/var/www/inter-certf` e as variáveis `INTER_PATH_CRT` e `INTER_PATH_KEY` apontam para esses caminhos.

### Requisito de imagem do Pix no Docker (GD/“imagekick”)

O container `app` já vem com **GD habilitado** (necessário para `intervention/image` servir o QRCode do Pix).

Se você preferir usar **Imagick/ImageMagick** (“imagekick”), será necessário instalar a extensão `imagick` no `docker/php/Dockerfile`.

## Geração automática de boletos

O sistema permite **gerar boletos automaticamente** para todos os clientes ativos com serviços vinculados, enviando os boletos por e-mail (e, se configurado, por WhatsApp).

### Comando de geração automática

```bash
php artisan ln:auto_generate_boleto
```

- O comando:
  - Localiza clientes com status **ativo**
  - Gera boletos baseados nas configurações do cliente (valor, vencimento, juros, mora, dias após vencimento etc.)
  - **Data de vencimento** (dia configurado no cadastro do cliente):
    - Se o dia **já tiver passado** no mês atual (data retroativa), o vencimento é jogado para o **próximo mês** com o mesmo dia (respeitando o último dia do mês).
    - Se o dia configurado for **31** e o mês tiver menos dias (ex.: 28 ou 30), é usado o **último dia do mês**.
    - Se o vencimento for **hoje**, o boleto é gerado para hoje (não é jogado para o próximo mês).
  - Ajusta datas considerando feriados (via `HolidayRepository`)
  - Gera o boleto no Banco Inter
  - Baixa/gera o PDF e o armazena em `storage/app/boleto/pdf/...`
  - Dispara o envio por e-mail e, se habilitado, por WhatsApp

Você pode agendar esse comando no `App\Console\Kernel` usando o scheduler do Laravel para rodar diariamente/mensalmente, conforme sua necessidade.

### Falha na geração de boleto

- O **motivo da falha** (resposta da API Inter: violações, `detail`, etc.) é registrado no log:
  - `storage/logs/gerar-boleto/boleto.log` — mensagem no formato: *"Falha ao gerar boleto para {nome}. Motivo: {detalhe}"*
- A falha também é enviada ao **Bugsnag** (se configurado), com metadata: `customer_id`, `customer_name`, `motivo`, `status_code`, `raw_body`.

### Geração automática de Pix

O sistema permite **gerar Pix automaticamente** para clientes ativos com serviços vinculados, enviando o link de pagamento por e-mail e, se configurado, por WhatsApp.

```bash
php artisan ln:auto_generate_pix
```

- O comando utiliza a **mesma lógica de data de vencimento** do boleto:
  - Se o dia já tiver passado no mês atual, o vencimento vai para o **próximo mês** (mesmo dia, último dia do mês quando for 31).
  - Feriados são considerados via `HolidayRepository`.
- Em caso de **falha**, o motivo é registrado em `storage/logs/gerar-pix/pix.log` e enviado ao **Bugsnag** (se configurado), com contexto do cliente e da resposta da API Inter.
- Você pode agendar o comando no `App\Console\Kernel` (scheduler) para rodar diariamente ou conforme a necessidade.

### Logs

- **Geração de boleto / Pix / NF-e**:
  - `storage/logs/gerar-boleto/boleto.log`
  - `storage/logs/gerar-pix/pix.log`
  - `storage/logs/gerar-nfe/nfe.log`
- **Callback Inter**:
  - `storage/logs/callback/callback.log`

Arquivos de log adicionais podem ser gerados por comandos/integrações específicas.

### Extrato para contabilidade

Existe um comando que **gera o extrato em PDF do mês anterior** (Banco Inter), **envia automaticamente** para o contador por **e-mail** e **WhatsApp**, e **salva cada envio no banco** (tabela `contabilidades`). Se o seu contador permite receber o extrato em PDF, você pode ativar essa funcionalidade configurando no `.env` o e-mail e o WhatsApp da contabilidade (e o nome, para registro) e rodando o comando manualmente ou via agendamento (ex.: todo dia 1º do mês). O comando é `ln:process-pdf-report-and-sent-email-e-whats-app`; ele também está listado na tabela [Relatório e manutenção](#relatório-e-manutenção).

---

## Comandos Artisan (detalhado)

Todos os comandos abaixo podem ser **agendados via cron** usando o **Laravel Scheduler** no `App\Console\Kernel`. Exemplo de cron para executar o scheduler a cada minuto:

```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

No método `schedule()` do `Kernel` você define, por exemplo: `$schedule->command('ln:auto_generate_boleto')->monthlyOn(1, '06:00');` para gerar boletos todo dia 1 às 6h.

O sistema inclui comandos de **cobrança** para boletos e Pix: lembretes de **vencimento** (antes de vencer), lembretes de **vencido** (após o vencimento), e envio por **e-mail**, **WhatsApp**, **SMS** e **voz (Voicer)**. Assim você pode automatizar toda a rotina de geração, envio e cobrança.

### Geração automática

| Comando | Descrição |
|--------|------------|
| `php artisan ln:auto_generate_boleto` | Gera boletos automaticamente para clientes ativos com serviços definidos. Envia por e-mail e, se configurado, por WhatsApp. Respeita data de vencimento, feriados e último dia do mês. |
| `php artisan ln:auto_generate_pix` | Gera Pix e QR Code para clientes ativos com serviços definidos. Mesma lógica de vencimento do boleto. Envia link por e-mail e WhatsApp. |
| `php artisan ln:generate-nfe` | Gera NF-e automaticamente para todos os clientes ativos com serviços atrelados (prefeituras com IPM Fiscal). |

### Envio avulso (por ID)

| Comando | Descrição |
|--------|------------|
| `php artisan ln:send-boleto {boletos_id}` | Envia o boleto por e-mail e dispara o envio pelo WhatsApp (Notifish). |
| `php artisan ln:send-whatsapp {boletos_id}` | Envia apenas via WhatsApp (Notifish) o boleto informado. |
| `php artisan ln:send_pix {pixes_id}` | Envia o Pix por e-mail e Notifish (WhatsApp). |
| `php artisan ln:send-nfe {nfes_id}` | Envia a NF-e por e-mail e notifica via WhatsApp. |

### Lembretes de vencimento e vencido (cobrança)

| Comando | Descrição |
|--------|------------|
| `php artisan ln:resend-boleto` | **Lembrete de vencimento** de boleto (fatura próxima do vencimento). Reenvia boletos que estão no período de lembrete. |
| `php artisan ln:resend-duedate-boleto` | **Lembrete de boleto vencido**. Reenvia boletos já vencidos para cobrança. |
| `php artisan ln:resend-pix` | **Lembrete de vencimento** de Pix (fatura próxima do vencimento). |
| `php artisan ln:resend-duedate-pix` | **Lembrete de Pix vencido**. Reenvia Pix vencidos para cobrança. |

Esses comandos podem ser agendados no cron (ex.: `ln:resend-boleto` todo dia às 8h; `ln:resend-duedate-boleto` em dias alternados após o vencimento).

### Cobrança por SMS

Requer configuração de `TOKEN_SMS` no `.env` e uso do serviço [SMS Dev](https://smsdev.com.br).

| Comando | Descrição |
|--------|------------|
| `php artisan ln:send-boleto-sms` | Envia **SMS** de cobrança para boletos **vencidos** (lista obtida pela mesma regra do lembrete de vencido). |
| `php artisan ln:send-pix-sms` | Envia **SMS** de cobrança para Pix **vencidos**. |

### Cobrança por voz (Voicer / torpedo de voz)

Requer configuração de `VOICER_TOKEN`, `VOICER_LOGIN` e `VOICER_AUDIO` no `.env` e uso do serviço [King SMS – Torpedo de voz](https://www.kingsms.com.br/torpedo-de-voz).

| Comando | Descrição |
|--------|------------|
| `php artisan ln:send-boleto-voicer` | Consulta boletos **vencidos** e envia cobrança por **voz** (torpedo de voz). |
| `php artisan ln:send-pix-voicer` | Consulta Pix **vencidos** e envia cobrança por **voz** (torpedo de voz). |

### Relatório e manutenção

| Comando | Descrição |
|--------|------------|
| `php artisan ln:process-pdf-report-and-sent-email-e-whats-app` | Gera o extrato em PDF do mês anterior (Banco Inter), envia ao contador por e-mail e WhatsApp e salva o registro no banco (`contabilidades`). Ative via `.env` se o contador aceita receber o extrato em PDF. |
| `php artisan ln:downloadBoleto {boletos_id}` | Baixa o PDF do boleto gerado no Inter (útil para conferência ou armazenamento manual). |
| `php artisan ln:mount_qr_code {pixes_id}` | Gera/atualiza o QR Code do Pix informado (quando o Pix já existe no Inter). |
| `php artisan ln:remove-old-pdf-boleto {dir?}` | Remove PDFs de boletos antigos do diretório informado (ou do diretório padrão). Útil para limpeza de disco. |

---

## Screenshots


### Página inicial
![Página inicial](https://i.imgur.com/RsC5KcU.png)



### Cadastro de serviços
![Cadastro de serviços](https://i.imgur.com/SaXMvOf.png)


### Cadastro de Cliente
![Cadastro de serviços](https://i.imgur.com/7clMNgp.png)

### Geração de boleto avulso

![Boleto Avulso](https://i.imgur.com/TUyDNKh.png)

### Login no WhatsApp (Notifish)
![Login no WhatsApp](https://i.imgur.com/XWadznx.png)

### Envio via E-mail
![Envio de boleto por e-mail](https://i.imgur.com/YGAAf3z.png)

### Envio via WhatsApp 
![Envio via WhatsApp](https://i.imgur.com/uAS1xCl.png)

### Envio Via WhatsApp Anexo
![Envio anexo](https://i.imgur.com/oZzvVtl.png)
## Créditos

- [Template E-mail - ckissi](https://github.com/ckissi/responsive-html-email-templates)
- [wppconnect-team](https://github.com/wppconnect-team)
- [ColorlibHQ](https://github.com/ColorlibHQ/AdminLTE)

## Este projeto é útil para você? Aceito um café ☕

Este projeto te auxiliou de alguma forma? Então, que tal me pagar um café? ☕☕



<h3 align="left">Connect with me:</h3>
<p align="left">
</p>

<h3 align="left">Languages and Tools:</h3>
<p align="left"> <a href="https://getbootstrap.com" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/bootstrap/bootstrap-plain-wordmark.svg" alt="bootstrap" width="40" height="40"/> </a> <a href="https://www.w3schools.com/css/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original-wordmark.svg" alt="css3" width="40" height="40"/> </a> <a href="https://git-scm.com/" target="_blank" rel="noreferrer"> <img src="https://www.vectorlogo.zone/logos/git-scm/git-scm-icon.svg" alt="git" width="40" height="40"/> </a> <a href="https://www.w3.org/html/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original-wordmark.svg" alt="html5" width="40" height="40"/> </a> <a href="https://laravel.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/laravel/laravel-plain-wordmark.svg" alt="laravel" width="40" height="40"/> </a> <a href="https://www.linux.org/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/linux/linux-original.svg" alt="linux" width="40" height="40"/> </a> <a href="https://www.mysql.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original-wordmark.svg" alt="mysql" width="40" height="40"/> </a> <a href="https://www.php.net" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" alt="php" width="40" height="40"/> </a> <a href="https://redis.io" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/redis/redis-original-wordmark.svg" alt="redis" width="40" height="40"/> </a> <a href="https://vuejs.org/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/vuejs/vuejs-original-wordmark.svg" alt="vuejs" width="40" height="40"/> </a> <a href="https://webpack.js.org" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/d00d0969292a6569d45b06d3f350f463a0107b0d/icons/webpack/webpack-original-wordmark.svg" alt="webpack" width="40" height="40"/> </a> </p>

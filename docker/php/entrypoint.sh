#!/bin/sh
set -e

# Aguardar MySQL estar pronto
echo "Aguardando MySQL..."
while ! nc -z mysql 3306; do
  sleep 0.1
done
echo "MySQL está pronto!"

# Aguardar Redis estar pronto
echo "Aguardando Redis..."
while ! nc -z redis 6379; do
  sleep 0.1
done
echo "Redis está pronto!"

# Gerar chave da aplicação se não existir ou estiver vazia
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "Gerando chave da aplicação..."
    php artisan key:generate --force || true
fi

# Limpar e cachear configurações (apenas se o banco estiver configurado)
if php artisan migrate:status >/dev/null 2>&1; then
    echo "Cacheando configurações..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
else
    echo "Aviso: Banco de dados não configurado ainda. Execute 'php artisan migrate' após configurar o .env"
fi

# Executar migrations (opcional - descomente se necessário)
# php artisan migrate --force

exec "$@"

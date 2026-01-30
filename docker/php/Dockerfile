FROM php:8.1-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    nodejs \
    npm \
    netcat-openbsd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto (exceto os que estão no .dockerignore)
COPY . .

# Copiar .env.example para .env se .env não existir
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Instalar dependências do Composer (sem dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Instalar dependências do NPM
RUN npm ci --production=false

# Build dos assets para produção
RUN npm run production

# Limpar cache do npm após build
RUN rm -rf node_modules

# Copiar script de entrada
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expor porta do PHP-FPM
EXPOSE 9000

# Usar script de entrada
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Comando padrão
CMD ["php-fpm"]

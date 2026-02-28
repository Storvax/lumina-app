FROM php:8.4-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    cron \
    git \
    curl \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Node.js 18
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copiar configurações do Nginx
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Copiar configuração do Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Definir directório de trabalho
WORKDIR /var/www/html

# Copiar código da aplicação
COPY . .

# Instalar dependências PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Compilar assets (Vite)
RUN npm install && npm run build && rm -rf node_modules

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
#!/bin/bash
set -e

echo "==> [1/6] A criar base de dados SQLite..."
DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
mkdir -p "$(dirname "$DB_PATH")"
touch "$DB_PATH"
chown -R www-data:www-data "$(dirname "$DB_PATH")"

echo "==> [2/6] A corrigir permissões..."
mkdir -p \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> [3/6] Cache de configurações Laravel..."
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true

echo "==> [4/6] Migrações..."
php artisan migrate --force || true

echo "==> [5/6] Permissões pós-migração..."
chown -R www-data:www-data "$(dirname "$DB_PATH")"

echo "==> [6/6] A iniciar supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
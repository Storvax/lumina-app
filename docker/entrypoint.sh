#!/bin/sh
set -e

# ── Base de dados SQLite ──
mkdir -p /var/www/html/storage/database
if [ ! -f /var/www/html/storage/database/database.sqlite ]; then
    touch /var/www/html/storage/database/database.sqlite
fi

# ── Permissões ──
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ── Caches do Laravel ──
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# ── Migrações ──
echo "[entrypoint] A correr migrations..."
php /var/www/html/artisan migrate --force

# Corrigir ownership após migrate (SQLite WAL files)
chown -R www-data:www-data /var/www/html/storage/database

# ── Laravel Scheduler via cron ──
echo "* * * * * www-data php /var/www/html/artisan schedule:run >> /dev/null 2>&1" \
    > /etc/cron.d/laravel
chmod 0644 /etc/cron.d/laravel
crontab /etc/cron.d/laravel

# ── Arrancar Supervisor (Nginx + PHP-FPM + Cron) ──
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf

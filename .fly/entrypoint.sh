#!/usr/bin/env sh

# ── Setup rápido ──

# Garante que a pasta e o ficheiro da DB existem
mkdir -p /var/www/html/storage/database
if [ ! -f /var/www/html/storage/database/database.sqlite ]; then
    touch /var/www/html/storage/database/database.sqlite
fi

# Permissões no volume mount + bootstrap/cache
chown -R www-data:www-data /var/www/html/storage/database
chmod -R 775 /var/www/html/storage/database
chown -R www-data:www-data /var/www/html/bootstrap/cache

# ── Migrations (crítico — esquema DB tem de estar pronto antes de servir pedidos) ──
# Demora ~2-3s para SQLite. As caches (config/route/view) correm em paralelo
# no programa "init" do Supervisor (não são críticas para o primeiro pedido).
echo "[entrypoint] A correr migrations..."
php /var/www/html/artisan migrate --force

# Fix ownership after migrate (root may have created WAL/journal files)
chown -R www-data:www-data /var/www/html/storage/database

# ── Arrancar Supervisor ──
if [ $# -gt 0 ]; then
    exec "$@"
else
    exec supervisord -c /etc/supervisor/supervisord.conf
fi

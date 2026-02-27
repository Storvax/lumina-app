#!/usr/bin/env sh

# ── Fast setup (apenas operações rápidas antes de arrancar o Supervisor) ──

# Garante que a pasta e o ficheiro da DB existem
mkdir -p /var/www/html/storage/database
if [ ! -f /var/www/html/storage/database/database.sqlite ]; then
    touch /var/www/html/storage/database/database.sqlite
fi

# Permissões apenas no volume mount (rápido — poucos ficheiros)
chown -R www-data:www-data /var/www/html/storage/database
chmod -R 775 /var/www/html/storage/database

# Garante que bootstrap/cache é writable pelo www-data (para config:cache)
chown -R www-data:www-data /var/www/html/bootstrap/cache

# ── Arrancar Supervisor IMEDIATAMENTE ──
# O Nginx começa a escutar na :8080 em ~1-2s.
# As tarefas pesadas (config:cache, migrate) correm em paralelo
# como o programa "init" do Supervisor (ver conf.d/init.conf).

if [ $# -gt 0 ]; then
    exec "$@"
else
    exec supervisord -c /etc/supervisor/supervisord.conf
fi

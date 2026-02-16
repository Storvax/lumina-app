#!/usr/bin/env sh

# Run user scripts, if they exist
for f in /var/www/html/.fly/scripts/*.sh; do
    # Bail out this loop if any script exits with non-zero status code
    bash "$f" -e
done

if [ $# -gt 0 ]; then
    # If we passed a command, run it as root
    # --- ADICIONA ISTO ANTES DO FINAL DO FICHEIRO ---

# 1. Garante que a pasta e o ficheiro da DB existem
mkdir -p /var/www/html/storage/database
if [ ! -f /var/www/html/storage/database/database.sqlite ]; then
    touch /var/www/html/storage/database/database.sqlite
fi

# 2. Garante permissões (crucial para SQLite)
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# 3. Corre as migrações na máquina certa
echo "🚀 A correr migrações..."
php artisan migrate --force

# --- FIM DO BLOCO ADICIONADO ---

# (A última linha do ficheiro original deve estar aqui, ex: exec "$@")
    exec "$@"
else
    exec supervisord -c /etc/supervisor/supervisord.conf
fi

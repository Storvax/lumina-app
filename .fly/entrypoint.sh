#!/usr/bin/env sh

# Run user scripts, if they exist
for f in /var/www/html/.fly/scripts/*.sh; do
    # Bail out this loop if any script exits with non-zero status code
    bash "$f" -e
done

# Garante que a pasta e o ficheiro da DB existem
mkdir -p /var/www/html/storage/database
if [ ! -f /var/www/html/storage/database/database.sqlite ]; then
    touch /var/www/html/storage/database/database.sqlite
fi

# Garante permissoes (crucial para SQLite)
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Corre as migracoes antes de iniciar qualquer processo
echo "A correr migracoes..."
php artisan migrate --force

if [ $# -gt 0 ]; then
    # If we passed a command, run it as root
    exec "$@"
else
    exec supervisord -c /etc/supervisor/supervisord.conf
fi

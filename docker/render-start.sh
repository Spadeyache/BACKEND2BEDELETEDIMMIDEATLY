#!/bin/sh
set -u

cd /var/www/html

echo "Starting Veara backend container"

if [ -f /var/secrets/veara/.env ]; then
  echo "Installing production .env from Secret Manager"
  cp /var/secrets/veara/.env /var/www/html/.env
  chown www-data:www-data /var/www/html/.env
  chmod 600 /var/www/html/.env
else
  echo "WARNING: /var/secrets/veara/.env is missing; Laravel will use baked/default environment only"
fi

if [ -n "${PORT:-}" ]; then
  echo "Configuring nginx to listen on PORT=${PORT}"
  sed -i "s/listen 8080;/listen ${PORT};/" /etc/nginx/http.d/default.conf
fi

echo "Clearing Laravel caches"
php artisan optimize:clear

echo "Running database migrations"
if ! php artisan migrate --force; then
  echo "WARNING: migrations failed; continuing so the web process can expose logs"
fi

echo "Syncing admin user from environment"
if ! php artisan veara:sync-admin-user; then
  echo "WARNING: admin user sync failed; check VEARA_ADMIN_* and DB env vars"
fi

echo "Starting supervisor"
exec /usr/bin/supervisord -c /etc/supervisord.conf

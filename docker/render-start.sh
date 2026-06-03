#!/bin/sh
set -u

cd /var/www/html

echo "Starting Veara backend container"

if [ -n "${PORT:-}" ]; then
  echo "Configuring nginx to listen on Render PORT=${PORT}"
  sed -i "s/listen 8080;/listen ${PORT};/" /etc/nginx/http.d/default.conf
fi

echo "Clearing Laravel caches"
php artisan optimize:clear

echo "Running database migrations"
if ! php artisan migrate --force; then
  echo "WARNING: migrations failed; continuing so the web process can expose logs"
fi

if [ -n "${VEARA_ADMIN_PASSWORD:-}" ]; then
  echo "Syncing admin user from environment"
  if ! php artisan veara:sync-admin-user; then
    echo "WARNING: admin user sync failed; check VEARA_ADMIN_* and DB env vars"
  fi
else
  echo "WARNING: VEARA_ADMIN_PASSWORD is not set; skipping admin user sync"
fi

echo "Starting supervisor"
exec /usr/bin/supervisord -c /etc/supervisord.conf

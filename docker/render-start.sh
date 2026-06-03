#!/bin/sh
set -eu

cd /var/www/html

if [ -n "${PORT:-}" ]; then
  sed -i "s/listen 8080;/listen ${PORT};/" /etc/nginx/http.d/default.conf
fi

php artisan optimize:clear
php artisan migrate --force
php artisan veara:sync-admin-user

exec /usr/bin/supervisord -c /etc/supervisord.conf

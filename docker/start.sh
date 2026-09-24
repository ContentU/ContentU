#!/bin/sh
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force || true
php artisan migrate --force
php artisan ped:seed-if-empty

exec frankenphp php-server --root public --listen ":${PORT:-8080}"

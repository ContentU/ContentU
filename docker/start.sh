#!/bin/sh
set -e

# Fail fast with a readable message instead of a PDO stack trace.
missing=""
for var in APP_KEY DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    eval "value=\${$var:-}"
    [ -z "$value" ] && missing="$missing $var"
done
if [ -n "$missing" ]; then
    echo "ERRORE: variabili d'ambiente mancanti:$missing" >&2
    echo "Impostale su Render in Environment -> Environment Variables." >&2
    exit 1
fi

if [ -n "${MYSQL_ATTR_SSL_CA:-}" ] && [ ! -r "$MYSQL_ATTR_SSL_CA" ]; then
    echo "ERRORE: certificato CA del database non trovato in $MYSQL_ATTR_SSL_CA" >&2
    echo "Caricalo su Render in Environment -> Secret Files con nome $(basename "$MYSQL_ATTR_SSL_CA")." >&2
    echo "File presenti in /etc/secrets:" >&2
    ls -la /etc/secrets >&2 2>/dev/null || echo "  (cartella assente: nessun Secret File caricato)" >&2
    exit 1
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force || true
php artisan migrate --force
php artisan ped:seed-if-empty

exec frankenphp php-server --root public --listen ":${PORT:-8080}"

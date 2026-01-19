#!/usr/bin/env sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

exec "$@"

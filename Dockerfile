# syntax=docker/dockerfile:1

# Production image for Render (or any Docker host).
# Stage 1 installs PHP + Node deps and builds the Vite assets (Wayfinder needs PHP
# during the build); stage 2 is the slim runtime served by FrankenPHP.

FROM dunglas/frankenphp:1-php8.4 AS base

RUN install-php-extensions pdo_mysql intl zip bcmath opcache pcntl

WORKDIR /app

FROM base AS build

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY package.json package-lock.json .npmrc ./
RUN npm ci

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && npm run build \
    && rm -rf node_modules

FROM base

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

COPY --from=build /app /app
COPY docker/php.ini $PHP_INI_DIR/conf.d/zz-app.ini

RUN chmod +x docker/start.sh \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD ["docker/start.sh"]

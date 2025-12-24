FROM php:8.3-cli-alpine

WORKDIR /app

RUN apk add --no-cache git unzip icu-dev libzip-dev oniguruma-dev postgresql-dev bash \
  && docker-php-ext-install intl zip pdo pdo_pgsql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN wget -qO- https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

CMD sh -c "composer install --no-interaction && symfony server:start --no-tls --allow-http --port=8000 --dir=/app"

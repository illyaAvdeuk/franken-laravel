# ./docker/Dockerfile
FROM dunglas/frankenphp:1-php8.4-alpine

ARG USER=appuser
ENV APP_HOME=/app

# system deps (build, composer) and PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    git curl unzip libzip-dev icu-dev oniguruma-dev zlib-dev autoconf build-base \
  && install-php-extensions pdo_pgsql pdo_mysql zip opcache intl redis \
  && apk del .build-deps


# install composer in image
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# create app user
RUN adduser -D -u 1000 ${USER}
WORKDIR /app
COPY --chown=${USER}:${USER} ./src/ /app


USER ${USER}

# composer install in image (optionally run in CI and use multi-stage)
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --optimize-autoloader; fi

# expose ports (FrankenPHP uses 80/443 by default)
EXPOSE 80 443

CMD ["frankenphp", "php-server", "--listen", "0.0.0.0:80", "--root", "public/"]

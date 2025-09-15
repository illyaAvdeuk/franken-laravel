# ./docker/Dockerfile
FROM dunglas/frankenphp:1-php8.4-alpine

ARG USER=appuser
ENV APP_HOME=/app
ENV HOME=/home/${USER}

# System deps (build, composer) and PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    git curl unzip libzip-dev icu-dev oniguruma-dev zlib-dev autoconf build-base \
  && install-php-extensions pdo_pgsql pdo_mysql zip opcache intl redis \
  && apk del .build-deps

# Install composer in image
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Create app user with home directory and create psysh config dir
RUN adduser -D -u 1000 -h /home/${USER} ${USER} \
    && mkdir -p /home/${USER}/.config/psysh \
    && chown -R ${USER}:${USER} /home/${USER}

WORKDIR /app

# Copy application code with proper permissions
COPY --chown=${USER}:${USER} ./src/ /app/

# Create directories and set permissions for FrankenPHP and PsySH
RUN mkdir -p /data/caddy/locks /tmp/psysh \
    && chown -R ${USER}:${USER} /data /tmp/psysh \
    && chmod -R 775 /data /tmp/psysh

# Ensure app storage/cache are owned by app user
RUN chown -R ${USER}:${USER} /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Create a minimal PsySH config file so artisan tinker (psysh) won't fail
RUN printf "<?php\n\nreturn [];\n" > /home/${USER}/.config/psysh/config.php \
    && chown ${USER}:${USER} /home/${USER}/.config/psysh/config.php \
    && chmod 644 /home/${USER}/.config/psysh/config.php

# Set environment variables for PsySH (optional but convenient)
ENV XDG_CONFIG_HOME=/home/${USER}/.config
ENV PSYSH_CONFIG=/home/${USER}/.config/psysh/config.php

USER ${USER}

# Composer install in image (runs as app user)
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --optimize-autoloader; fi

# Expose ports (FrankenPHP uses 80/443 by default)
EXPOSE 80 443

CMD ["frankenphp", "php-server", "--listen", "0.0.0.0:80", "--root", "public/"]

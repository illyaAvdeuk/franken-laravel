# Franken-Laravel — Local Development with FrankenPHP + Laravel

## Overview

This repository runs a Laravel application on FrankenPHP (via `dunglas/frankenphp`) with PostgreSQL, Redis and RabbitMQ in Docker. The app is exposed on host port **8100**. `.env` is kept in the repository root and mounted into the container so Laravel sees it as `/app/.env`.

## Requirements

* Docker (Engine) and Docker Compose (use `docker compose` v2 if available)
* Linux host (tested on Linux Mint)
* VSCode (optional) or any editor

## Important paths

* Application code: `./src` (mounted into container as `/app`)
* Project root `.env` (must be present) → mounted into container as `/app/.env`
* `docker/Dockerfile` builds the `app` image using `dunglas/frankenphp`

## Ports

* Application: `http://localhost:8100` → mapped `8100:80`
* Postgres (host access): `5467:5432` (container still listens on `5432` internally)
* RabbitMQ management UI: `http://localhost:15672` (default user/password from `.env`)
* Xdebug (dev): `9003`

## `.env` rules (how to configure)

Place your `.env` in the project root (not in `src`). Required minimum settings (example):

```env
APP_NAME=FrankenLaravel
APP_ENV=local
APP_KEY=            # leave empty, will generate below
APP_DEBUG=true
APP_URL=http://localhost:8100

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432        # IMPORTANT: internal container port must be 5432
EXTERNAL_DB_PORT=   # external container db port
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest

# Xdebug dev toggle
XDEBUG_MODE=off
XDEBUG_CLIENT_HOST=host.docker.internal
XDEBUG_CLIENT_PORT=9003
```

**Notes**

* For host access to Postgres (e.g., from VSCode DB tools) use the host port you mapped in `docker-compose.yml` (e.g. `5467`). Inside Docker network use `DB_PORT=5432`.
* Docker Compose uses the `.env` in the project root for interpolation. Keep it in the project root.

## Quick start (commands)

Run from project root:

```bash
# stop / clean previous state
docker compose down --volumes --remove-orphans

# build the app image
docker compose build --no-cache app

# start services (detached)
docker compose up -d
```

If you mounted `src` over `/app`, install PHP dependencies (if not already present in `src/vendor`):

```bash
docker compose exec app composer install
```

Generate application key:

```bash
docker compose exec app php artisan key:generate
```

Ensure Postgres is accepting connections (optional check):

```bash
docker compose exec db pg_isready -U ${DB_USERNAME:-laravel} -d ${DB_DATABASE:-laravel}
```

Run migrations:

```bash
docker compose exec app php artisan migrate --force
```

Run seeders (optional):

```bash
docker compose exec app php artisan db:seed --force
```

Start queue worker (if you need it interactively):

```bash
docker compose exec worker php /app/artisan queue:work --sleep=3 --tries=3
```

View logs:

```bash
docker compose logs -f app
docker compose logs -f db
```

Stop everything:

```bash
docker compose down
```

Rebuild and restart (clean):

```bash
docker compose down --volumes --remove-orphans
docker compose build --no-cache app
docker compose up -d
```

## Access DB from host (VSCode)

If `docker-compose.yml` maps Postgres as `5467:5432`, configure your DB client in VSCode with:

* Host: `localhost`
* Port: `5467`
* Database: `laravel`
* User: `laravel`
* Password: `secret`

Alternatively, access Postgres from within container:

```bash
docker compose exec db psql -U laravel -d laravel
```

## Xdebug (enable in dev)

To enable Xdebug set `XDEBUG_MODE=develop,debug` in project root `.env` and restart the app container:

```bash
# update .env
docker compose restart app
```

Configure your IDE to listen on port `9003`. Use `XDEBUG_CLIENT_HOST` value to point to your host (on Linux use host gateway or `host.docker.internal` if available).

## Benchmarks

Suggested tools (host machine): `wrk`, `hey`, or `fortio`.

Example:

```bash
# from host
wrk -t4 -c50 -d30s http://localhost:8100/
```

Record p50/p95/p99 latencies, RPS and docker stats (`docker stats laravel_app`).

## Common issues & troubleshooting

* **App container exits with `no such file or directory /app/public`**
  Ensure `docker-compose.yml` mounts `./src:/app` (not `./:/app`). Verify `src/public/index.php` exists.

* **`DB connection refused`**
  Ensure `DB_PORT` in `.env` is `5432` (internal) while `docker-compose.yml` maps host port (e.g. `5467:5432`). Restart services after fixing `.env`.

* **Variables not interpolated in compose**
  Docker Compose interpolates variables from the `.env` in the compose file directory. Keep `.env` in project root.

* **Vendor directory missing after mount**
  If `vendor/` is present in the image but missing after bind-mount, run `composer install` inside the `app` container (mount hides image filesystem).

## Useful commands summary

```bash
docker compose build --no-cache app
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec worker php /app/artisan queue:work --sleep=3 --tries=3
docker compose logs -f app
docker compose down --volumes --remove-orphans

### nice table wiew
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}"
docker network inspect franken-laravel_default

docker compose -f dev-compose.yml down && docker compose -f dev-compose.yml up -d
docker compose -f dev-compose.yml down
```

## Useful commands for coding inside app container

```bash
# PSR-12
./vendor/bin/pint # Start formatting all code
./vendor/bin/pint --test # check if code formatted, but do not change it yet
./vendor/bin/pint --dirty # Format only changed files (git dirty)
./vendor/bin/pint  --test  --preset psr12 # Using a specific preset (eg pure PSR-12)

# Tests
./artisan test
# or
./vendor/bin/pest --colors
```

## Useful commands for understanding load
```bash
### HTTP-benchmark
wrk -t2 -c10 -d5s http://localhost:8100

### alternative to wrk on Go
hey -n 1000 -c 20 http://localhost:8100

### pgbench — PostgreSQL load
PGPASSWORD=secret pgbench -h localhost -p 5467 -U laravel -i laravel
PGPASSWORD=secret pgbench -h localhost -p 5467 -U laravel -c 10 -j 2 -T 30 laravel


### Postgres logs report example
docker logs pg_main &> ~/franken-laravel/pg_container.log
pgbadger ~/franken-laravel/pg_container.log -o ~/franken-laravel/pg_report.html
# open report in browser
xdg-open ~/franken-laravel/pg_report.html
```
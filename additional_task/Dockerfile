FROM php:8.4-fpm-bullseye

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor build-essential autoconf pkg-config \
    libicu-dev libxml2-dev zlib1g-dev libzip-dev libsqlite3-dev zip unzip \
  && docker-php-ext-configure intl --with-icu-dir=/usr \
  && docker-php-ext-install -j$(nproc) pdo pdo_sqlite sqlite3 intl \
  && apt-get purge -y --auto-remove build-essential autoconf pkg-config \
  && rm -rf /var/lib/apt/lists/*

COPY additional_task/docker/prod/nginx.conf /etc/nginx/sites-available/default
COPY additional_task/docker/prod/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY additional_task/docker/prod/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY additional_task/app /app
WORKDIR /app
RUN mkdir -p /app/data && chmod 777 /app/data

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]

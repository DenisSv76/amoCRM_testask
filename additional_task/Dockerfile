# Dockerfile только для локальной разработки (docker-compose не использует этот файл)
# Для деплоя на Render используется Dockerfile.render в корне репозитория

FROM php:8.4-fpm-bullseye

RUN apt-get update && apt-get install -y \
    nginx supervisor libsqlite3-dev libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_sqlite sqlite3 intl \
    && rm -rf /var/lib/apt/lists/*

COPY docker/prod/nginx.conf /etc/nginx/sites-available/default
COPY docker/prod/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/prod/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY app /app
WORKDIR /app
RUN mkdir -p /app/data && chmod 777 /app/data

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]

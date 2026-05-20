FROM php:8.4-fpm-bullseye

RUN apt-get update && apt-get install -y \
    nginx supervisor libsqlite3-dev libzip-dev zip unzip \
    && docker-php-ext-install pdo \
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

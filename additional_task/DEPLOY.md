# Деплой на Render.com

## Бесплатный хостинг (1 контейнер, Docker)

### Если этот проект — корень репозитория

#### Способ 1: Blueprint (автоматически, через `render.yaml`)

1. Залей проект на GitHub/GitLab
2. Зайди на [dashboard.render.com](https://dashboard.render.com) → зарегистрируйся (можно через GitHub)
3. Нажми **New** → **Blueprint**
4. Подключи репозиторий → Render сам найдёт `render.yaml` и создаст сервис

### Если этот проект — поддиректория в общем репозитории (моноРепа)

#### Способ 2: Web Service с указанием Root Directory

1. Зайди на [dashboard.render.com](https://dashboard.render.com)
2. Нажми **New** → **Web Service**
3. Подключи репозиторий
4. Заполни поля:
   - **Name**: `visits-dashboard`
   - **Root Directory**: `additional_task` *(путь к этой поддиректории в репозитории)*
   - **Environment**: `Docker`
   - **Plan**: `Free`
5. Нажми **Create Web Service**

> **Важно**: Blueprint (`render.yaml`) работает только из корня репозитория. Если проект в поддиректории — только ручной Web Service с указанием **Root Directory**.

### Что внутри Dockerfile

Один контейнер объединяет:
- **nginx** — веб-сервер (слушает порт `$PORT` от Render, по умолчанию 10000)
- **php-fpm** — обработка PHP
- **SQLite3** — база данных (хранится в `/app/data/data.sqlite`)

Supervisord управляет обоими процессами (nginx + php-fpm).

### Важно (Free Tier)

- **Cold start**: после 15 минут бездействия контейнер засыпает. При следующем запросе ~30-60 секунд на запуск.
- **Диск**: эфемерный — БД очищается при каждом деплое/рестарте. Seed-пользователь `admin/admin` создаётся автоматически.
- **Лимит**: 750 часов в месяц (хватает на 1 контейнер 24/7)

### Учётные данные по умолчанию

```
Логин:  admin
Пароль: admin
```

### Локальная разработка (без изменений!)

`docker-compose.yml` остаётся для локальной разработки:

```bash
docker compose up -d
# → http://localhost:8086
```

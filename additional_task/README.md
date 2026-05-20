# Счётчик посещений страницы (Visits Dashboard)

Решение состоит из двух компонентов:

- **JS-трекер** — подключается к любому сайту, собирает данные (IP, город, устройство) и отправляет на сервер
- **Бэкенд** — хранит данные в SQLite, показывает график посещений по часам и круговую диаграмму по городам

Доступ к статистике — по авторизации.

## Демо

Развёрнуто на Render: **[https://visits-dashboard.onrender.com](https://visits-dashboard.onrender.com)**

| Страница | Описание |
|----------|----------|
| `/login` | Вход (`admin` / `admin`) |
| `/register` | Регистрация нового пользователя |
| `/dashboard` | Графики: уникальные посещения по часам + распределение по городам |
| `/test_page` | Тестовая страница с подключённым трекером |

## Как работает трекинг

1. На страницу добавляется `<script src="/track.js" async></script>`
2. `track.js` определяет устройство (desktop/mobile/tablet), запрашивает геолокацию по IP через [ipapi.co](https://ipapi.co)
3. Отправляет POST на `/api/track` с данными: IP, город, страна, устройство, User-Agent, URL, referrer, разрешение экрана

## API

| Метод | Путь | Авторизация | Описание |
|-------|------|-------------|----------|
| POST | `/track` | Нет | Приём трека |
| POST | `/login` | Нет | Вход |
| POST | `/register` | Нет | Регистрация |
| POST | `/logout` | Да | Выход |
| GET | `/stats/hourly?hours=24` | Да | Посещения по часам |
| GET | `/stats/cities` | Да | Посещения по городам |

## Стек

- **PHP 8.4** (FPM) + **nginx**
- **SQLite3** (база данных)
- **Chart.js** (графики)
- **Docker** / **docker-compose** (локальная разработка)
- **Render.com** (продакшен-хостинг, бесплатный тир)

## Локальный запуск

```bash
docker compose up -d
# → http://localhost:8086
```

Seed-пользователь: `admin` / `admin` (создаётся автоматически).

## Деплой

См. [`DEPLOY.md`](DEPLOY.md).

## Структура проекта

```
├── app/
│   ├── public/          # Точка входа, HTML, track.js
│   │   ├── index.php    # Роутер и API
│   │   ├── login.html
│   │   ├── register.html
│   │   ├── dashboard.html
│   │   ├── test_page.html
│   │   └── track.js     # JS-трекер
│   ├── src/
│   │   └── db.php       # SQLite-подключение и миграции
│   └── data/
│       └── data.sqlite  # База (создаётся автоматически)
├── docker/
│   ├── nginx/           # nginx для локальной разработки
│   ├── php-fpm/         # php-fpm для локальной разработки
│   ├── php-cli/         # php-cli для локальной разработки
│   └── prod/            # nginx + supervisor для Render
├── docker-compose.yml   # Локальная разработка (3 сервиса)
├── Dockerfile           # Одно-контейнерная сборка для Render
├── render.yaml          # Render Blueprint
└── DEPLOY.md            # Инструкция по деплою
```

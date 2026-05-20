# Laravel Joke Fetcher

Laravel-приложение, которое периодически получает случайные шутки из [Chuck Norris API](https://api.chucknorris.io) и сохраняет их в базу данных, с JSON-эндпоинтом для их выдачи.

## Постановка задачи

Техническое задание из двух частей:

1. **Консольная команда** — каждые 5 минут получает данные от внешнего API и сохраняет их в таблицу БД.
2. **JSON API route** — отдаёт массив всех сохранённых записей в формате JSON.

## Реализация

### 1. Консольная команда: `fetch:joke`

**Файл:** [`app/Console/Commands/FetchJokeCommand.php`](app/Console/Commands/FetchJokeCommand.php)

```
php artisan fetch:joke
```

- Отправляет `GET`-запрос к `https://api.chucknorris.io/jokes/random` каждые 5 минут (300 секунд).
- Сохраняет каждую шутку в таблицу `jokes`, используя `external_id` (ID из API) как уникальный ключ — дубликаты исключаются через `firstOrCreate`.
- Поддерживает флаг `--once` для однократного выполнения (удобно при интеграции с cron/планировщиком).
- Интерактивный режим: введите `stop`, `exit` или `q` в терминале, чтобы корректно остановить цикл.
- Сетевые ошибки, ошибки API и ошибки записи в БД обрабатываются с выводом информативных сообщений в лог.

#### Использование

```bash
# Запуск в непрерывном режиме (запрос каждые 5 минут)
php artisan fetch:joke

# Однократный запуск
php artisan fetch:joke --once
```

### 2. API-маршрут: `GET /api/random_joke`

**Файл:** [`routes/api.php`](routes/api.php)

Возвращает все сохранённые шутки в виде JSON-массива:

```
GET /api/random_joke
```

**Пример ответа:**

```json
[
  {
    "id": 1,
    "external_id": "abc123",
    "joke_text": "Chuck Norris can divide by zero.",
    "created_at": "2026-05-19T12:00:00.000000Z",
    "updated_at": "2026-05-19T12:00:00.000000Z"
  }
]
```

## База данных

**Миграция:** [`database/migrations/2026_05_19_042817_create_jokes_table.php`](database/migrations/2026_05_19_042817_create_jokes_table.php)

**Модель:** [`app/Models/Joke.php`](app/Models/Joke.php)

| Колонка       | Тип      | Описание                                |
|-------------- |----------|-----------------------------------------|
| `id`          | bigint   | Первичный ключ (автоинкремент)           |
| `external_id` | string   | Уникальный ID из ответа API              |
| `joke_text`   | text     | Текст шутки                              |
| `created_at`  | timestamp| Дата и время создания записи             |
| `updated_at`  | timestamp| Дата и время последнего обновления записи |

## Конфигурация

Базовый URL API задаётся в [`config/services.php`](config/services.php):

```php
'jokes' => [
    'url' => env('API_JOKES_URL', 'https://api.chucknorris.io'),
],
```

Переопределяется через `.env`:

```
API_JOKES_URL=https://api.chucknorris.io
```

## Требования

- PHP 8.2+
- Composer
- MySQL / PostgreSQL / SQLite

## Установка и запуск

```bash
cd app
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Запуск

```bash
# Запустить сборщик шуток
php artisan fetch:joke

# В другом терминале — запросить накопленные шутки
curl http://localhost:8000/api/random_joke
```

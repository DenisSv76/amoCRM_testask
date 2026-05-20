#!/bin/bash
set -e

# Render передаёт PORT (по умолчанию 10000)
LISTEN_PORT="${PORT:-80}"
sed -i "s/PORT_PLACEHOLDER/${LISTEN_PORT}/" /etc/nginx/sites-available/default

# --- Гарантируем наличие папки данных и корректные права ---
mkdir -p /app/data

# если в проекте другой путь к базе, поправь /app/data/database.sqlite на реальный путь
touch /app/data/database.sqlite

# Сделать владельцем www-data и дать права на запись
chown -R www-data:www-data /app
chmod -R 755 /app
chmod -R 775 /app/data
chmod 664 /app/data/database.sqlite || true
# ---------------------------------------------------------

# Запустить supervisord (как было)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

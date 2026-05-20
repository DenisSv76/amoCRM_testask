#!/bin/bash
# Render передаёт PORT (по умолчанию 10000)
LISTEN_PORT="${PORT:-80}"
sed -i "s/PORT_PLACEHOLDER/${LISTEN_PORT}/" /etc/nginx/sites-available/default
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

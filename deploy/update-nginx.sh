#!/usr/bin/env bash
# Добавя кеширането на статичните файлове към вече работещата Nginx конфигурация
# (след certbot). Безопасно е да се пусне повече от веднъж.
# Пуска се като root:  sudo bash deploy/update-nginx.sh
set -euo pipefail

CONF="/etc/nginx/conf.d/creatiumlab.conf"
SNIPPET="/etc/nginx/creatium-static.conf"
SRC="$(dirname "$0")/nginx-static.conf"

[ "$(id -u)" -eq 0 ] || { echo "Пуснете като root (с sudo)."; exit 1; }
[ -f "$CONF" ] || { echo "Липсва $CONF"; exit 1; }

install -m 644 "$SRC" "$SNIPPET"
command -v restorecon >/dev/null && restorecon "$SNIPPET" || true

if ! grep -q "include $SNIPPET;" "$CONF"; then
    cp "$CONF" "$CONF.bak.$(date +%s)"
    # Добавя include преди "location /build/" във всеки сървърен блок на сайта (HTTP и HTTPS).
    sed -i -E "s#^([[:space:]]*)location (\^~ )?/build/ \{#\1include $SNIPPET;\n\n\1location ^~ /build/ {#" "$CONF"
fi

nginx -t
systemctl reload nginx
echo "Готово: статичните файлове се кешират 30 дни."

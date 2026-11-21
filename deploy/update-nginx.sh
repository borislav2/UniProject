#!/usr/bin/env bash
# Обновява вече работещата Nginx конфигурация (след certbot):
#  - кеширане на статичните файлове (creatium-static.conf);
#  - заглавки за сигурност (HSTS, CSP, X-Frame-Options, ...) за страниците и за статичните файлове
#    (creatium-security.conf);
#  - HTTP/2 за HTTPS (certbot не го включва) и по-силна gzip компресия (creatium-gzip.conf), за по-бърз сайт на телефон.
# Безопасно е да се пусне повече от веднъж. Пуска се като root:  sudo bash deploy/update-nginx.sh
set -euo pipefail

CONF="/etc/nginx/conf.d/creatiumlab.conf"
STATIC="/etc/nginx/creatium-static.conf"
SECURITY="/etc/nginx/creatium-security.conf"
GZIP="/etc/nginx/conf.d/00-creatium-gzip.conf"
DIR="$(dirname "$0")"

[ "$(id -u)" -eq 0 ] || { echo "Пуснете като root (с sudo)."; exit 1; }
[ -f "$CONF" ] || { echo "Липсва $CONF"; exit 1; }

install -m 644 "$DIR/nginx-static.conf" "$STATIC"
install -m 644 "$DIR/nginx-security.conf" "$SECURITY"
command -v restorecon >/dev/null && restorecon "$STATIC" "$SECURITY" || true

BACKUP="$CONF.bak.$(date +%s)"
cp "$CONF" "$BACKUP"

# 1. Кеширане: include преди "location /build/" във всеки сървърен блок на сайта (HTTP и HTTPS).
if ! grep -q "include $STATIC;" "$CONF"; then
    sed -i -E "s#^([[:space:]]*)location (\^~ )?/build/ \{#\1include $STATIC;\n\n\1location ^~ /build/ {#" "$CONF"
fi

# 2. Заглавки за сигурност на ниво сървър: старите три add_header се заменят с include на файла.
if ! grep -q "include $SECURITY;" "$CONF"; then
    sed -i -E "s#^([[:space:]]*)add_header X-Frame-Options .*#\1include $SECURITY;#" "$CONF"
    sed -i -E "/^[[:space:]]*add_header (X-Content-Type-Options|Referrer-Policy) /d" "$CONF"
    # Ако старите редове ги е нямало: include след "charset utf-8;" във всеки сървърен блок на сайта.
    grep -q "include $SECURITY;" "$CONF" \
        || sed -i -E "s#^([[:space:]]*)charset utf-8;#&\n\n\1include $SECURITY;#" "$CONF"
fi

# 3. Същите заглавки и в /build/ (там add_header Cache-Control спира наследяването от сървъра):
#    include на следващия ред след Cache-Control, ако още го няма.
awk -v inc="include $SECURITY;" '
    { print }
    /add_header Cache-Control "public, immutable";/ {
        if ((getline nxt) > 0) {
            if (index(nxt, inc) == 0) { match($0, /^[ \t]*/); print substr($0, 1, RLENGTH) inc }
            print nxt
        }
    }' "$CONF" > "$CONF.tmp"
cat "$CONF.tmp" > "$CONF" && rm -f "$CONF.tmp"

# 4. HTTP/2 на HTTPS реда (nginx 1.20: "listen 443 ssl http2;"). Много файлове наведнъж по една връзка, важно на бавен телефон.
sed -i -E '/http2/! s#^([[:space:]]*listen[[:space:]]+(\[::\]:)?443[[:space:]]+ssl)#\1 http2#' "$CONF"

# 5. Компресия на ниво http: по-силна (5 вместо 1), Vary за кешовете, и за JavaScript с type text/javascript.
#    gzip_types в сървърния блок остава, тук се добавят само нивото, Vary и минималният размер.
cat > "$GZIP" <<'GZ'
gzip_comp_level 5;
gzip_vary on;
gzip_min_length 256;
gzip_proxied any;
GZ
command -v restorecon >/dev/null && restorecon "$GZIP" || true
sed -i -E 's#^([[:space:]]*gzip_types )text/css application/javascript application/json image/svg\+xml;#\1text/css text/javascript application/javascript application/json image/svg+xml text/plain application/xml;#' "$CONF"

if ! nginx -t; then
    cp "$BACKUP" "$CONF"
    rm -f "$GZIP"
    echo "Грешка в конфигурацията: върнах старата ($BACKUP). Nginx не е презареден."
    exit 1
fi
systemctl reload nginx
echo "Готово: статичните файлове се кешират 30 дни, заглавките за сигурност са включени навсякъде, HTTP/2 и gzip ниво 5 са включени."
# PHP: без OPcache всяка страница се компилира наново и сървърът отговаря бавно.
if php -r 'exit((int) !(function_exists("opcache_get_status") && ini_get("opcache.enable")));' 2>/dev/null; then
    echo "OPcache: включен."
else
    echo "ВНИМАНИЕ: OPcache изглежда изключен за PHP-FPM. Инсталирайте php-opcache (dnf install php-opcache) и рестартирайте php-fpm."
fi

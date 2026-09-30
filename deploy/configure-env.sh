#!/usr/bin/env bash
# Създава .env за production от .env.example и данните за базата, записани от almalinux-setup.sh.
# Пуска се като root:  sudo bash deploy/configure-env.sh creatiumlab.com
set -euo pipefail

DOMAIN="${1:?Използване: configure-env.sh <домейн>, напр. creatiumlab.com}"
APP_DIR="${APP_DIR:-/var/www/creatiumlab}"
APP_USER="${APP_USER:-deploy}"
CREDS_FILE="${CREDS_FILE:-/root/creatium-db.txt}"

[ "$(id -u)" -eq 0 ] || { echo "Пуснете като root (с sudo)."; exit 1; }
[ -f "$CREDS_FILE" ] || { echo "Липсва $CREDS_FILE. Пуснете първо almalinux-setup.sh."; exit 1; }
cd "$APP_DIR"
[ -f .env ] && { echo ".env вече съществува. Преименувайте или изтрийте го, ако искате да започнете отначало."; exit 1; }

cp .env.example .env

set_env() {
    local key="$1" value="$2"
    if grep -qE "^#?[[:space:]]*${key}=" .env; then
        sed -i -E "s|^#?[[:space:]]*${key}=.*|${key}=${value}|" .env
    else
        printf '%s=%s\n' "$key" "$value" >> .env
    fi
}

# DB_DATABASE, DB_USERNAME, DB_PASSWORD
while IFS='=' read -r k v; do
    [ -n "$k" ] && set_env "$k" "$v"
done < "$CREDS_FILE"

set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "https://${DOMAIN}"
set_env LOG_LEVEL warning
set_env DB_CONNECTION mysql
set_env DB_HOST 127.0.0.1
set_env DB_PORT 3306
set_env SESSION_DRIVER database
set_env CACHE_STORE database
set_env QUEUE_CONNECTION sync
set_env MAIL_MAILER log

chown "$APP_USER":nginx .env
chmod 640 .env
runuser -u "$APP_USER" -- php artisan key:generate --force

cat <<MSG

.env е готов (имейлите засега се записват в лога: MAIL_MAILER=log).
Следваща стъпка, като потребител '$APP_USER':
    sudo -iu $APP_USER
    cd $APP_DIR && bash deploy/deploy.sh
MSG

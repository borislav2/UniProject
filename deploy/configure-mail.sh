#!/usr/bin/env bash
# Настройва изпращането на имейли (SMTP) в съществуващия .env и праща тестово писмо.
# Пуска се като потребител 'deploy' в папката на приложението:
#   bash deploy/configure-mail.sh [smtp-хост] [акаунт за вход] [адрес на подателя]
# По подразбиране: smtp.gmail.com, вход и подател hello@creatiumlab.com (Google Workspace).
# Ако hello@ е само псевдоним (alias) и няма собствен вход, влезте с истински акаунт:
#   bash deploy/configure-mail.sh smtp.gmail.com vladi@creatiumlab.com hello@creatiumlab.com
# (в Gmail на този акаунт hello@ трябва да е добавен в „Изпращане като“).
# Паролата се пита скрито и се записва само в .env. Не я пращайте в чат или имейл.
# Порт 587 е по подразбиране; за 465: MAIL_PORT_OVERRIDE=465 bash deploy/configure-mail.sh
set -euo pipefail

cd "$(dirname "$0")/.."
[ -f .env ] || { echo "Липсва .env в $(pwd)."; exit 1; }

HOST="${1:-smtp.gmail.com}"
ACCOUNT="${2:-hello@creatiumlab.com}"
FROM_ADDRESS="${3:-$ACCOUNT}"
PORT="${MAIL_PORT_OVERRIDE:-587}"
NOTIFY="${CREATIUM_NOTIFY_OVERRIDE:-hello@creatiumlab.com}"

read -r -s -p "Парола на приложение за ${ACCOUNT} (не се показва): " PASS
echo
PASS="${PASS// /}"   # Google я показва на групи с интервали
[ -n "$PASS" ] || { echo "Празна парола."; exit 1; }

set_env() {
    local key="$1" value="$2"
    value="${value//\\/\\\\}"
    value="${value//\"/\\\"}"
    grep -vE "^#?[[:space:]]*${key}=" .env > .env.tmp || true
    printf '%s="%s"\n' "$key" "$value" >> .env.tmp
    cat .env.tmp > .env      # запазва собственика и правата на .env
    rm -f .env.tmp
}

set_env MAIL_MAILER smtp
set_env MAIL_HOST "$HOST"
set_env MAIL_PORT "$PORT"
set_env MAIL_SCHEME "$([ "$PORT" = "465" ] && echo smtps || echo smtp)"
set_env MAIL_USERNAME "$ACCOUNT"
set_env MAIL_PASSWORD "$PASS"
set_env MAIL_FROM_ADDRESS "$FROM_ADDRESS"
set_env MAIL_FROM_NAME "Creatium Lab"
set_env CREATIUM_NOTIFY_EMAIL "$NOTIFY"

php artisan config:clear >/dev/null
php artisan config:cache >/dev/null
echo "Настройките са записани. Изпращам тестово писмо до ${NOTIFY} ..."
php artisan creatium:test-mail

#!/usr/bin/env bash
# Настройва изпращането на имейли (SMTP) в съществуващия .env и праща тестово писмо.
# Пуска се като потребител 'deploy' в папката на приложението:
#   bash deploy/configure-mail.sh [smtp-хост] [акаунт за вход] [адрес на подателя]
# По подразбиране: smtp.gmail.com, вход и подател team@creatiumlab.com (Google Workspace;
# от октомври 2026 team@ е основният акаунт, а info@ е негов псевдоним и писмата до него идват в team@).
# Друг подател от същия акаунт (трябва да е добавен в Gmail → „Изпращане като“):
#   bash deploy/configure-mail.sh smtp.gmail.com team@creatiumlab.com info@creatiumlab.com
# Паролата се пита скрито и се записва само в .env. Не я пращайте в чат или имейл.
# Порт 587 е по подразбиране; за 465: MAIL_PORT_OVERRIDE=465 bash deploy/configure-mail.sh
set -euo pipefail

cd "$(dirname "$0")/.."
[ -f .env ] || { echo "Липсва .env в $(pwd)."; exit 1; }

HOST="${1:-smtp.gmail.com}"
ACCOUNT="${2:-team@creatiumlab.com}"
FROM_ADDRESS="${3:-$ACCOUNT}"
PORT="${MAIL_PORT_OVERRIDE:-587}"
NOTIFY="${CREATIUM_NOTIFY_OVERRIDE:-info@creatiumlab.com}"

# Проверка на аргументите: грешно поставен ред (напр. пътят на папката като трети аргумент)
# иначе записва невалиден подател в .env и всяко писмо пада с „does not comply with RFC 2822“.
is_email() { [[ "$1" =~ ^[^@[:space:]/]+@[^@[:space:]/]+\.[^@[:space:]/]+$ ]]; }
for pair in "акаунт за вход:$ACCOUNT" "подател:$FROM_ADDRESS" "получател на известията:$NOTIFY"; do
    label="${pair%%:*}" value="${pair#*:}"
    is_email "$value" || { echo "Невалиден имейл за ${label}: \"${value}\". Пример: bash deploy/configure-mail.sh smtp.gmail.com team@creatiumlab.com"; exit 1; }
done
[[ "$HOST" == *.* && "$HOST" != */* ]] || { echo "Невалиден SMTP хост: \"${HOST}\". Пример: smtp.gmail.com"; exit 1; }

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

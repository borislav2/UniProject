#!/usr/bin/env bash
# Настройва отделната поща за първи писма до нови бизнеси (админ: „Потенциални клиенти“).
# Пуска се като потребител 'deploy' в папката на приложението:
#   bash deploy/configure-outreach-mail.sh <smtp-хост> <акаунт за вход> [адрес на подателя] [дневен лимит]
# Пример: bash deploy/configure-outreach-mail.sh smtp.gmail.com info@creativemoa.site
# Отделен домейн е по-безопасен; адрес от creatiumlab.com се приема само след потвърждение.
# Паролата се пита скрито и се записва само в .env. Не я пращайте в чат или имейл.
# Порт 587 е по подразбиране; за 465: OUTREACH_PORT_OVERRIDE=465 bash deploy/configure-outreach-mail.sh ...
set -euo pipefail

cd "$(dirname "$0")/.."
[ -f .env ] || { echo "Липсва .env в $(pwd)."; exit 1; }
[ $# -ge 2 ] || { echo "Употреба: bash deploy/configure-outreach-mail.sh <smtp-хост> <акаунт> [подател] [дневен лимит]"; exit 1; }

HOST="$1"
ACCOUNT="$2"
FROM_ADDRESS="${3:-$ACCOUNT}"
LIMIT="${4:-20}"
PORT="${OUTREACH_PORT_OVERRIDE:-587}"

# По решение на собствениците (23.10.2026) е разрешен и основният домейн, след изрично потвърждение:
# оплаквания за спам от студените писма могат да навредят и на нормалната поща от creatiumlab.com.
case "$FROM_ADDRESS" in
    *@creatiumlab.com)
        echo "Внимание: първите писма ще тръгват от основния домейн creatiumlab.com."
        echo "Ако получатели ги отбележат като спам, може да пострада и нормалната ви поща. Пращайте малко и само проверени писма."
        read -r -p "Продължавате ли? (да/не): " ANSWER
        [ "$ANSWER" = "да" ] || { echo "Отказано. Нищо не е променено."; exit 1; } ;;
esac

read -r -s -p "Парола на приложение за ${ACCOUNT} (не се показва): " PASS
echo
PASS="${PASS// /}"
[ -n "$PASS" ] || { echo "Празна парола."; exit 1; }

set_env() {
    local key="$1" value="$2"
    value="${value//\\/\\\\}"
    value="${value//\"/\\\"}"
    grep -vE "^#?[[:space:]]*${key}=" .env > .env.tmp || true
    printf '%s="%s"\n' "$key" "$value" >> .env.tmp
    cat .env.tmp > .env
    rm -f .env.tmp
}

set_env OUTREACH_MAIL_HOST "$HOST"
set_env OUTREACH_MAIL_PORT "$PORT"
set_env OUTREACH_MAIL_SCHEME "$([ "$PORT" = "465" ] && echo smtps || echo smtp)"
set_env OUTREACH_MAIL_USERNAME "$ACCOUNT"
set_env OUTREACH_MAIL_PASSWORD "$PASS"
set_env OUTREACH_MAIL_FROM "$FROM_ADDRESS"
set_env OUTREACH_MAIL_FROM_NAME "Creatium Lab"
set_env OUTREACH_DAILY_LIMIT "$LIMIT"

php artisan config:clear >/dev/null
php artisan config:cache >/dev/null
echo "Готово. Първите писма ще се пращат от ${FROM_ADDRESS}, до ${LIMIT} на ден. Нищо не е изпратено."

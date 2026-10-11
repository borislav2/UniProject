#!/usr/bin/env bash
# Еднократна настройка на автоматичния деплой от GitHub Actions (.github/workflows/deploy.yml).
# Пуска се на сървъра като потребител 'deploy':
#   bash /var/www/creatiumlab/deploy/setup-auto-deploy.sh
#
# Създава отделен SSH ключ само за GitHub и го разрешава в ~/.ssh/authorized_keys с ограничение:
# с него може да се пусне единствено deploy/deploy.sh (без shell, без пренасочване на портове).
# Накрая показва стойностите, които се поставят в GitHub -> Settings -> Secrets and variables -> Actions.
# Безопасно е да се пусне повече от веднъж (ключът се пази; редът в authorized_keys се подменя).
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
KEY="$HOME/.ssh/github-actions-deploy"
AUTH="$HOME/.ssh/authorized_keys"
TAG="github-actions-deploy"

[ "$(id -un)" = "deploy" ] || { echo "Пуснете като потребител 'deploy' (sudo -iu deploy)."; exit 1; }
[ -f "$APP_DIR/deploy/deploy.sh" ] || { echo "Не намирам $APP_DIR/deploy/deploy.sh"; exit 1; }

# deploy.sh прави git pull; без интерактивна парола трябва да минава и без човек пред терминала.
if ! GIT_TERMINAL_PROMPT=0 git -C "$APP_DIR" fetch --quiet --dry-run origin 2>/dev/null; then
    echo "ВНИМАНИЕ: 'git fetch' в $APP_DIR иска парола или не успява."
    echo "Автоматичният деплой ще се провали, докато git pull не работи без въпроси (вижте DEPLOY-WEBDOCK.md, deploy key)."
fi

mkdir -p "$HOME/.ssh" && chmod 700 "$HOME/.ssh"
[ -f "$KEY" ] || ssh-keygen -q -t ed25519 -N "" -C "$TAG" -f "$KEY"

touch "$AUTH" && chmod 600 "$AUTH"
grep -v " $TAG\$" "$AUTH" > "$AUTH.tmp" || true
printf 'restrict,command="cd %s && bash deploy/deploy.sh 2>&1" %s\n' "$APP_DIR" "$(cut -d' ' -f1,2 "$KEY.pub") $TAG" >> "$AUTH.tmp"
cat "$AUTH.tmp" > "$AUTH" && rm -f "$AUTH.tmp"
command -v restorecon >/dev/null && restorecon -R "$HOME/.ssh" 2>/dev/null || true

HOST_KEY="$(cut -d' ' -f1,2 /etc/ssh/ssh_host_ed25519_key.pub 2>/dev/null || true)"
PORT="$(sshd -T 2>/dev/null | awk '$1 == "port" { print $2; exit }' || true)"

cat <<EOF

Готово. В GitHub отворете хранилището -> Settings -> Secrets and variables -> Actions -> New repository secret
и добавете (името вляво, стойността вдясно):

  DEPLOY_HOST       IP адресът на сървъра или creatiumlab.com (ако DNS сочи директно към сървъра)
                    IP адреси тук: $(hostname -I 2>/dev/null || echo "?")
  DEPLOY_HOST_KEY   ${HOST_KEY:-"(не намерих /etc/ssh/ssh_host_ed25519_key.pub; попитайте Claude)"}
  DEPLOY_SSH_KEY    целият текст между линиите по-долу, включително BEGIN/END редовете
EOF
[ -n "$PORT" ] && [ "$PORT" != "22" ] && echo "  DEPLOY_PORT       $PORT"
echo
echo "----------------------------------------------------------------"
cat "$KEY"
echo "----------------------------------------------------------------"
echo
echo "После: GitHub -> Actions -> Deploy -> Run workflow, за да проверите. Частният ключ не го пращайте никъде другаде."

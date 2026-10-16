#!/usr/bin/env bash
# Инсталира Harvey на сървъра като отделен потребител 'harvey' (без достъп до сайта, .env и базата).
# Harvey само НАМИРА бизнеси и имейли: изпращането му е изключено (tools/harvey/harvey.local.yaml).
# Всеки ден в 06:30 списъкът се изнася в storage/app/harvey/prospects.csv на сайта,
# откъдето админът го внася с един бутон („Потенциални клиенти“ → „Внеси от сървъра“).
#
# Пуска се като root:  sudo bash /var/www/creatiumlab/deploy/harvey/install-harvey.sh
# Безопасно е да се пусне отново (обновява Harvey и конфигурацията).
set -euo pipefail

APP_DIR="/var/www/creatiumlab"
HUSER="harvey"
HHOME="/home/$HUSER"
HDIR="$HHOME/harvey"
EXPORT_DIR="$APP_DIR/storage/app/harvey"

[ "$(id -u)" = 0 ] || { echo "Пуснете с sudo."; exit 1; }

echo "== Python 3.12 и git"
dnf -y install python3.12 python3.12-pip git >/dev/null

echo "== Потребител $HUSER"
id "$HUSER" >/dev/null 2>&1 || useradd --create-home --shell /bin/bash "$HUSER"
chmod 700 "$HHOME"

echo "== Harvey"
if [ -d "$HDIR/.git" ]; then
    runuser -u "$HUSER" -- git -C "$HDIR" pull --ff-only
else
    runuser -u "$HUSER" -- git clone https://github.com/ethanplusai/harvey.git "$HDIR"
fi
runuser -u "$HUSER" -- bash -c "cd '$HDIR' && { [ -d .venv ] || python3.12 -m venv .venv; } && .venv/bin/pip install -q --upgrade pip && .venv/bin/pip install -q -e ."
install -o "$HUSER" -g "$HUSER" -m 644 "$APP_DIR/tools/harvey/harvey.local.yaml" "$HDIR/harvey.local.yaml"
install -o "$HUSER" -g "$HUSER" -m 644 "$APP_DIR/tools/harvey/product_knowledge.md" "$HDIR/skills/product_knowledge.md"
[ -f "$HDIR/.env" ] || install -o "$HUSER" -g "$HUSER" -m 600 "$HDIR/.env.example" "$HDIR/.env"

echo "== Claude Code за $HUSER"
if ! runuser -u "$HUSER" -- bash -lc 'command -v claude' >/dev/null 2>&1; then
    runuser -u "$HUSER" -- bash -lc 'curl -fsSL https://claude.ai/install.sh | bash'
fi

echo "== Услуги"
CLAUDE_PATH="$HHOME/.local/bin"
cat > /etc/systemd/system/harvey.service <<UNIT
[Unit]
Description=Harvey (Creatium Lab): finds businesses and emails, sends nothing
After=network-online.target
Wants=network-online.target

[Service]
User=$HUSER
WorkingDirectory=$HDIR
Environment=PYTHONUTF8=1
Environment=PATH=$CLAUDE_PATH:$HDIR/.venv/bin:/usr/local/bin:/usr/bin
ExecStart=$HDIR/.venv/bin/harvey run
Restart=on-failure
RestartSec=60
NoNewPrivileges=true
ProtectSystem=strict
ReadWritePaths=$HHOME /tmp

[Install]
WantedBy=multi-user.target
UNIT

cat > /etc/systemd/system/harvey-dashboard.service <<UNIT
[Unit]
Description=Harvey dashboard on 127.0.0.1:5555 (reach it with an SSH tunnel)
After=network-online.target

[Service]
User=$HUSER
WorkingDirectory=$HDIR
Environment=PYTHONUTF8=1
Environment=PATH=$CLAUDE_PATH:$HDIR/.venv/bin:/usr/local/bin:/usr/bin
ExecStart=$HDIR/.venv/bin/harvey dashboard
Restart=on-failure
NoNewPrivileges=true

[Install]
WantedBy=multi-user.target
UNIT

cat > /etc/systemd/system/harvey-export.service <<UNIT
[Unit]
Description=Export Harvey's verified prospects for the Creatium admin

[Service]
Type=oneshot
Environment=PYTHONUTF8=1
ExecStart=/usr/sbin/runuser -u $HUSER -- $HDIR/.venv/bin/harvey export --out $HHOME/prospects.csv
ExecStart=/usr/bin/install -d -o deploy -g deploy -m 750 $EXPORT_DIR
ExecStart=/usr/bin/install -o deploy -g deploy -m 640 $HHOME/prospects.csv $EXPORT_DIR/prospects.csv
ExecStart=/usr/sbin/restorecon -R $EXPORT_DIR
UNIT

cat > /etc/systemd/system/harvey-export.timer <<UNIT
[Unit]
Description=Daily export of Harvey's prospects

[Timer]
OnCalendar=*-*-* 06:30:00
Persistent=true

[Install]
WantedBy=timers.target
UNIT

systemctl daemon-reload
systemctl enable --now harvey-export.timer >/dev/null

cat <<NEXT

Готово. Harvey е инсталиран, но още НЕ е пуснат. Остават 3 стъпки (веднъж):

1. Вход в Claude с вашия абонамент (покажа линк, отворете го на компютъра си и поставете кода):
     sudo -iu $HUSER claude
   След входа напишете /exit.

2. Ключ за проверка на имейли (reoon.com), без кавички:
     sudo -iu $HUSER nano $HDIR/.env      → REOON_API_KEY=...

3. Сигнали и старт:
     sudo -iu $HUSER bash -c 'cd $HDIR && PYTHONUTF8=1 .venv/bin/harvey signals --confirm free && PYTHONUTF8=1 .venv/bin/harvey discover'
     sudo systemctl enable --now harvey harvey-dashboard

Таблото: на компютъра си  ssh -L 5555:127.0.0.1:5555 deploy@<IP>  и отворете http://localhost:5555
Логове:   sudo journalctl -u harvey -f
Спиране:  sudo systemctl stop harvey
Списъкът се изнася всеки ден в 06:30 (веднага: sudo systemctl start harvey-export).
NEXT

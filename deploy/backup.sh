#!/usr/bin/env bash
# Ежедневен бекъп на базата и качените файлове, пази последните 14 дни.
# Cron (като root):  30 3 * * * /bin/bash /var/www/creatiumlab/deploy/backup.sh
set -euo pipefail

APP_DIR="/var/www/creatiumlab"
DEST="/var/backups/creatium"
STAMP="$(date +%F)"

umask 077
mkdir -p "$DEST"
set -a; . "$APP_DIR/.env"; set +a

mysqldump --single-transaction -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "$DEST/db-$STAMP.sql.gz"
[ -d "$APP_DIR/public/uploads" ] && tar -czf "$DEST/uploads-$STAMP.tar.gz" -C "$APP_DIR/public" uploads
find "$DEST" -type f -mtime +14 -delete

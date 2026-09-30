#!/usr/bin/env bash
# Деплой на нова версия. Пуска се като потребител 'deploy' в /var/www/creatiumlab:
#   bash deploy/deploy.sh
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Липсва .env. Копирайте .env.example и го попълнете (вижте DEPLOY-WEBDOCK.md)."; exit 1; }

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force          # в production добавя само роли, категории и технологии
php artisan config:cache
php artisan route:cache
php artisan view:cache

chgrp nginx .env && chmod 640 .env

# Права за PHP-FPM (работи като nginx)
chgrp -R nginx storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
mkdir -p public/uploads/projects && chgrp -R nginx public/uploads && chmod -R ug+rwX public/uploads

# SELinux контекстите се прилагат върху новосъздадените файлове
command -v restorecon >/dev/null && restorecon -R storage bootstrap/cache public/uploads public/build || true

echo "Деплоят завърши."

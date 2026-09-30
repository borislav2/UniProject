#!/usr/bin/env bash
# Деплой на нова версия. Пуска се като потребител 'deploy' в /var/www/creatiumlab:
#   bash deploy/deploy.sh
set -euo pipefail
cd "$(dirname "$0")/.."

export PATH="/usr/local/bin:$PATH"
for tool in git composer npm php; do
    command -v "$tool" >/dev/null || { echo "Липсва '$tool'. Пуснете първо deploy/almalinux-setup.sh (или инсталирайте $tool)."; exit 1; }
done

[ -f .env ] || { echo "Липсва .env. Копирайте .env.example и го попълнете (вижте DEPLOY-WEBDOCK.md)."; exit 1; }

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
rm -f public/hot   # остатък от `npm run dev` кара Laravel да зарежда Vite dev сървър вместо билда
php artisan migrate --force
php artisan db:seed --force          # в production добавя само роли, категории и технологии
php artisan config:cache
php artisan route:cache
php artisan view:cache

# PHP-FPM работи като потребителя 'deploy', така че всички файлове са негови и не се сменят групи.
chmod 640 .env
chmod -R u+rwX storage bootstrap/cache
mkdir -p public/uploads/projects

echo "Деплоят завърши."

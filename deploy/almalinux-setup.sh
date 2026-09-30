#!/usr/bin/env bash
# Еднократна подготовка на чист AlmaLinux 9 сървър (Webdock) за Creatium Lab.
# Пуска се като root:  sudo bash deploy/almalinux-setup.sh creatiumlab.com
set -euo pipefail

# sudo на RHEL не включва /usr/local/bin (там е composer)
export PATH="/usr/local/bin:$PATH"

DOMAIN="${1:?Използване: almalinux-setup.sh <домейн>, напр. creatiumlab.com}"
APP_DIR="/var/www/creatiumlab"
APP_USER="deploy"
DB_NAME="creatium"
DB_USER="creatium"
PHP_STREAM="remi-8.3"

[ "$(id -u)" -eq 0 ] || { echo "Пуснете като root."; exit 1; }

echo "==> Обновяване и основни пакети"
dnf -y update
dnf -y install epel-release dnf-utils git unzip curl policycoreutils-python-utils

echo "==> PHP 8.3 (Remi), Nginx, MariaDB, Node.js, Certbot"
dnf -y install "https://rpms.remirepo.net/enterprise/remi-release-$(rpm -E %rhel).rpm" || true
dnf -y module reset php
dnf -y module enable "php:${PHP_STREAM}"
dnf -y module enable nodejs:22 || true
dnf -y install nginx mariadb-server nodejs npm \
    php-fpm php-cli php-mbstring php-xml php-mysqlnd php-pdo php-bcmath php-intl php-zip php-gd php-opcache php-curl \
    certbot python3-certbot-nginx

echo "==> Composer"
if ! command -v composer >/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> Потребител за деплой"
id "$APP_USER" >/dev/null 2>&1 || useradd -m -s /bin/bash "$APP_USER"
mkdir -p "$APP_DIR"
chown -R "$APP_USER":nginx "$APP_DIR"

echo "==> PHP-FPM (работи като $APP_USER, групата на сокета е nginx)"
sed -i "s/^user = .*/user = $APP_USER/; s/^group = .*/group = nginx/" /etc/php-fpm.d/www.conf
sed -i 's/^;\?listen.owner = .*/listen.owner = nginx/; s/^;\?listen.group = .*/listen.group = nginx/' /etc/php-fpm.d/www.conf
cat > /etc/php.d/99-creatium.ini <<'INI'
expose_php = Off
upload_max_filesize = 12M
post_max_size = 12M
opcache.enable = 1
opcache.validate_timestamps = 1
opcache.revalidate_freq = 2
INI

echo "==> MariaDB"
printf "[mysqld]\nbind-address=127.0.0.1\n" > /etc/my.cnf.d/99-creatium.cnf
systemctl enable --now mariadb
if [ -f /root/creatium-db.txt ]; then
    DB_PASS="$(grep '^DB_PASSWORD=' /root/creatium-db.txt | cut -d= -f2-)"
else
    DB_PASS="$(openssl rand -base64 24 | tr -d '=+/' | cut -c1-24)"
fi
mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"
umask 077
printf 'DB_DATABASE=%s\nDB_USERNAME=%s\nDB_PASSWORD=%s\n' "$DB_NAME" "$DB_USER" "$DB_PASS" > /root/creatium-db.txt

echo "==> Nginx"
sed "s/__DOMAIN__/$DOMAIN/g; s#__APP_DIR__#$APP_DIR#g" "$(dirname "$0")/nginx.conf" > /etc/nginx/conf.d/creatiumlab.conf
nginx -t

echo "==> SELinux (AlmaLinux го има включен по подразбиране)"
setsebool -P httpd_can_network_connect on      # изходящи връзки (SMTP към доставчика на имейли)
setsebool -P httpd_can_sendmail on
semanage fcontext -a -t httpd_sys_content_t "$APP_DIR(/.*)?" || true
for d in storage bootstrap/cache public/uploads; do
    semanage fcontext -a -t httpd_sys_rw_content_t "$APP_DIR/$d(/.*)?" || true
done
mkdir -p "$APP_DIR/public/uploads/projects"
chown -R "$APP_USER":nginx "$APP_DIR"
restorecon -R "$APP_DIR"

echo "==> Защитна стена"
if systemctl is-active --quiet firewalld; then
    firewall-cmd --permanent --add-service=http --add-service=https
    firewall-cmd --reload
fi

systemctl enable --now php-fpm nginx

echo "==> Проверка на инсталираното"
missing=0
for tool in php composer node npm nginx mysql certbot git; do
    if command -v "$tool" >/dev/null; then printf '  ok   %s\n' "$tool"; else printf '  ЛИПСВА %s\n' "$tool"; missing=1; fi
done
systemctl is-active --quiet nginx && systemctl is-active --quiet php-fpm && systemctl is-active --quiet mariadb \
    || { echo "  ЛИПСВА: някоя от услугите nginx/php-fpm/mariadb не работи"; missing=1; }
[ "$missing" -eq 0 ] || { echo "Инсталацията НЕ е пълна. Прегледайте грешките по-горе."; exit 1; }

cat <<MSG

Готово. Следващи стъпки:
  1. Данните за базата са в /root/creatium-db.txt (само за root). Сложете ги в .env.
  2. Като потребител '$APP_USER' клонирайте кода в $APP_DIR и пуснете deploy/deploy.sh (вижте DEPLOY-WEBDOCK.md).
  3. След като DNS за $DOMAIN сочи към този сървър:
       certbot --nginx -d $DOMAIN -d www.$DOMAIN
MSG

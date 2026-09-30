# Деплой на Webdock (AlmaLinux 9)

Скриптовете в `deploy/` са писани за AlmaLinux 9 и **не са изпробвани на жив Webdock сървър** (нямах достъп до такъв). Преди първото пускане направете **snapshot** от празния сървър в панела на Webdock, за да можете да започнете отначало при проблем.

Резултат: Nginx + PHP-FPM 8.3 + MariaDB, HTTPS с Let's Encrypt, SELinux включен, ежедневен бекъп.

## 1. Създаване на сървъра

1. В панела на Webdock: нов сървър, локация в Европа, профил по ваш бюджет (1 vCPU и 1-2 GB RAM стигат за този сайт), образ **AlmaLinux 9**.
2. Добавете вашия **SSH публичен ключ** (`ssh-keygen -t ed25519`, после съдържанието на `~/.ssh/id_ed25519.pub`).
3. Запишете IP адреса на сървъра и потребителя за вход, който Webdock показва (често не е `root`, а потребител със `sudo`).
4. Във firewall настройките на Webdock разрешете портове 22, 80 и 443.

## 2. DNS

При регистратора на домейна добавете:

| Тип | Име | Стойност |
|---|---|---|
| A | `@` | IP на сървъра |
| A | `www` | IP на сървъра |
| AAAA | `@` и `www` | IPv6 на сървъра (ако Webdock ви е дал такъв) |

Разпространението отнема от минути до няколко часа. Проверка: `dig +short creatiumlab.com`.

## 3. Подготовка на сървъра (еднократно)

```bash
ssh <потребител>@<IP>
sudo dnf -y install git
sudo git clone -b main https://github.com/borislav2/UniProject.git /var/www/creatiumlab
sudo bash /var/www/creatiumlab/deploy/almalinux-setup.sh creatiumlab.com
```

- Кодът трябва да е в `main`. Докато работата е на branch `ccr-77a66b3d-7rahdq`, или направете merge преди това, или сменете `-b main` с името на branch-а.
- Ако хранилището е частно, добавете *deploy key* (`ssh-keygen` на сървъра, публичният ключ в GitHub → Settings → Deploy keys) и клонирайте по SSH адреса.
- Скриптът инсталира пакетите, създава потребител `deploy`, база данни и Nginx конфигурация, настройва SELinux и firewalld. Паролата на базата се записва в `/root/creatium-db.txt` (само за root).

## 4. Настройка на приложението

```bash
sudo -iu deploy
cd /var/www/creatiumlab
cp .env.example .env
nano .env          # стойностите са описани в DEPLOY.md, раздел 3
php artisan key:generate
```

В `.env` сложете `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306` и `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` от `/root/creatium-db.txt` (`sudo cat /root/creatium-db.txt`). Не забравяйте `APP_ENV=production`, `APP_DEBUG=false` и `APP_URL=https://creatiumlab.com`.

```bash
bash deploy/deploy.sh
php artisan creatium:make-admin you@creatiumlab.com --name="Вашето име"
```

## 5. HTTPS

След като DNS сочи към сървъра:

```bash
sudo certbot --nginx -d creatiumlab.com -d www.creatiumlab.com
sudo systemctl enable --now certbot-renew.timer
sudo certbot renew --dry-run
```

Проверка: отворете `https://creatiumlab.com`, влезте в `/login`, изпратете тестово запитване от `/kontakti` и проверете, че се появява в админ панела и пристига на имейла.

## 6. Имейл от сървъра

Изходящият порт 25 често е блокиран при VPS доставчици. Ползвайте SMTP доставчик (Brevo, Mailgun, Postmark, Resend) на порт **587** и добавете SPF/DKIM/DMARC записите му в DNS. Настройките са в `.env` (`MAIL_*`).

## 7. Бекъпи

```bash
sudo crontab -e
# добавете реда:
30 3 * * * /bin/bash /var/www/creatiumlab/deploy/backup.sh
```

Скриптът пази последните 14 дни в `/var/backups/creatium`. Това е на същия сървър, затова включете и бекъпите/снапшотите на Webdock в панела и периодично копирайте архива извън сървъра.

## 8. Нови версии

```bash
sudo -iu deploy
cd /var/www/creatiumlab
bash deploy/deploy.sh
```

Обновявайте системата редовно: `sudo dnf -y update` (веднъж месечно, при нужда `sudo reboot`).

## Ако нещо не работи

| Симптом | Къде да погледнете |
|---|---|
| 502 Bad Gateway | `sudo systemctl status php-fpm`, `sudo tail /var/log/nginx/error.log` |
| 500 след деплой | `tail storage/logs/laravel.log`; проверете правата на `storage/` и `bootstrap/cache/` |
| 500/403 без запис в лога | SELinux: `sudo ausearch -m avc -ts recent`; след смяна на файлове пуснете `sudo restorecon -R /var/www/creatiumlab` |
| `npm run build` пада (няма памет) | билдвайте на компютъра си и качете `public/build` (`rsync -av public/build/ deploy@IP:/var/www/creatiumlab/public/build/`), или добавете swap |
| Формата не праща имейл | `MAIL_*` в `.env`; грешките се пишат в `storage/logs/laravel.log`, а запитването така или иначе се записва в `/admin` |
| Промени в `.env` не се виждат | `php artisan config:cache` |

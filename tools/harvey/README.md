# Harvey за Creatium Lab

[Harvey](https://github.com/ethanplusai/harvey) намира малки бизнеси, чете сайтовете им и открива проверени имейли.
При нас **Harvey само намира**. Писмата се пишат и пращат ръчно от админа на creatiumlab.com → **Потенциални клиенти**.

## На сървъра (препоръчано, работи 24/7)

```bash
sudo bash /var/www/creatiumlab/deploy/harvey/install-harvey.sh
```

Скриптът създава отделен потребител `harvey` без достъп до сайта и базата, инсталира Harvey и Claude Code и показва последните 3 стъпки (вход в Claude, ключ от Reoon, старт). Всеки ден в 06:30 списъкът се изнася за админа: **Потенциални клиенти → Внеси от сървъра**.

## 1. Инсталация на Windows (веднъж)

Нужни са Python 3.11 или по-нов (`py --version`), Git и Claude Code с вход в абонамента (`claude login`).
Командите са за Command Prompt (cmd). Не пишете `py pip ...` и не задавайте версия като `py -3.12`: `py` избира инсталирания Python.

```bat
git clone https://github.com/ethanplusai/harvey.git
cd harvey
py -m venv .venv
.venv\Scripts\activate
python -m pip install -e .
copy .env.example .env
```

След `activate` в началото на реда пише `(.venv)`. При всеки нов прозорец влезте в папката `harvey` и пуснете пак `.venv\Scripts\activate`.
В PowerShell, ако `activate` е блокиран: `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`, после `.venv\Scripts\Activate.ps1`.

Копирайте `harvey.local.yaml` от тази папка в папката `harvey`, а `product_knowledge.md` в `harvey\skills\`.

Безплатният източник (OpenStreetMap) в Harvey познава само няколко американски занаята и отказва „restaurant“ или „beauty salon“. Копирайте и `osm_categories.py` в папката `harvey` и пуснете `python osm_categories.py .` Пускайте го и след всяко обновяване на Harvey; ако `git pull` откаже, първо `git checkout -- harvey/collectors/discover.py`.

В `.env` добавете поне един ключ за проверка на имейли (без него адресите остават „guess“ и не стават за писане):
`REOON_API_KEY=` (600 безплатни проверки на месец, reoon.com). По желание `HUNTER_API_KEY=`.

Ако Harvey гръмне с „'charmap' codec can't decode“, Python чете кирилицата грешно. Веднъж пуснете `setx PYTHONUTF8 1` и отворете нов прозорец (в текущия PowerShell: `$env:PYTHONUTF8 = "1"`).

## 2. Какво да търси (веднъж)

```bat
harvey signals --confirm free
```

## 3. Намиране на бизнеси

```bat
harvey discover --estimate     # показва цената (OpenStreetMap е безплатен)
harvey discover                # намира бизнеси в София, Пловдив, Варна, Бургас, Велико Търново и Русе
harvey run                     # чете сайтовете, намира и проверява имейли; спрете с Ctrl+C
harvey export                  # записва prospects.csv само с проверени адреси
```

Таблото е на http://localhost:5555 (`harvey dashboard`).

## 4. Изпращане от админа

1. creatiumlab.com/admin → **Потенциални клиенти** → **Внеси** → изберете `prospects.csv`.
2. При всеки бизнес: **Напиши** → напишете на български какво сте забелязали на сайта им (Harvey го подсказва, често на английски) → **Изпрати**.
3. Писмата тръгват от отделния адрес (напр. info@creativemoa.site), до 20 на ден, всяко с линк за отписване.

Пощата за тези писма се настройва веднъж на сървъра (паролата се въвежда там, не в чат):

```bash
bash deploy/configure-outreach-mail.sh <smtp-хост> info@creativemoa.site
```

## Важно

- Домейнът creativemoa.site трябва да има SPF, DKIM и DMARC и да се „загрее“ 2–4 седмици с малко писма на ден.
- Пишете само на фирмени адреси, публикувани на сайта на бизнеса, и с конкретна причина. Отписаните никога не получават второ писмо (админът го пази автоматично).
- Harvey е настроен за английски сайтове: разпознаването на агенция във футъра (напр. „Изработка: …“) и част от сигналите работят по-слабо на български.

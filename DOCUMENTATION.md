# Система за управление на проекти - Документация

## Съдържание
1. [Въведение](#въведение)
2. [Изисквания към системата](#изисквания-към-системата)
3. [Инсталация и настройка](#инсталация-и-настройка)
4. [Структура на базата данни](#структура-на-базата-данни)
5. [Модели на данни](#модели-на-данни)
6. [Контролери](#контролери)
7. [Изгледи и потребителски интерфейс](#изгледи-и-потребителски-интерфейс)
8. [Роутове](#роутове)
9. [Функционалности](#функционалности)
10. [Административен панел](#административен-панел)
11. [Търсене и филтриране](#търсене-и-филтриране)
12. [Качване на файлове](#качване-на-файлове)
13. [Сигурност](#сигурност)
14. [Ролева базиран контрол на достъпа](#ролева-базиран-контрол-на-достъпа)
15. [Технологии](#технологии)
16. [Поддръжка и поддръжка](#поддръжка-и-поддръжка)

---

## Въведение

Системата за управление на проекти е уеб приложение, разработено с Laravel фреймуърк, предназначено за ефективно управление на проекти, технологии и категории. Системата предоставя административен панел за пълно управление на данните, както и публична част за представяне на проекта.

### Основни цели:
- Централизирано съхранение на информация за проекти
- Лесно управление на технологии и категории
- Мощни възможности за търсене и филтриране
- Интуитивен административен интерфейс
- Професионален публичен изглед

---

## Изисквания към системата

### Минимални изисквания:
- PHP 8.2 или по-нова версия
- MySQL 5.7+ или MariaDB 10.2+
- Web server (Apache, Nginx)
- Composer
- Node.js и NPM (за разработка)

### Препоръчителни изисквания:
- PHP 8.3
- MySQL 8.0
- SSD хранилище
- Минимум 2GB RAM

---

## Инсталация и настройка

### 1. Клониране на проекта
```bash
git clone <repository-url>
cd UniProject
```

### 2. Инсталиране на зависимости
```bash
composer install
npm install
```

### 3. Конфигурация на средата
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Настройка на базата данни
Редактирайте `.env` файла:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uniproject
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Миграция на базата данни
```bash
php artisan migrate
```

### 6. Попълване с примерни данни
```bash
php artisan db:seed
```

### 7. Стартиране на сървъра
```bash
php artisan serve
```

Приложението ще бъде достъпно на `http://localhost:8000`

---

## Структура на базата данни

### Таблици:

#### 1. `categories`
Съдържа информация за категориите на проектите.

**Колони:**
- `id` - Primary key
- `name` - Име на категория (string, 255)
- `description` - Описание на категория (text, nullable)
- `created_at` - Време на създаване
- `updated_at` - Време на актуализация

**Връзки:**
- `hasMany` - Projects

#### 2. `technologies`
Съдържа информация за технологиите, използвани в проектите.

**Колони:**
- `id` - Primary key
- `name` - Име на технологията (string, 255)
- `version` - Версия на технологията (string, 50, nullable)
- `created_at` - Време на създаване
- `updated_at` - Време на актуализация

**Връзки:**
- `belongsToMany` - Projects

#### 3. `projects`
Основна таблица за съхранение на проектите.

**Колони:**
- `id` - Primary key
- `name` - Име на проекта (string, 255)
- `description` - Описание на проекта (text)
- `start_date` - Начална дата (date)
- `end_date` - Крайна дата (date, nullable)
- `status` - Статус на проекта (string, 255)
- `manager` - Ръководител на проекта (string, 255)
- `category_id` - Foreign key към categories
- `file_path` - Път до прикачен файл (string, 255, nullable)
- `created_at` - Време на създаване
- `updated_at` - Време на актуализация

**Възможни стойности за status:**
- `Planning` - Планиране
- `In Progress` - В процес
- `Completed` - Завършен
- `On Hold` - На пауза
- `Cancelled` - Отменен

**Връзки:**
- `belongsTo` - Category
- `belongsToMany` - Technologies

#### 4. `project_technology`
Pivot таблица за many-to-many връзката между проекти и технологии.

**Колони:**
- `project_id` - Foreign key към projects
- `technology_id` - Foreign key към technologies
- `created_at` - Време на създаване
- `updated_at` - Време на актуализация

**Primary Key:** (`project_id`, `technology_id`)

#### 5. `users`
Стандартна Laravel таблица за потребители, разширена с:

**Допълнителни колони (чрез миграция):**
- `role` - Основна роля на потребителя (nullable)

**Връзки:**
- `roles` - Many-to-many връзка с Role модела

**Методи за проверка на роли:**
- `isAdmin()` - Проверява дали потребителя е администратор
- `isProjectManager()` - Проверява дали потребителя е Project Manager
- `isDeveloper()` - Проверява дали потребителя е Developer
- `canAccessAdminPanel()` - Проверява дали потребителя има достъп до административен панел
- `hasRole(string $role)` - Проверява за специфична роля
- `hasAnyRole(array $roles)` - Проверява за някоя от посочените роли

---

## Модели на данни

### 1. Category Model
**Местоположение:** `app/Models/Category.php`

**Основни методи:**
```php
// Връзка с проекти
public function projects()
{
    return $this->hasMany(Project::class);
}
```

**Fillable полета:**
- `name`
- `description`

### 2. Technology Model
**Местоположение:** `app/Models/Technology.php`

**Основни методи:**
```php
// Връзка с проекти
public function projects()
{
    return $this->belongsToMany(Project::class);
}
```

**Fillable полета:**
- `name`
- `version`

### 3. Project Model
**Местоположение:** `app/Models/Project.php`

**Основни методи:**
```php
// Връзка с категория
public function category()
{
    return $this->belongsTo(Category::class);
}

// Връзка с технологии
public function technologies()
{
    return $this->belongsToMany(Technology::class);
}

// Метод за търсене
public function scopeSearch(Builder $query, string $search): Builder
{
    return $query->where('name', 'like', "%{$search}%")
        ->orWhere('description', 'like', "%{$search}%")
        ->orWhere('manager', 'like', "%{$search}%")
        ->orWhere('status', 'like', "%{$search}%")
        ->orWhereHas('category', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orWhereHas('technologies', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
}
```

**Fillable полета:**
- `name`
- `description`
- `start_date`
- `end_date`
- `status`
- `manager`
- `category_id`
- `file_path`

**Casts:**
- `start_date` -> `date`
- `end_date` -> `date`

---

## Контролери

### 1. HomeController
**Местоположение:** `app/Http/Controllers/HomeController.php`

**Методи:**
- `index()` - Главна страница със статистика и избрани проекти
- `about()` - Страница "За нас"
- `contact()` - Контактна страница
- `showLoginForm()` - Показване на форма за вход
- `showRegisterForm()` - Показване на форма за регистрация
- `logout(Request $request)` - Излизане от системата

**Функционалности за автентикация:**
- Управление на сесии на потребители
- Валидация на входни данни
- Пренасочване след вход/изход

### 2. Admin/AdminController
**Местоположение:** `app/Http/Controllers/Admin/AdminController.php`

**Методи:**
- `dashboard()` - Административен дашборд със статистика

### 3. Admin/ProjectController
**Местоположение:** `app/Http/Controllers/Admin/ProjectController.php`

**Методи:**
- `index()` - Списък с проекти с пагинация
- `create()` - Форма за създаване на нов проект
- `store(Request $request)` - Запазване на нов проект
- `show(Project $project)` - Детайли за проект
- `edit(Project $project)` - Форма за редактиране
- `update(Request $request, Project $project)` - Актуализация на проект
- `destroy(Project $project)` - Изтриване на проект
- `search(Request $request)` - Търсене на проекти

**Валидация при създаване/редактиране:**
- `name` - задължително, максимум 255 символа
- `description` - задължително
- `start_date` - задължително, валидна дата
- `end_date` - опционално, след start_date
- `status` - задължително, избира се от списък
- `manager` - задължително, максимум 255 символа
- `category_id` - задължително, съществуваща категория
- `technologies` - опционално, масив от валидни технологии
- `file` - опционално, файл с валидни разширения

### 4. Admin/CategoryController
**Местоположение:** `app/Http/Controllers/Admin/CategoryController.php`

**Методи:**
- `index()` - Списък с категории с брой проекти
- `create()` - Форма за създаване
- `store(Request $request)` - Запазване на нова категория
- `show(Category $category)` - Детайли за категория
- `edit(Category $category)` - Форма за редактиране
- `update(Request $request, Category $category)` - Актуализация
- `destroy(Category $category)` - Изтриване (с проверка за проекти)

### 5. Admin/TechnologyController
**Местоположение:** `app/Http/Controllers/Admin/TechnologyController.php`

**Методи:**
- `index()` - Списък с технологии с брой проекти
- `create()` - Форма за създаване
- `store(Request $request)` - Запазване на нова технология
- `show(Technology $technology)` - Детайли за технология
- `edit(Technology $technology)` - Форма за редактиране
- `update(Request $request, Technology $technology)` - Актуализация
- `destroy(Technology $technology)` - Изтриване (с проверка за проекти)

### 6. Admin/UserController
**Местоположение:** `app/Http/Controllers/Admin/UserController.php`

**Методи:**
- `index()` - Списък с потребители
- `create()` - Форма за създаване на потребител
- `store(Request $request)` - Запазване на нов потребител
- `show(User $user)` - Детайли за потребител
- `edit(User $user)` - Форма за редактиране
- `update(Request $request, User $user)` - Актуализация на потребител
- `destroy(User $user)` - Изтриване на потребител

---

## Изгледи и потребителски интерфейс

### Публични изгледи

#### 1. Layout (`resources/views/layouts/public.blade.php`)
Основен шаблон за публичните страници с:
- Навигационно меню
- Responsive дизайн
- Footer с линкове
- Mobile menu

#### 2. Home Page (`resources/views/home.blade.php`)
Главна страница със:
- Hero секция с gradient фон
- Статистика в реално време
- Избрани проекти
- Категории с икони
- Популярни технологии
- Call-to-action секция

#### 3. About Page (`resources/views/about.blade.php`)
Страница "За нас" с:
- Мисия на системата
- Ключови функционалности
- Технологичен стек
- Информация за екипа

#### 4. Contact Page (`resources/views/contact.blade.php`)
Контактна страница с:
- Контактна форма
- Информация за връзка
- Социални мрежи
- FAQ секция

### Административни изгледи

#### 1. Admin Layout (`resources/views/admin/layout.blade.php`)
Основен шаблон за административния панел:
- Sidebar навигация
- Header с breadcrumbs
- Сообщения за успех/грешка
- Responsive дизайн

#### 2. Dashboard (`resources/views/admin/dashboard.blade.php`)
Административен дашборд със:
- Статистически картички
- Графики и диаграми
- Последни проекти
- Бързи линкове

#### 3. Project Views (`resources/views/admin/projects/`)
- `index.blade.php` - Списък с проекти, търсене, пагинация
- `create.blade.php` - Форма за създаване на проект
- `edit.blade.php` - Форма за редактиране на проект
- `show.blade.php` - Детайли за проект

#### 4. Category Views (`resources/views/admin/categories/`)
- `index.blade.php` - Списък с категории
- `create.blade.php` - Форма за създаване
- `edit.blade.php` - Форма за редактиране
- `show.blade.php` - Детайли за категория

#### 5. Technology Views (`resources/views/admin/technologies/`)
- `index.blade.php` - Списък с технологии
- `create.blade.php` - Форма за създаване
- `edit.blade.php` - Форма за редактиране
- `show.blade.php` - Детайли за технология

#### 6. Authentication Views (`resources/views/auth/`)
- `login.blade.php` - Форма за вход с демо данни
- `register.blade.php` - Форма за регистрация с валидация

**Функционалности за автентикация:**
- Условно показване на бутони (логнат/гост)
- Перонализирано съобщение за добре дошли
- Демо данни за лесен достъп
- Български интерфейс за всички форми

---

## Роутове

### Публични роутове
```php
// Главна страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// За нас
Route::get('/about', [HomeController::class, 'about'])->name('about');

// Контакти
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// Аутентикация
Route::get('/login', [HomeController::class, 'showLoginForm'])->name('login');
Route::get('/register', [HomeController::class, 'showRegisterForm'])->name('register');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');
```

### Административни роутове
```php
// Административен панел
Route::prefix('admin')->name('admin.')->group(function () {
    // Дашборд
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Проекти (CRUD + търсене)
    Route::resource('projects', ProjectController::class);
    Route::get('projects/search', [ProjectController::class, 'search'])->name('projects.search');
    
    // Категории (CRUD)
    Route::resource('categories', CategoryController::class);
    
    // Технологии (CRUD)
    Route::resource('technologies', TechnologyController::class);
    
    // Потребители (CRUD)
    Route::resource('users', UserController::class);
});
```

---

## Функционалности

### 1. Управление на проекти
- Създаване на нови проекти с детайлна информация
- Редактиране на съществуващи проекти
- Изтриване на проекти (с валидация)
- Преглед на детайли за проект
- Качване на файлове към проекти
- Свързване на проекти с технологии

### 2. Управление на категории
- Създаване на категории
- Редактиране на категории
- Изтриване на категории (с проверка за свързани проекти)
- Преглед на проекти в категория

### 3. Управление на технологии
- Създаване на технологии с версии
- Редактиране на технологии
- Изтриване на технологии (с проверка за свързани проекти)
- Преглед на проекти, използващи технологията

### 4. Управление на потребители
- Създаване на административни потребители
- Редактиране на потребителски данни
- Смяна на пароли
- Изтриване на потребители
- Автентикация и авторизация
- Управление на сесии
- Условно показване на интерфейс
- Български език за всички форми

**Функционалности за вход:**
- Форма за вход с валидация
- Демо данни за лесен достъп
- Запомняне на потребител
- Защита срещу brute force атаки

**Функционалности за регистрация:**
- Форма за регистрация с валидация
- Проверка за уникалност на имейл
- Потвърждение на парола
- Съгласие с условия за ползване

### 5. Търсене и филтриране
- Търсене на проекти по име
- Търсене по ръководител
- Търсене по категория
- Търсене по технология
- Комбинирано търсене

---

## Административен панел

### Достъп
- URL: `/admin`
- Изисква административни права

### Основни секции

#### 1. Дашборд
- Обща статистика
- Графики на състоянието на проектите
- Последни проекти
- Бързи действия

#### 2. Проекти
- Списък с всички проекти
- Филтриране и сортиране
- Търсене
- CRUD операции
- Управление на файлове

#### 3. Категории
- Списък с категории
- Брой проекти във всяка категория
- CRUD операции

#### 4. Технологии
- Списък с технологии
- Брой проекти по технологии
- CRUD операции

#### 5. Потребители
- Управление на административни акаунти
- CRUD операции

---

## Търсене и филтриране

### Функционалност за търсене
Системата предоставя мощна функция за търсене, която позволява търсене по:

1. **Име на проект** - Търсене в полето `name`
2. **Описание** - Търсене в полето `description`
3. **Ръководител** - Търсене в полето `manager`
4. **Статус** - Търсене в полето `status`
5. **Категория** - Търсене в името на категорията
6. **Технология** - Търсене в имената на технологиите

### Реализация
Търсенето е реализирано чрез scope метод в Project модела:

```php
public function scopeSearch(Builder $query, string $search): Builder
{
    return $query->where('name', 'like', "%{$search}%")
        ->orWhere('description', 'like', "%{$search}%")
        ->orWhere('manager', 'like', "%{$search}%")
        ->orWhere('status', 'like', "%{$search}%")
        ->orWhereHas('category', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orWhereHas('technologies', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
}
```

---

## Качване на файлове

### Поддържани формати
- **Документи:** PDF, DOC, DOCX, TXT
- **Изображения:** JPG, JPEG, PNG, GIF
- **Максимален размер:** 10MB

### Процес на качване
1. Файлът се качва чрез HTML форма
2. Валидация на тип и размер
3. Генериране на уникално име
4. Преместване в `public/uploads/projects/`
5. Запазване на пътя в базата данни

### Сигурност
- Валидация на MIME типове
- Ограничение на размера
- Уникални имена на файловете
- Проверка за съществуващи файлове

---

## Сигурност

### Мерки за сигурност

#### 1. Валидация на входни данни
- Всички входни данни се валидират
- Използват се Laravel валидационни правила
- CSRF защита за всички форми

#### 2. Авторизация
- Административен достъп само за оторизирани потребители
- Разделение на публични и административни функции

#### 3. Защита на файлове
- Валидация на файлови типове
- Ограничение на размерите
- Сигурно съхранение

#### 4. SQL Injection защита
- Използване на Eloquent ORM
- Parameterized queries

## Ролева базиран контрол на достъпа (Role-Based Access Control)

### Преглед на системата
Системата използва пълна ролева базирана контрол на достъпа (RBAC) за защита на административния панел и управление на потребителските права.

### Дефинирани роли
1. **Administrator (admin)** - Пълен достъп до всички функции
   - Управление на всички проекти
   - Управление на всички категории
   - Управление на всички технологии
   - Управление на потребители
   - Пълен административен достъп

2. **Project Manager (project-manager)** - Управление на проекти и ресурси
   - Създаване и редактиране на проекти
   - Управление на категории
   - Управление на технологии
   - Достъп до административен панел

3. **Developer (developer)** - Достъп до проекти и технологии
   - Преглед на проекти
   - Преглед на категории
   - Преглед на технологии
   - Ограничен административен достъп

### База данни
**Таблица `roles`:**
- `id` - Уникален идентификатор
- `name` - Име на ролята
- `slug` - Уникален идентификатор (admin, project-manager, developer)
- `description` - Описание на ролята
- `color` - Цвят за визуализация
- `created_at`, `updated_at` - Времеви отпечатъци

**Таблица `user_roles`:**
- `user_id` - ID на потребител
- `role_id` - ID на роля
- Комбиниран първичен ключ (user_id, role_id)

### Модели и методи
**User Model:**
```php
// Релации
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class, 'user_roles');
}

// Помощни методи
public function hasRole(string $role): bool
public function hasAnyRole(array $roles): bool
public function isAdmin(): bool
public function isProjectManager(): bool
public function isDeveloper(): bool
public function canAccessAdminPanel(): bool
```

**Role Model:**
```php
// Релации
public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'user_roles');
}
```

### Middleware за защита
**CheckAdminAccess Middleware:**
- Проверява дали потребителят е логнат
- Проверява дали потребителят има права за достъп до admin панела
- Пренасочва към login при неоторизиран достъп
- Връща 403 грешка при липса на права

### Роутове и защита
```php
// Административни роутове с двойна защита
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Всички административни роутове
});
```

### Демо потребители
- **Administrator:** admin@projectmanager.com / password
- **Project Manager:** pm@projectmanager.com / password
- **Developer:** dev@projectmanager.com / password

### Потребителски интерфейс
- **Login форма:** С CSRF защита и валидация
- **Register форма:** Автоматично присвояване на developer роля
- **Navigation:** Показване на роли и бутони според статус
- **Admin Panel:** Достъп само за оторизирани потребители

### Сигурност
- Двойна защита (auth + admin middleware)
- Сесийна защита при вход/изход
- CSRF токени за всички форми
- Валидация на входни данни
- Ролева базирана авторизация

---

## Технологии

### Backend
- **Laravel 12.x** - PHP фреймуърк
- **MySQL 8.0** - База данни
- **Composer** - Управление на зависимости

### Frontend
- **Tailwind CSS** - CSS фреймуърк
- **Font Awesome** - Икони
- **JavaScript** - Клиентска логика

### Инструменти за разработка
- **Git** - Версион контрол
- **NPM** - Управление на frontend зависимости
- **PHPUnit** - Unit тестове

---

## Поддръжка и поддръжка

### Поддръжка на системата

#### 1. Резервни копия
- Редовни резервни копия на базата данни
- Архивиране на качените файлове

#### 2. Обновления
- Редовни обновления на Laravel
- Обновления на зависимости
- Пачове за сигурност

#### 3. Мониторинг
- Логване на грешки
- Performance мониторинг
- Security сканиране

### Тroubleshooting

#### Често срещани проблеми

1. **Проблеми с базата данни**
   - Проверете връзката в `.env`
   - Уверете се, че миграциите са изпълнени

2. **Проблеми с файлове**
   - Проверете права за писане в `storage/`
   - Проверете права за `public/uploads/`

3. **Performance проблеми**
   - Оптимизирайте заявките към базата
- Използвайте кеширане

### Контакт за поддръжка
- Email: support@projectmanager.com
- Phone: +1 (555) 987-6543
- Documentation: [GitHub Wiki]

---

## Заключение

Системата за управление на проекти предоставя цялостно решение за управление на проекти, технологии и категории. С модерния си интерфейс, мощни функционалности и сигурна архитектура, системата е подходяща за малки и средни екипи, които искат да оптимизират работния си процес.

Системата е разработена с най-добри практики в уеб разработката и е готова за продуктивна употреба. Тя е лесна за разширяване и адаптиране към специфични нужди на клиента.

---

*Документацията е актуализирана на: {{ date('d.m.Y') }}*
*Версия на системата: 1.0.0*

# GND Admin

**KB @CerbeRus - Nexus Invest Team**

Админ-панель на Laravel с авторизацией (Laravel Breeze). Предназначена для управления данными GND (кошельки, API-ключи и т.д.) и интеграции с нодой GND по REST API.

## Требования

- PHP 8.2+
- Composer
- Node.js 18+ и npm (для сборки фронтенда)
- Расширения PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Установка (уже выполнена)

```bash
composer create-project laravel/laravel GND_admin
cd GND_admin
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
```

## Запуск

1. **Переменные окружения**

   Файл `.env` уже создан из `.env.example`. При первом запуске на другом окружении:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   По умолчанию используется SQLite (`database/database.sqlite`). Для MySQL/PostgreSQL измените `DB_*` в `.env`.

2. **Миграции**

   ```bash
   php artisan migrate
   ```

3. **Сервер разработки**

   ```bash
   php artisan serve
   ```

   Приложение будет доступно по адресу: **http://127.0.0.1:8000**

## Авторизация

- **Вход только по паролю:** http://127.0.0.1:8000/login (публичная регистрация отключена; пользователей создаёт только админ или сидер).
- **Dashboard (после входа):** http://127.0.0.1:8000/dashboard  
- **Профиль:** http://127.0.0.1:8000/profile  

**RBAC:** у пользователей есть роль `user` или `admin`. Админ-панель доступна только с ролью `admin`.

**Первый администратор** создаётся при выполнении `php artisan db:seed`. По умолчанию: email `admin@localhost`, пароль `password`. Чтобы задать свои данные, в `.env` укажите `GND_ADMIN_EMAIL` и `GND_ADMIN_PASSWORD`, затем снова выполните `php artisan db:seed`.

## Интеграция с GND (опционально)

В `.env` можно задать переменные для подключения к ноде GND:

```env
# URL ноды GND (REST API)
GND_NODE_URL=http://localhost:8182

# API-ключ для операций создания кошелька и т.д.
GND_API_KEY=

# Админ-токен ноды (если реализован эндпоинт создания API-ключей)
# GND_ADMIN_SECRET=
```

Использование в коде: `config('services.gnd.node_url')`, `config('services.gnd.api_key')`. При необходимости добавьте в `config/services.php` секцию `gnd`.

**Состояния контрактов (чтение и запись):** сервис `App\Services\GndAdminApi` предоставляет методы для работы с API состояний ноды (требуется миграция 014 на ноде):

- `getAccountStateCurrent($address)` — текущее состояние аккаунта (GET /api/v1/state/account/:address)
- `getAccountStateAtBlock($address, $blockId)` — снимок состояния на блок (GET /api/v1/state/account/:address/block/:blockId)
- `getContractStorage($address, $blockId)` — слоты storage контракта на блок (GET /api/v1/state/contract/:address/storage?block_id=)
- `writeContractStorageSlot($address, $blockId, $slotKey, $slotValue)` — запись слота (POST /api/v1/admin/state/contract/:address/storage, требуется X-Admin-Token)

Подробнее: `docs/wallet-admin-testing.md`, в репозитории GND_v1 — `docs/api.md`, `docs/admin-api.md`.

## Админ-панель (роль admin)

- **Админ:** http://127.0.0.1:8000/admin — обзор (количество пользователей и админов), ссылка на управление пользователями.
- **Пользователи:** http://127.0.0.1:8000/admin/users — список пользователей, смена роли (user / admin).

В навигации ссылка «Админ» отображается только у пользователей с ролью `admin`.

## Структура

- `app/Http/Controllers/` — контроллеры (Auth, Profile, Admin\DashboardController, Admin\UserController)
- `app/Http/Middleware/EnsureUserHasRole.php` — RBAC: проверка роли (middleware `role:admin`)
- `app/Models/User.php` — поле `role`, константы `ROLE_USER`, `ROLE_ADMIN`, метод `isAdmin()`
- `resources/views/admin/` — Blade-шаблоны админки (dashboard, users.index, users.edit)
- `routes/web.php` — маршруты; группа `admin` с middleware `auth`, `verified`, `role:admin`
- `database/seeders/AdminUserSeeder.php` — создание администратора по умолчанию

## Документация Laravel

- [Laravel](https://laravel.com/docs)
- [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze)

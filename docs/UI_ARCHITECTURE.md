# UI / фронтенд GND_admin

## Стек

- **Основной UI админки:** Bootstrap 5 (`resources/css/app.css`, тёмная тема `data-bs-theme="dark"`).
- **Tailwind** (директивы в конце `app.css`): оставлен для legacy-шаблонов Breeze — `components/modal`, `components/dropdown`, `welcome.blade.php`. При удалении Tailwind эти компоненты нужно переписать на Bootstrap или изолировать отдельным entry.

## Токены темы

CSS-переменные в `[data-bs-theme="dark"]`: `--admin-bg-page`, `--admin-bg-card`, `--admin-border`, `--admin-link`, `--admin-text-muted`. Расширять при введении второй темы.

## Навигация

Пункты бокового меню задаются в **`config/admin_navigation.php`** и выводятся через **`resources/views/partials/admin-nav-items.blade.php`** (десктоп и мобильный offcanvas). Добавляя раздел, правьте только конфиг.

## Компоненты

| Компонент | Назначение |
|-----------|------------|
| `<x-admin.page-header>` | Заголовок страницы + слот `actions` для кнопок. |
| `<x-admin.breadcrumbs>` | Цепочка `:items="[['label'=>'…','url'=>…], …]"`. |
| `<x-admin.alert>` | Алерты с `type`: `danger`, `warning`, `success`, `info`; `role="alert"` и `aria-live`. |

## Ошибки ноды

Разбор ответов в **`App\Support\GndNodeError`**: человекочитаемое сообщение, подсказка для 401/X-Admin-Token, сырой текст для блока «Технические детали».

## Модальные окна (SweetAlert2)

Темизация в **`resources/js/app.js`** через `Swal.mixin` (тёмный фон, читаемый текст). Flash из сессии поддерживает `data-flash-hint` и `data-flash-raw`.

## Доступность

- Ссылка **«К основному содержимому»** в `layouts/app.blade.php`.
- У `<main>` атрибуты `id="main-content"` и `tabindex="-1"`.
- Регион `#aria-flash-live` для объявления итоговых сообщений screen reader’ам.

## Загрузка форм

Формы с атрибутом **`data-loading-on-submit`** блокируют кнопку отправки и показывают спиннер; у кнопки опционально **`data-loading-label`**.

## Плейсхолдер загрузки

Класс **`.admin-skeleton-bar`** в `app.css` — для будущих async-блоков или отложенной подгрузки данных с ноды.

## Удалённые файлы

- **`resources/views/layouts/navigation.blade.php`** — не подключался (navbar Breeze); навигация задаётся в `layouts/app.blade.php`. При необходимости старого вида см. историю git.

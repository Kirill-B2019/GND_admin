<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    {{-- Мобильная вёрстка и общие стили в resources/css/app.css (сборка: npm run dev / npm run build) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark min-vh-100 d-flex">
    {{-- Боковое меню (десктоп) --}}
    <aside class="app-sidebar bg-dark border-end border-secondary d-none d-lg-flex flex-column">
        <div class="p-3 border-bottom border-secondary">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none text-light">
                <x-application-logo class="fill-current text-secondary me-2" style="width: 2rem; height: 2rem;" />
                <span class="fw-semibold">{{ config('app.name', 'GND Admin') }}</span>
            </a>
        </div>
        <nav class="flex-grow-1 p-2">
            <ul class="nav flex-column">
                @if(Auth::user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.dashboard') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.dashboard') }}">
                        Обзор
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.users.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.users.index') }}">
                        Пользователи
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.accounts.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.accounts.index') }}">
                        Аккаунты
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.wallets.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.wallets.index') }}">
                        Кошельки
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.contracts.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.contracts.index') }}">
                        Контракты
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.tokens.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.tokens.index') }}">
                        Токены
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.transactions.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.transactions.index') }}">
                        Транзакции
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.keys.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.keys.index') }}">
                        Ключи приложений
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.api.*') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.api.index') }}">
                        API (REST, WS, RPC)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light {{ request()->routeIs('admin.instruction.index') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.instruction.index') }}">
                        Инструкции
                    </a>
                </li>
                <li class="nav-item ps-3 small">
                    <a class="nav-link text-light {{ request()->routeIs('admin.instruction.steps') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.instruction.steps') }}">
                        Пошаговая инструкция
                    </a>
                </li>
                <li class="nav-item ps-3 small">
                    <a class="nav-link text-light {{ request()->routeIs('admin.instruction.setup') ? 'active bg-secondary bg-opacity-25 rounded' : '' }}" href="{{ route('admin.instruction.setup') }}">
                        Сводная настройка
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        <div class="p-3 border-top border-secondary">
            <div class="text-secondary small mb-2">{{ Auth::user()->name }}</div>
            <div class="d-flex flex-column gap-1">
                <a class="btn btn-outline-light btn-sm text-start" href="{{ route('profile.edit') }}">Профиль</a>
                <form method="POST" action="{{ route('logout') }}" data-confirm="Выйти из учётной записи?">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Выход</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Кнопка открытия меню на мобильных --}}
    <div class="d-lg-none fixed-top start-0 end-0 p-2 bg-dark border-bottom border-secondary" style="z-index: 1030;">
        <button id="mobile-menu-btn" class="btn btn-outline-secondary btn-sm py-2 px-3" type="button" aria-controls="sidebarOffcanvas" aria-label="Открыть меню" style="min-height: 44px;">
            Меню
        </button>
    </div>

    {{-- Затемнение фона при открытом меню (мобильные) --}}
    <div id="sidebarBackdrop" class="d-lg-none" aria-hidden="true"></div>

    {{-- Боковая панель меню (мобильные) --}}
    <div class="offcanvas offcanvas-start bg-dark border-end border-secondary d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" aria-hidden="true">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title text-light" id="sidebarOffcanvasLabel">{{ config('app.name', 'GND Admin') }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <nav class="flex-grow-1 p-2">
                <ul class="nav flex-column">
                    @if(Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.dashboard') }}" data-bs-dismiss="offcanvas">Обзор</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.users.index') }}" data-bs-dismiss="offcanvas">Пользователи</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.accounts.index') }}" data-bs-dismiss="offcanvas">Аккаунты</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.wallets.index') }}" data-bs-dismiss="offcanvas">Кошельки</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.contracts.index') }}" data-bs-dismiss="offcanvas">Контракты</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.tokens.index') }}" data-bs-dismiss="offcanvas">Токены</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.transactions.index') }}" data-bs-dismiss="offcanvas">Транзакции</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.keys.index') }}" data-bs-dismiss="offcanvas">Ключи приложений</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.api.index') }}" data-bs-dismiss="offcanvas">API (REST, WS, RPC)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('admin.instruction.index') }}" data-bs-dismiss="offcanvas">Инструкции</a>
                    </li>
                    <li class="nav-item ps-3">
                        <a class="nav-link text-light small" href="{{ route('admin.instruction.steps') }}" data-bs-dismiss="offcanvas">Пошаговая инструкция</a>
                    </li>
                    <li class="nav-item ps-3">
                        <a class="nav-link text-light small" href="{{ route('admin.instruction.setup') }}" data-bs-dismiss="offcanvas">Сводная настройка</a>
                    </li>
                    @endif
                </ul>
            </nav>
            <div class="p-3 border-top border-secondary">
                <div class="text-secondary small mb-2">{{ Auth::user()->name }}</div>
                <a class="btn btn-outline-light btn-sm d-block mb-1" href="{{ route('profile.edit') }}" data-bs-dismiss="offcanvas">Профиль</a>
                <form method="POST" action="{{ route('logout') }}" data-confirm="Выйти из учётной записи?">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Выход</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Центральное окно (контент) --}}
    <main class="app-main flex-grow-1 p-4 pt-5 pt-lg-4">
        @if(session('status'))
            <div class="visually-hidden" data-flash="success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="visually-hidden" data-flash="error">{{ session('error') }}</div>
        @endif
        @isset($header)
            <header class="mb-3 mb-md-4" role="banner">
                {{ $header }}
            </header>
        @endisset
        <div class="flex-grow-1">
            {{ $slot }}
        </div>
    </main>
    @stack('scripts')
</body>
</html>

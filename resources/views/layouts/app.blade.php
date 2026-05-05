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
    <a href="#main-content" class="visually-hidden-focusable position-fixed top-0 start-0 p-2 m-2 btn btn-sm btn-primary" style="z-index: 1080;">К основному содержимому</a>
    {{-- Боковое меню (десктоп) --}}
    <aside class="app-sidebar bg-dark border-end border-secondary d-none d-lg-flex flex-column">
        <div class="p-3 border-bottom border-secondary">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none text-light">
                <x-application-logo class="fill-current text-secondary me-2" style="width: 2rem; height: 2rem;" />
                <span class="fw-semibold">{{ config('app.name', 'GND Admin') }}</span>
            </a>
        </div>
        <nav class="flex-grow-1 p-2" aria-label="{{ __('Разделы админки') }}">
            @if(Auth::user()->isAdmin())
                <ul class="nav flex-column">
                    @include('partials.admin-nav-items', ['mobile' => false])
                </ul>
            @endif
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
            <nav class="flex-grow-1 p-2" aria-label="{{ __('Разделы админки') }}">
                @if(Auth::user()->isAdmin())
                    <ul class="nav flex-column">
                        @include('partials.admin-nav-items', ['mobile' => true])
                    </ul>
                @endif
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
    <main id="main-content" class="app-main flex-grow-1 p-4 pt-5 pt-lg-4" tabindex="-1">
        <div id="aria-flash-live" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>
        @if(session('status'))
            <div class="visually-hidden" data-flash="success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            @php
                $parsedErr = \App\Support\GndNodeError::parse(is_string(session('error')) ? session('error') : (string) session('error'));
            @endphp
            <div class="visually-hidden" data-flash="error" data-flash-hint="{{ $parsedErr['hint'] ?? '' }}" data-flash-raw="{{ e($parsedErr['raw']) }}">{{ $parsedErr['user_message'] }}</div>
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

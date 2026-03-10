<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-dark min-vh-100 d-flex flex-column justify-content-center align-items-center py-5">
        <div class="mb-4 d-flex flex-column align-items-center text-center">
            <a href="/" class="d-flex flex-column align-items-center text-decoration-none text-reset">
                <x-application-logo class="fill-current text-secondary w-20"/>
                <span class="text-xl text-uppercase mt-2">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div>
        <div class="card bg-dark border-secondary w-100 mx-3" style="max-width: 28rem;">
            <div class="card-body p-4">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin2FA
{
    /**
     * Проверка прохождения 2FA для админа. Если 2FA отключена в конфиге — пропуск.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('admin.2fa_enabled', false)) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || ! $user->isAdmin()) {
            return $next($request);
        }

        // Маршруты настройки и проверки 2FA не требуют повторной верификации
        if ($request->routeIs('admin.2fa.*')) {
            return $next($request);
        }

        if (session('admin_2fa_verified') === $user->id) {
            return $next($request);
        }

        // Нет секрета — сначала настройка
        if (empty($user->two_factor_secret) || ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.2fa.setup');
        }

        return redirect()->route('admin.2fa.challenge');
    }
}

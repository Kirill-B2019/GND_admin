<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Показать форму входа.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Обработка входа: при включённой 2FA админ идёт на проверку/настройку 2FA, иначе — в админ-панель.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (config('admin.2fa_enabled', false) && $request->user()?->isAdmin()) {
            if (! $request->user()->hasTwoFactorEnabled()) {
                return redirect()->intended(route('admin.2fa.setup', absolute: false));
            }
            return redirect()->intended(route('admin.2fa.challenge', absolute: false));
        }

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Выход из системы.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

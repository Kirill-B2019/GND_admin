<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RobThree\Auth\Providers\Qr\QRServerProvider;
use RobThree\Auth\TwoFactorAuth;

class TwoFactorController extends Controller
{
    private function twoFactorAuth(): TwoFactorAuth
    {
        return new TwoFactorAuth(
            new QRServerProvider(verifyssl: true),
            config('app.name', 'GND Admin'),
            6,
            30
        );
    }

    /**
     * Форма ввода кода 2FA после входа.
     */
    public function challenge(): View
    {
        return view('admin.2fa.challenge');
    }

    /**
     * Проверка кода и отметка сессии как верифицированной.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ]);

        $user = $request->user();
        if (! $user || ! $user->two_factor_secret) {
            throw ValidationException::withMessages(['code' => ['Сначала настройте двухфакторную аутентификацию.']]);
        }

        $tfa = $this->twoFactorAuth();
        if (! $tfa->verifyCode($user->two_factor_secret, $request->input('code'))) {
            throw ValidationException::withMessages(['code' => ['Неверный код.']]);
        }

        session(['admin_2fa_verified' => $user->id]);

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Страница настройки 2FA: QR-код и секрет.
     */
    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        $secret = session('admin_2fa_secret_pending');
        if (! $secret) {
            $tfa = $this->twoFactorAuth();
            $secret = $tfa->createSecret();
            session(['admin_2fa_secret_pending' => $secret]);
        }

        $tfa = $this->twoFactorAuth();
        $label = $user->email;
        $qrDataUri = $tfa->getQRCodeImageAsDataUri($label, $secret, 200);

        return view('admin.2fa.setup', [
            'qrDataUri' => $qrDataUri,
            'secret' => $secret,
        ]);
    }

    /**
     * Подтверждение настройки: проверка кода и сохранение секрета в БД.
     */
    public function confirmSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ]);

        $user = $request->user();
        $secret = session('admin_2fa_secret_pending');
        if (! $user || ! $secret) {
            session()->forget('admin_2fa_secret_pending');
            return redirect()->route('admin.2fa.setup');
        }

        $tfa = $this->twoFactorAuth();
        if (! $tfa->verifyCode($secret, $request->input('code'))) {
            throw ValidationException::withMessages(['code' => ['Неверный код. Введите код из приложения.']]);
        }

        $user->two_factor_secret = $secret;
        $user->two_factor_confirmed_at = now();
        $user->save();

        session()->forget('admin_2fa_secret_pending');
        session(['admin_2fa_verified' => $user->id]);

        return redirect()->route('admin.dashboard')->with('status', '2FA включена.');
    }
}

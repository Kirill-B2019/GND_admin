<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Регистрация отключена
    Route::get('register', fn () => redirect()->route('login'))->name('register');
    Route::post('register', fn () => redirect()->route('login'));

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Восстановление пароля отключено
    Route::get('forgot-password', fn () => redirect()->route('login'))->name('password.request');
    Route::post('forgot-password', fn () => redirect()->route('login'))->name('password.email');
    Route::get('reset-password/{token}', fn () => redirect()->route('login'))->name('password.reset');
    Route::post('reset-password', fn () => redirect()->route('login'))->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Верификация по email отключена — редирект на дашборд
    Route::get('verify-email', fn () => redirect()->route('dashboard'))->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', fn () => redirect()->route('dashboard'))->name('verification.verify');
    Route::post('email/verification-notification', fn () => redirect()->route('dashboard'))->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

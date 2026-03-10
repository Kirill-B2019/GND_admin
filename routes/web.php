<?php

use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\ApiController as AdminApiController;
use App\Http\Controllers\Admin\AppKeyController as AdminAppKeyController;
use App\Http\Controllers\Admin\ContractController as AdminContractController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InstructionController as AdminInstructionController;
use App\Http\Controllers\Admin\TokenController as AdminTokenController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\TwoFactorController as AdminTwoFactorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('admin.dashboard') : redirect()->route('login'))->name('home');
Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Маршруты 2FA для админа (без middleware admin.2fa, иначе редирект по кругу)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/2fa/challenge', [AdminTwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/2fa/verify', [AdminTwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::get('/2fa/setup', [AdminTwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/confirm-setup', [AdminTwoFactorController::class, 'confirmSetup'])->name('2fa.confirm-setup');
});

Route::middleware(['auth', 'role:admin', 'admin.2fa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/api', [AdminApiController::class, 'index'])->name('api.index');
    Route::post('/api/send-request', [AdminApiController::class, 'sendRequest'])->name('api.send-request');
    Route::get('/instruction', [AdminInstructionController::class, 'index'])->name('instruction.index');
    Route::get('/instruction/steps', [AdminInstructionController::class, 'steps'])->name('instruction.steps');
    Route::get('/instruction/setup', [AdminInstructionController::class, 'setup'])->name('instruction.setup');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    Route::get('/accounts', [AdminAccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AdminAccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AdminAccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{account}', [AdminAccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{account}/edit', [AdminAccountController::class, 'edit'])->name('accounts.edit');
    Route::patch('/accounts/{account}', [AdminAccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}', [AdminAccountController::class, 'destroy'])->name('accounts.destroy');

    Route::get('/wallets', [AdminWalletController::class, 'index'])->name('wallets.index');
    Route::get('/wallets/create', [AdminWalletController::class, 'create'])->name('wallets.create');
    Route::post('/wallets', [AdminWalletController::class, 'store'])->name('wallets.store');
    Route::get('/wallets/{address}/edit', [AdminWalletController::class, 'edit'])->name('wallets.edit');
    Route::patch('/wallets/{address}', [AdminWalletController::class, 'update'])->name('wallets.update');
    Route::post('/wallets/{address}/disable', [AdminWalletController::class, 'disable'])->name('wallets.disable');
    Route::post('/wallets/{address}/enable', [AdminWalletController::class, 'enable'])->name('wallets.enable');
    Route::delete('/wallets/{address}', [AdminWalletController::class, 'destroy'])->name('wallets.destroy');
    Route::get('/wallets/{address}', [AdminWalletController::class, 'show'])->name('wallets.show');

    Route::get('/keys', [AdminAppKeyController::class, 'index'])->name('keys.index');
    Route::get('/keys/create', [AdminAppKeyController::class, 'create'])->name('keys.create');
    Route::post('/keys', [AdminAppKeyController::class, 'store'])->name('keys.store');
    Route::get('/keys/created', [AdminAppKeyController::class, 'created'])->name('keys.created');
    Route::post('/keys/{id}/revoke', [AdminAppKeyController::class, 'revoke'])->name('keys.revoke')->where('id', '[0-9]+');

    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [AdminTransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [AdminTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{id}', [AdminTransactionController::class, 'show'])->name('transactions.show')->where('id', '[0-9]+');

    Route::get('/tokens', [AdminTokenController::class, 'index'])->name('tokens.index');
    Route::get('/tokens/deploy', [AdminTokenController::class, 'deployForm'])->name('tokens.deploy');
    Route::post('/tokens/deploy', [AdminTokenController::class, 'deploySubmit'])->name('tokens.deploy.submit');
    Route::get('/tokens/create', [AdminTokenController::class, 'create'])->name('tokens.create');
    Route::post('/tokens', [AdminTokenController::class, 'store'])->name('tokens.store');
    Route::get('/tokens/{token}', [AdminTokenController::class, 'show'])->name('tokens.show');
    Route::get('/tokens/{token}/edit', [AdminTokenController::class, 'edit'])->name('tokens.edit');
    Route::patch('/tokens/{token}', [AdminTokenController::class, 'update'])->name('tokens.update');
    Route::delete('/tokens/{token}', [AdminTokenController::class, 'destroy'])->name('tokens.destroy');

    Route::get('/contracts', [AdminContractController::class, 'index'])->name('contracts.index');
    Route::post('/contracts/sync-from-node', [AdminContractController::class, 'syncFromNode'])->name('contracts.sync-from-node');
    Route::post('/contracts/sync-abi-to-node', [AdminContractController::class, 'syncAbiToNode'])->name('contracts.sync-abi-to-node');
    Route::get('/contracts/create', [AdminContractController::class, 'create'])->name('contracts.create');
    Route::get('/contracts/template', [AdminContractController::class, 'loadTemplate'])->name('contracts.template');
    Route::post('/contracts', [AdminContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}', [AdminContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{contract}/edit', [AdminContractController::class, 'edit'])->name('contracts.edit');
    Route::patch('/contracts/{contract}', [AdminContractController::class, 'update'])->name('contracts.update');
    Route::post('/contracts/{contract}/compile', [AdminContractController::class, 'compile'])->name('contracts.compile');
    Route::post('/contracts/{contract}/analyze', [AdminContractController::class, 'analyze'])->name('contracts.analyze');
    Route::post('/contracts/{contract}/deploy', [AdminContractController::class, 'deploy'])->name('contracts.deploy');
    Route::post('/contracts/{contract}/verify', [AdminContractController::class, 'verify'])->name('contracts.verify');
    Route::post('/contracts/{contract}/update-abi-on-node', [AdminContractController::class, 'updateAbiOnNode'])->name('contracts.update-abi-on-node');
    Route::post('/contracts/{contract}/state/storage', [AdminContractController::class, 'writeStorage'])->name('contracts.state.write-storage');
    Route::post('/contracts/{contract}/state/snapshot', [AdminContractController::class, 'saveState'])->name('contracts.state.save-snapshot');
    Route::post('/contracts/{contract}/call', [AdminContractController::class, 'contractCall'])->name('contracts.call');
    Route::post('/contracts/{contract}/send', [AdminContractController::class, 'contractSend'])->name('contracts.send');
    Route::patch('/contracts/{contract}/wallets', [AdminContractController::class, 'updateWallets'])->name('contracts.wallets');
    Route::post('/contracts/{contract}/wallets/remove', [AdminContractController::class, 'removeWallet'])->name('contracts.wallets.remove');
    Route::delete('/contracts/{contract}', [AdminContractController::class, 'destroy'])->name('contracts.destroy');
});

require __DIR__.'/auth.php';

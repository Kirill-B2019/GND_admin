<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Wallet;
use App\Services\GndAdminApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Кошельки: список из админского API GND, создание через POST /api/v1/wallet (X-API-Key),
 * редактирование имени/роли через PATCH /api/v1/admin/wallets/:address (X-Admin-Token).
 */
class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $roleFilter = $request->query('role');
        $error = null;
        $wallets = [];
        $accountByAddress = collect();
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::listWallets($roleFilter ?: null);
            $wallets = $result['wallets'];
            $error = $result['error'];
            $addresses = collect($wallets)->pluck('address')->filter()->unique()->values()->all();
            if (! empty($addresses)) {
                $accountByAddress = Wallet::whereIn('address', $addresses)->with('account')->get()->keyBy('address');
            }
        }
        return view('admin.wallets.index', [
            'wallets' => collect($wallets),
            'accountByAddress' => $accountByAddress,
            'gnd_error' => $error,
            'gnd_configured' => GndAdminApi::isConfigured(),
        ]);
    }

    public function create(): View
    {
        $nodeUrl = config('services.gnd.node_url');
        $apiKey = config('services.gnd.api_key');
        return view('admin.wallets.create', [
            'node_configured' => ! empty($nodeUrl) && ! empty($apiKey),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $nodeUrl = config('services.gnd.node_url');
        $apiKey = config('services.gnd.api_key');
        if (! $nodeUrl || ! $apiKey) {
            return redirect()->route('admin.wallets.create')
                ->withInput()
                ->withErrors(['gnd' => 'Настройте GND_NODE_URL и GND_API_KEY в .env для создания кошелька через ноду.']);
        }
        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(rtrim($nodeUrl, '/') . '/api/v1/wallet', []);

        if (! $response->successful()) {
            return redirect()->route('admin.wallets.create')
                ->withInput()
                ->withErrors(['gnd' => 'Ошибка ноды GND: ' . $response->body()]);
        }
        $data = $response->json('data', []);
        $address = $data['Address'] ?? $data['address'] ?? null;
        if (! $address) {
            return redirect()->route('admin.wallets.create')
                ->withInput()
                ->withErrors(['gnd' => 'Нода не вернула адрес кошелька.']);
        }
        return redirect()->route('admin.wallets.index')->with('status', 'Кошелёк создан.');
    }

    /**
     * Просмотр кошелька: адрес, имя/роль (если есть в списке), балансы по GET /api/v1/wallet/:address/balance.
     */
    public function show(string $address): View
    {
        $walletInfo = [
            'address' => $address,
            'name' => null,
            'role' => null,
            'signer_wallet_id' => null,
            'created_at' => null,
            'blocked' => false,
        ];
        if (GndAdminApi::isConfigured()) {
            $list = GndAdminApi::listWallets(null);
            foreach ($list['wallets'] ?? [] as $w) {
                $w = is_array($w) ? $w : (array) $w;
                if (($w['address'] ?? '') === $address) {
                    $walletInfo = array_merge($walletInfo, $w);
                    break;
                }
            }
        }
        $walletInfo = (object) $walletInfo;
        $balanceError = null;
        $balanceData = null;
        $nodeRequestUrls = [];
        $baseUrl = rtrim(config('services.gnd.node_url', ''), '/');
        if ($baseUrl !== '') {
            $nodeRequestUrls['balance'] = $baseUrl . '/api/v1/wallet/' . rawurlencode($address) . '/balance';
            $nodeRequestUrls['coin_balance_gnd'] = $baseUrl . '/api/v1/coin/GND/balance/' . rawurlencode($address);
            $nodeRequestUrls['coin_balance_gani'] = $baseUrl . '/api/v1/coin/GANI/balance/' . rawurlencode($address);
            $nodeRequestUrls['coin_supply_gnd'] = $baseUrl . '/api/v1/coin/GND/supply';
            $nodeRequestUrls['coin_supply_gani'] = $baseUrl . '/api/v1/coin/GANI/supply';
            $nodeRequestUrls['admin_wallets'] = $baseUrl . '/api/v1/admin/wallets';
            $result = GndAdminApi::getWalletBalance($address);
            $balanceError = $result['error'];
            $balanceData = $result['data'];
            // Не выводить нулевые балансы: оставляем только записи с balance > 0
            if (is_array($balanceData) && isset($balanceData['balances']) && is_array($balanceData['balances'])) {
                $balanceData['balances'] = array_values(array_filter($balanceData['balances'], function ($b) {
                    $bal = is_array($b) ? (string) ($b['balance'] ?? '0') : (string) ($b->balance ?? '0');
                    return preg_match('/[1-9]/', $bal) === 1;
                }));
            }
        }
        return view('admin.wallets.show', [
            'wallet' => $walletInfo,
            'balance_data' => $balanceData,
            'balance_error' => $balanceError,
            'node_request_urls' => $nodeRequestUrls,
        ]);
    }

    /**
     * Форма редактирования имени, роли и привязки к аккаунту.
     * Имя и роль подтягиваются из списка кошельков ноды (GET /api/v1/admin/wallets).
     */
    public function edit(string $address): View
    {
        $localWallet = Wallet::where('address', $address)->first();
        $accounts = Account::orderBy('name')->get();
        $currentName = null;
        $currentRole = null;
        if (GndAdminApi::isConfigured()) {
            $list = GndAdminApi::listWallets(null);
            foreach ($list['wallets'] ?? [] as $w) {
                $w = is_array($w) ? $w : (array) $w;
                if (($w['address'] ?? '') === $address) {
                    $currentName = $w['name'] ?? null;
                    $currentRole = $w['role'] ?? null;
                    break;
                }
            }
        }

        return view('admin.wallets.edit', [
            'address' => $address,
            'current_name' => $currentName,
            'current_role' => $currentRole,
            'current_account_id' => $localWallet?->account_id,
            'accounts' => $accounts,
            'gnd_configured' => GndAdminApi::isConfigured(),
        ]);
    }

    /**
     * PATCH /api/v1/admin/wallets/:address — обновить name и/или role на ноде.
     */
    public function update(Request $request, string $address): RedirectResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:64',
            'account_id' => 'nullable|integer|exists:accounts,id',
        ]);
        $name = $request->filled('name') ? $request->name : null;
        $role = $request->filled('role') ? $request->role : null;
        $accountId = $request->filled('account_id') ? (int) $request->account_id : null;
        if ($name !== null || $role !== null) {
            if (! GndAdminApi::isConfigured()) {
                return redirect()->route('admin.wallets.edit', ['address' => $address])
                    ->withInput()
                    ->withErrors(['gnd' => 'Задайте GND_ADMIN_SECRET в .env для изменения имени/роли на ноде.']);
            }
            $result = GndAdminApi::updateWallet($address, $name, $role);
            if ($result['error'] !== null) {
                return redirect()->route('admin.wallets.edit', ['address' => $address])
                    ->withInput()
                    ->withErrors(['gnd' => $result['error']]);
            }
        }
        // Привязка кошелька к аккаунту (локальная БД)
        Wallet::updateOrCreate(
            ['address' => $address],
            ['account_id' => $accountId]
        );
        return redirect()->route('admin.wallets.index')->with('status', 'Кошелёк обновлён.');
    }

    /**
     * POST /admin/wallets/:address/disable — блокировка подписания кошелька.
     */
    public function disable(string $address): RedirectResponse
    {
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.wallets.index')
                ->withErrors(['gnd' => 'Задайте GND_ADMIN_SECRET в .env']);
        }
        $result = GndAdminApi::disableWallet($address);
        if ($result['error'] !== null) {
            return redirect()->route('admin.wallets.index')
                ->withErrors(['gnd' => 'Блокировка: ' . $result['error']]);
        }
        return redirect()->route('admin.wallets.index')->with('status', 'Кошелёк заблокирован (подписание отключено).');
    }

    /**
     * POST /admin/wallets/:address/enable — снять блокировку подписания.
     */
    public function enable(string $address): RedirectResponse
    {
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.wallets.index')
                ->withErrors(['gnd' => 'Задайте GND_ADMIN_SECRET в .env']);
        }
        $result = GndAdminApi::enableWallet($address);
        if ($result['error'] !== null) {
            return redirect()->route('admin.wallets.index')
                ->withErrors(['gnd' => 'Разблокировка: ' . $result['error']]);
        }
        return redirect()->route('admin.wallets.index')->with('status', 'Кошелёк разблокирован.');
    }

    /**
     * DELETE /admin/wallets/:address — мягкое удаление кошелька (скрывается из списка).
     */
    public function destroy(string $address): RedirectResponse
    {
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.wallets.index')
                ->withErrors(['gnd' => 'Задайте GND_ADMIN_SECRET в .env']);
        }
        $result = GndAdminApi::deleteWallet($address);
        if ($result['error'] !== null) {
            return redirect()->route('admin.wallets.index')
                ->withErrors(['gnd' => 'Удаление: ' . $result['error']]);
        }
        return redirect()->route('admin.wallets.index')->with('status', 'Кошелёк удалён (мягкое удаление).');
    }
}

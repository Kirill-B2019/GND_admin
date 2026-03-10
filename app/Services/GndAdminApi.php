<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Клиент админского API ноды GND (X-Admin-Token).
 * Маршруты: /api/v1/admin/keys, /api/v1/admin/wallets, /api/v1/state/* (чтение/запись состояний контрактов).
 */
class GndAdminApi
{
    public static function baseUrl(): string
    {
        return rtrim(config('services.gnd.node_url', ''), '/');
    }

    public static function adminHeaders(): array
    {
        $secret = config('services.gnd.admin_secret');
        if (empty($secret)) {
            return [];
        }
        return [
            'X-Admin-Token' => $secret,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public static function isConfigured(): bool
    {
        return ! empty(self::baseUrl()) && ! empty(config('services.gnd.admin_secret'));
    }

    /**
     * GET /api/v1/admin/keys — список ключей (без поля key).
     */
    public static function listKeys(): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->get(self::baseUrl() . '/api/v1/admin/keys');
        if (! $r->successful()) {
            return ['keys' => [], 'error' => $r->body() ?: $r->reason()];
        }
        $data = $r->json('data', []);
        return ['keys' => $data['keys'] ?? [], 'error' => null];
    }

    /**
     * POST /api/v1/admin/keys — создать ключ. В ответе один раз возвращается data.key.
     */
    public static function createKey(string $name, array $permissions = [], ?string $expiresAt = null): array
    {
        $body = ['name' => $name, 'permissions' => $permissions];
        if ($expiresAt !== null && $expiresAt !== '') {
            $body['expires_at'] = $expiresAt;
        }
        $r = Http::withHeaders(self::adminHeaders())
            ->post(self::baseUrl() . '/api/v1/admin/keys', $body);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * POST /api/v1/admin/keys/:id/revoke — отозвать ключ.
     */
    public static function revokeKey(int $id): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->post(self::baseUrl() . '/api/v1/admin/keys/' . $id . '/revoke');
        if (! $r->successful()) {
            return ['ok' => false, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['ok' => true, 'error' => null];
    }

    /**
     * GET /api/v1/admin/wallets — список кошельков (name, role, address и т.д.).
     */
    public static function listWallets(?string $role = null): array
    {
        $url = self::baseUrl() . '/api/v1/admin/wallets';
        if ($role !== null && $role !== '') {
            $url .= '?role=' . rawurlencode($role);
        }
        $r = Http::withHeaders(self::adminHeaders())
            ->get($url);
        if (! $r->successful()) {
            return ['wallets' => [], 'metadata' => [], 'error' => $r->body() ?: $r->reason()];
        }
        $data = $r->json('data', []);
        $wallets = $data['wallets'] ?? [];
        // Исключаем private_key из ответа ноды (на случай устаревшего API)
        $wallets = array_map(function ($w) {
            if (is_array($w)) {
                unset($w['private_key'], $w['privateKey']);
            }
            return $w;
        }, $wallets);
        return ['wallets' => $wallets, 'metadata' => $data['metadata'] ?? [], 'error' => null];
    }

    /**
     * GET /api/v1/wallet/:address/balance — балансы кошелька: нативные монеты (GND, GANI) из native_balances
     * или token_balances (режим «всё на контрактах», config/native_contracts.json) + контрактные токены (публичный эндпоинт).
     */
    public static function getWalletBalance(string $address): array
    {
        $url = self::baseUrl() . '/api/v1/wallet/' . rawurlencode($address) . '/balance';
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data', []), 'error' => null];
    }

    /**
     * GET /api/v1/coin/:symbol/balance/:owner — баланс нативной монеты (GND или GANI) для адреса.
     */
    public static function getCoinBalance(string $symbol, string $owner): array
    {
        $url = self::baseUrl() . '/api/v1/coin/' . rawurlencode($symbol) . '/balance/' . rawurlencode($owner);
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data', []), 'error' => null];
    }

    /**
     * GET /api/v1/coin/:symbol/supply — total_supply и circulating_supply нативной монеты (GND, GANI).
     */
    public static function getCoinSupply(string $symbol): array
    {
        $url = self::baseUrl() . '/api/v1/coin/' . rawurlencode($symbol) . '/supply';
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data', []), 'error' => null];
    }

    /**
     * PATCH /api/v1/admin/wallets/:address — обновить name и/или role.
     */
    public static function updateWallet(string $address, ?string $name = null, ?string $role = null): array
    {
        $body = array_filter([
            'name' => $name,
            'role' => $role,
        ], fn ($v) => $v !== null);
        if (empty($body)) {
            return ['ok' => false, 'error' => 'Укажите name и/или role'];
        }
        $r = Http::withHeaders(self::adminHeaders())
            ->patch(self::baseUrl() . '/api/v1/admin/wallets/' . rawurlencode($address), $body);
        if (! $r->successful()) {
            return ['ok' => false, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['ok' => true, 'error' => null];
    }

    /**
     * POST /api/v1/admin/wallets/:address/disable — блокировка подписания (signer_wallets.disabled = true).
     */
    public static function disableWallet(string $address): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->post(self::baseUrl() . '/api/v1/admin/wallets/' . rawurlencode($address) . '/disable');
        if (! $r->successful()) {
            return ['ok' => false, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['ok' => true, 'error' => null];
    }

    /**
     * POST /api/v1/admin/wallets/:address/enable — снять блокировку подписания.
     */
    public static function enableWallet(string $address): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->post(self::baseUrl() . '/api/v1/admin/wallets/' . rawurlencode($address) . '/enable');
        if (! $r->successful()) {
            return ['ok' => false, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['ok' => true, 'error' => null];
    }

    /**
     * DELETE /api/v1/admin/wallets/:address — мягкое удаление (wallets.disabled = true).
     */
    public static function deleteWallet(string $address): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->delete(self::baseUrl() . '/api/v1/admin/wallets/' . rawurlencode($address));
        if (! $r->successful()) {
            return ['ok' => false, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['ok' => true, 'error' => null];
    }

    /**
     * POST /api/v1/contract/compile — компиляция исходного кода Solidity.
     */
    public static function compileContract(string $source, string $name = 'Contract', string $standard = 'GND-st1'): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->post(self::baseUrl() . '/api/v1/contract/compile', [
                'source' => $source,
                'name' => $name,
                'standard' => $standard,
            ]);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * POST /api/v1/contract/analyze — отчёт по безопасности исходного кода.
     */
    public static function analyzeContract(string $source): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->post(self::baseUrl() . '/api/v1/contract/analyze', ['source' => $source]);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * POST /api/v1/contract — деплой контракта на ноду.
     */
    public static function deployContract(array $params): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->post(self::baseUrl() . '/api/v1/contract', $params);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/contract/:address — информация о контракте с ноды.
     * В data нода возвращает контракт с полем ID (id в БД ноды).
     */
    public static function getContract(string $address): array
    {
        $r = Http::withHeaders(self::adminHeaders())
            ->get(self::baseUrl() . '/api/v1/contract/' . rawurlencode($address));
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * Возвращает id контракта на ноде по адресу (из ответа GET /api/v1/contract/:address).
     * Нода отдаёт поле "ID" (Go json). Возвращает null при ошибке или отсутствии id.
     */
    public static function getNodeContractIdByAddress(string $address): ?int
    {
        $result = self::getContract($address);
        if ($result['error'] !== null || empty($result['data'])) {
            return null;
        }
        $data = $result['data'];
        if (isset($data['ID']) && is_numeric($data['ID'])) {
            return (int) $data['ID'];
        }
        if (isset($data['id']) && is_numeric($data['id'])) {
            return (int) $data['id'];
        }

        return null;
    }

    /**
     * PATCH /api/v1/admin/contracts/:address/abi — обновить только ABI контракта на ноде (исправление abi = NULL в gnd_db).
     * $abi — массив ABI (как из abi_json).
     */
    public static function updateContractAbi(string $address, array $abi): array
    {
        $url = self::baseUrl() . '/api/v1/admin/contracts/' . rawurlencode($address) . '/abi';
        $r = Http::withHeaders(self::adminHeaders())
            ->patch($url, ['abi' => $abi]);
        if (! $r->successful()) {
            $err = $r->json('error', $r->body() ?: $r->reason());
            return ['success' => false, 'error' => is_string($err) ? $err : json_encode($err)];
        }
        return ['success' => true, 'error' => null];
    }

    /**
     * POST /api/v1/token/transfer — перевод токенов или нативных монет (GND/GANI).
     * Для нативной монеты: symbol = GND|GANI, token_address пустой. Для токена: token_address задан.
     */
    public static function tokenTransfer(string $from, string $to, ?string $tokenAddress, string $amount, ?string $symbol = null): array
    {
        $url = self::baseUrl() . '/api/v1/token/transfer';
        $body = [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ];
        if ($symbol !== null && $symbol !== '') {
            $body['symbol'] = $symbol;
        }
        if ($tokenAddress !== null && $tokenAddress !== '') {
            $body['token_address'] = $tokenAddress;
        } else {
            $body['token_address'] = '';
        }
        $r = Http::withHeaders(array_merge(self::adminHeaders(), ['Content-Type' => 'application/json']))
            ->post($url, $body);
        if (! $r->successful()) {
            $err = $r->json('error', $r->body() ?: $r->reason());
            return ['success' => false, 'data' => null, 'error' => is_string($err) ? $err : json_encode($err)];
        }
        return ['success' => true, 'data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/metrics — метрики ноды: блоки, транзакции (total_blocks, total_transactions и др.).
     */
    public static function getMetrics(): array
    {
        $r = Http::withHeaders(['Accept' => 'application/json'])
            ->get(self::baseUrl() . '/api/v1/metrics');
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data', []), 'error' => null];
    }

    /**
     * GET /api/v1/block/latest — последний блок (height, tx_count и др.).
     */
    public static function getBlockLatest(): array
    {
        $r = Http::withHeaders(['Accept' => 'application/json'])
            ->get(self::baseUrl() . '/api/v1/block/latest');
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/transactions — список ожидающих транзакций (мемпул).
     */
    public static function getMempool(): array
    {
        $r = Http::withHeaders(['Accept' => 'application/json'])
            ->get(self::baseUrl() . '/api/v1/transactions');
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data', []), 'error' => null];
    }

    /**
     * GET /api/v1/transactions/list — список транзакций из gnd_db.transactions (для страницы /admin/transactions).
     */
    public static function getTransactionsListFromNode(int $limit = 50, int $offset = 0): array
    {
        $url = self::baseUrl() . '/api/v1/transactions/list?limit=' . $limit . '&offset=' . $offset;
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['list' => [], 'total' => 0, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        $data = $r->json('data', []);
        return [
            'list' => $data['list'] ?? [],
            'total' => (int) ($data['total'] ?? 0),
            'error' => null,
        ];
    }

    /**
     * GET /api/v1/transaction/:hash — информация о транзакции по хешу.
     */
    public static function getTransaction(string $hash): array
    {
        $r = Http::withHeaders(['Accept' => 'application/json'])
            ->get(self::baseUrl() . '/api/v1/transaction/' . rawurlencode($hash));
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * POST /api/v1/token/deploy — выпуск токена на ноде (X-API-Key).
     * owner необязателен: пусто — нода создаёт кошелёк и назначает владельцем (регистрация в один клик),
     * либо при наличии deployWallet токен считается системным (без владельца).
     */
    public static function deployToken(string $name, string $symbol, int $decimals, string $totalSupply, ?string $owner = null, ?string $deployWallet = null): array
    {
        $apiKey = config('services.gnd.api_key');
        $url = self::baseUrl() . '/api/v1/token/deploy';
        $body = [
            'name' => $name,
            'symbol' => $symbol,
            'decimals' => $decimals,
            'total_supply' => $totalSupply,
        ];
        if ($owner !== null && trim($owner) !== '') {
            $body['owner'] = trim($owner);
        }
        if ($deployWallet !== null && trim($deployWallet) !== '') {
            $body['deploy_wallet'] = trim($deployWallet);
        }
        $headers = array_merge(
            ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            $apiKey ? ['X-API-Key' => $apiKey] : []
        );
        $r = Http::withHeaders($headers)->post($url, $body);
        if (! $r->successful()) {
            $err = $r->json('error', $r->body() ?: $r->reason());
            return ['data' => null, 'error' => is_string($err) ? $err : json_encode($err)];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/state/account/:address — текущее состояние аккаунта из accounts.
     */
    public static function getAccountStateCurrent(string $address): array
    {
        $url = self::baseUrl() . '/api/v1/state/account/' . rawurlencode($address);
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/contract/:address/view — просмотр контракта: ABI, список view/write функций, базовая инфо (name, symbol, decimals, totalSupply).
     */
    public static function getContractView(string $contractAddress): array
    {
        $url = self::baseUrl() . '/api/v1/contract/' . rawurlencode($contractAddress) . '/view';
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/contract/:address/state?addresses=addr1,addr2 — состояние контракта (функции: name, symbol, total_supply, balances).
     */
    public static function getContractState(string $contractAddress, array $accountAddresses = []): array
    {
        $url = self::baseUrl() . '/api/v1/contract/' . rawurlencode($contractAddress) . '/state';
        if (! empty($accountAddresses)) {
            $url .= '?addresses=' . rawurlencode(implode(',', $accountAddresses));
        }
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * POST /api/v1/admin/contracts/by-id/:id/call — вызов view/constant метода (без транзакции). body: data (hex), from?.
     */
    public static function contractCall(int $contractId, string $data, ?string $from = null): array
    {
        $url = self::baseUrl() . '/api/v1/admin/contracts/by-id/' . $contractId . '/call';
        $body = ['data' => $data];
        if ($from !== null && $from !== '') {
            $body['from'] = $from;
        }
        $r = Http::withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])
            ->post($url, $body);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * POST /api/v1/admin/contracts/by-id/:id/send — отправка транзакции вызова метода. body: from, data, value?, gas_limit?.
     * X-Admin-Token обязателен; при пустой подписи нода подпишет транзакцию за кошелёк с signer_wallet_id.
     */
    public static function contractSend(int $contractId, string $from, string $data, string $value = '0', int $gasLimit = 200000): array
    {
        $url = self::baseUrl() . '/api/v1/admin/contracts/by-id/' . $contractId . '/send';
        $r = Http::withHeaders(self::adminHeaders())
            ->post($url, [
                'from' => $from,
                'data' => $data,
                'value' => $value,
                'gas_limit' => $gasLimit,
            ]);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/state/account/:address/block/:blockId — снимок состояния аккаунта на блок из account_states.
     */
    public static function getAccountStateAtBlock(string $address, int $blockId): array
    {
        $url = self::baseUrl() . '/api/v1/state/account/' . rawurlencode($address) . '/block/' . $blockId;
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data'), 'error' => null];
    }

    /**
     * GET /api/v1/state/contract/:address/storage?block_id= — слоты storage контракта на блок.
     */
    public static function getContractStorage(string $address, int $blockId): array
    {
        $url = self::baseUrl() . '/api/v1/state/contract/' . rawurlencode($address) . '/storage?block_id=' . $blockId;
        $r = Http::withHeaders(['Accept' => 'application/json'])->get($url);
        if (! $r->successful()) {
            return ['data' => null, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['data' => $r->json('data', []), 'error' => null];
    }

    /**
     * POST /api/v1/admin/state/contract/:address/storage — запись слота storage контракта (X-Admin-Token).
     * body: block_id (int), slot_key (hex), slot_value (hex), по 32 байта.
     */
    public static function writeContractStorageSlot(string $address, int $blockId, string $slotKey, string $slotValue): array
    {
        $url = self::baseUrl() . '/api/v1/admin/state/contract/' . rawurlencode($address) . '/storage';
        $r = Http::withHeaders(self::adminHeaders())
            ->post($url, [
                'block_id' => $blockId,
                'slot_key' => $slotKey,
                'slot_value' => $slotValue,
            ]);
        if (! $r->successful()) {
            return ['ok' => false, 'error' => $r->json('error', $r->body() ?: $r->reason())];
        }
        return ['ok' => true, 'error' => null];
    }
}

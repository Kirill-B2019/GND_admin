<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ApiController extends Controller
{
    /**
     * Страница со всеми вариантами API ноды ГАНИМЕД: REST, WebSocket, RPC.
     */
    public function index(): View
    {
        $baseUrl = rtrim((string) Config::get('services.gnd.node_url', 'http://localhost:8182'), '/');
        $restBase = $baseUrl . '/api/v1';

        $wsHost = parse_url($baseUrl, PHP_URL_HOST) ?: 'localhost';
        $wsScheme = (parse_url($baseUrl, PHP_URL_SCHEME) === 'https') ? 'wss' : 'ws';
        $wsPort = Config::get('services.gnd.ws_port', '8183');
        $wsUrl = $wsScheme . '://' . $wsHost . ($wsPort ? ':' . $wsPort : '') . '/ws';

        $rpcPort = Config::get('services.gnd.rpc_port', '8181');
        $rpcUrl = (parse_url($baseUrl, PHP_URL_SCHEME) ?: 'http') . '://' . $wsHost . ($rpcPort ? ':' . $rpcPort : '');

        $restEndpoints = [
            ['method' => 'GET', 'path' => '/health', 'desc' => 'Проверка доступности ноды'],
            ['method' => 'GET', 'path' => '/metrics', 'desc' => 'Метрики блокчейна (блоки, транзакции)'],
            ['method' => 'GET', 'path' => '/metrics/transactions', 'desc' => 'Метрики транзакций'],
            ['method' => 'GET', 'path' => '/metrics/fees', 'desc' => 'Метрики комиссий'],
            ['method' => 'GET', 'path' => '/fees', 'desc' => 'Комиссии (алиас)'],
            ['method' => 'GET', 'path' => '/alerts', 'desc' => 'История алертов'],
            ['method' => 'POST', 'path' => '/alerts/thresholds', 'desc' => 'Установка порогов алертов'],
            ['method' => 'POST', 'path' => '/wallet', 'desc' => 'Создание кошелька (X-API-Key)', 'auth' => true],
            ['method' => 'GET', 'path' => '/wallet/:address/balance', 'desc' => 'Балансы кошелька (нативные GND/GANI + токены)'],
            ['method' => 'GET', 'path' => '/coin/:symbol/balance/:owner', 'desc' => 'Баланс нативной монеты (GND/GANI)'],
            ['method' => 'GET', 'path' => '/coin/:symbol/supply', 'desc' => 'total_supply и circulating_supply нативной монеты'],
            ['method' => 'POST', 'path' => '/token/transfer', 'desc' => 'Перевод токена или нативной монеты (GND/GANI)'],
            ['method' => 'POST', 'path' => '/token/approve', 'desc' => 'Approve для токена'],
            ['method' => 'POST', 'path' => '/token/deploy', 'desc' => 'Выпуск токена (X-API-Key)', 'auth' => true],
            ['method' => 'POST', 'path' => '/token/logo/upload', 'desc' => 'Загрузка логотипа токена'],
            ['method' => 'PATCH', 'path' => '/token/logo', 'desc' => 'Установка логотипа токена'],
            ['method' => 'GET', 'path' => '/token/:address/balance/:owner', 'desc' => 'Баланс контрактного токена по адресу'],
            ['method' => 'POST', 'path' => '/transaction', 'desc' => 'Отправка транзакции'],
            ['method' => 'GET', 'path' => '/transaction/:hash', 'desc' => 'Получение транзакции по хешу'],
            ['method' => 'GET', 'path' => '/transactions', 'desc' => 'Список ожидающих транзакций (мемпул)'],
            ['method' => 'GET', 'path' => '/mempool', 'desc' => 'Мемпул (ожидающие транзакции)'],
            ['method' => 'GET', 'path' => '/block/latest', 'desc' => 'Последний блок'],
            ['method' => 'GET', 'path' => '/block/:number', 'desc' => 'Блок по номеру'],
            ['method' => 'POST', 'path' => '/contract', 'desc' => 'Деплой контракта'],
            ['method' => 'POST', 'path' => '/contract/compile', 'desc' => 'Компиляция контракта'],
            ['method' => 'POST', 'path' => '/contract/analyze', 'desc' => 'Анализ контракта'],
            ['method' => 'GET', 'path' => '/contract/:address', 'desc' => 'Информация о контракте'],
            ['method' => 'GET', 'path' => '/contract/:address/state', 'desc' => 'Состояние контракта (name, symbol, total_supply, balances; ?addresses=)'],
            ['method' => 'GET', 'path' => '/contract/:address/view', 'desc' => 'Просмотр контракта: ABI, view/write функции, исходный код Solidity'],
            ['method' => 'POST', 'path' => '/admin/contracts/by-id/:id/call', 'desc' => 'Вызов view/constant метода контракта (без транзакции). Body: data (hex), from?'],
            ['method' => 'POST', 'path' => '/admin/contracts/by-id/:id/send', 'desc' => 'Отправка транзакции вызова метода контракта. Body: from, data, value?, gas_limit?'],
            ['method' => 'GET', 'path' => '/state/account/:address', 'desc' => 'Текущее состояние аккаунта (nonce, balance_gnd)'],
            ['method' => 'GET', 'path' => '/state/account/:address/block/:blockId', 'desc' => 'Снимок состояния аккаунта на блок'],
            ['method' => 'GET', 'path' => '/state/contract/:address/storage', 'desc' => 'Слоты storage контракта (query: block_id)'],
        ];

        $wsSubscriptions = [
            ['channel' => 'blocks', 'desc' => 'Новые блоки'],
            ['channel' => 'transactions', 'desc' => 'Новые транзакции'],
            ['channel' => 'events', 'desc' => 'События контрактов'],
        ];

        $rpcEndpoints = [
            ['method' => 'GET', 'path' => '/block/latest', 'desc' => 'Последний блок'],
            ['method' => 'GET', 'path' => '/block/by-number?number=N', 'desc' => 'Блок по номеру'],
            ['method' => 'POST', 'path' => '/contract/deploy', 'desc' => 'Деплой контракта'],
            ['method' => 'POST', 'path' => '/contract/call', 'desc' => 'Вызов метода контракта'],
            ['method' => 'POST', 'path' => '/contract/send', 'desc' => 'Отправка транзакции в контракт'],
            ['method' => 'GET', 'path' => '/account/balance?address=...', 'desc' => 'Баланс аккаунта'],
        ];

        return view('admin.api.index', [
            'restBase' => $restBase,
            'baseUrl' => $baseUrl,
            'wsUrl' => $wsUrl,
            'rpcUrl' => $rpcUrl,
            'restEndpoints' => $restEndpoints,
            'wsSubscriptions' => $wsSubscriptions,
            'rpcEndpoints' => $rpcEndpoints,
            'apiKeyConfigured' => ! empty(Config::get('services.gnd.api_key')),
        ]);
    }

    /**
     * Прокси-запрос к REST API ноды. Для эндпоинтов с auth подставляется X-API-Key из конфига.
     */
    public function sendRequest(Request $request): JsonResponse
    {
        $method = strtoupper((string) $request->input('method', 'GET'));
        $path = trim((string) $request->input('path', ''));
        $path = ltrim($path, '/');
        if ($path === '') {
            return response()->json(['success' => false, 'error' => 'Путь не указан', 'statusCode' => 0]);
        }

        $baseUrl = rtrim((string) Config::get('services.gnd.node_url', 'http://localhost:8182'), '/');
        $url = $baseUrl . '/api/v1/' . $path;

        $headers = ['Accept' => 'application/json'];
        $needsApiKey = ($method === 'POST' && $path === 'wallet') || ($method === 'POST' && $path === 'token/deploy');
        $apiKey = Config::get('services.gnd.api_key');
        if ($needsApiKey && $apiKey) {
            $headers['X-API-Key'] = $apiKey;
        }

        try {
            if ($method === 'GET') {
                $response = Http::withHeaders($headers)->timeout(15)->get($url);
            } elseif ($method === 'POST') {
                $body = $request->input('body');
                $response = Http::withHeaders($headers)->timeout(15)->post($url, is_array($body) ? $body : []);
            } elseif ($method === 'PATCH') {
                $body = $request->input('body');
                $response = Http::withHeaders($headers)->timeout(15)->patch($url, is_array($body) ? $body : []);
            } else {
                return response()->json(['success' => false, 'error' => 'Метод не поддерживается', 'statusCode' => 0]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'statusCode' => 0,
                'body' => null,
            ]);
        }

        $statusCode = $response->status();
        $body = $response->json();
        if ($body === null) {
            $body = $response->body();
        }

        return response()->json([
            'success' => $response->successful(),
            'statusCode' => $statusCode,
            'body' => $body,
            'usedApiKey' => $needsApiKey && ! empty($apiKey),
        ]);
    }
}

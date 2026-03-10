<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Token;
use App\Models\Transaction;
use App\Services\GndAdminApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD транзакций: перевод с кошелька на кошелёк.
 * Обязательные поля: кошельки (поиск по имени или адресу), токен или монета, сумма. Nonce опционален. Подтверждение перед отправкой — на фронте (SweetAlert2).
 */
class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::orderByDesc('created_at')->paginate(20);
        $mempool = null;
        $mempoolError = null;
        $nodeList = [];
        $nodeTotal = 0;
        $nodeError = null;
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::getMempool();
            $mempool = $result['data'];
            $mempoolError = $result['error'];
            $listResult = GndAdminApi::getTransactionsListFromNode(100, 0);
            $nodeList = $listResult['list'];
            $nodeTotal = $listResult['total'];
            $nodeError = $listResult['error'];
        }
        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'mempool' => $mempool,
            'mempool_error' => $mempoolError,
            'node_list' => $nodeList,
            'node_total' => $nodeTotal,
            'node_error' => $nodeError,
        ]);
    }

    public function create(): View
    {
        $wallets = [];
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::listWallets(null);
            $wallets = $result['wallets'] ?? [];
        }
        $tokens = Token::orderBy('symbol')->get(['id', 'token_address', 'symbol', 'name']);
        $coins = [
            ['symbol' => 'GND', 'name' => 'GND — Ganymede Coin (L1)'],
            ['symbol' => 'GANI', 'name' => 'GANI — Ganymede Governance (L1)'],
        ];
        return view('admin.transactions.create', [
            'wallets' => $wallets,
            'tokens' => $tokens,
            'coins' => $coins,
            'gnd_configured' => GndAdminApi::isConfigured(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'from_address' => 'required|string|max:128',
            'to_address' => 'required|string|max:128',
            'asset_type' => 'required|in:coin,token',
            'coin_symbol' => 'nullable|string|max:32|required_if:asset_type,coin',
            'token_address' => 'nullable|string|max:128|required_if:asset_type,token',
            'amount' => 'required|string|max:78',
            'nonce' => 'nullable|integer|min:0',
        ]);

        $from = trim($request->input('from_address'));
        $to = trim($request->input('to_address'));
        $amount = trim($request->input('amount'));
        $assetType = $request->input('asset_type');
        $coinSymbol = $request->filled('coin_symbol') ? trim($request->input('coin_symbol')) : null;
        $tokenAddress = $request->filled('token_address') ? trim($request->input('token_address')) : null;
        $nonce = $request->filled('nonce') ? (int) $request->input('nonce') : null;

        if ($from === $to) {
            return redirect()->route('admin.transactions.create')
                ->withInput()
                ->withErrors(['to_address' => 'Адрес получателя должен отличаться от отправителя.']);
        }

        if (! preg_match('/^\d+$/', $amount) || $amount === '0') {
            return redirect()->route('admin.transactions.create')
                ->withInput()
                ->withErrors(['amount' => 'Сумма должна быть положительным целым числом (в wei/smallest unit).']);
        }

        $record = Transaction::create([
            'from_address' => $from,
            'to_address' => $to,
            'token_address' => $assetType === 'token' ? $tokenAddress : null,
            'coin_symbol' => $assetType === 'coin' ? $coinSymbol : null,
            'amount' => $amount,
            'nonce' => $nonce,
            'status' => 'pending',
        ]);

        if (! GndAdminApi::isConfigured()) {
            $record->update(['status' => 'failed', 'error_message' => 'GND нода не настроена (GND_NODE_URL, GND_ADMIN_SECRET).']);
            return redirect()->route('admin.transactions.index')
                ->withErrors(['gnd' => 'Нода не настроена. Транзакция сохранена со статусом «ошибка».']);
        }

        $result = $assetType === 'coin'
            ? GndAdminApi::tokenTransfer($from, $to, null, $amount, $coinSymbol)
            : GndAdminApi::tokenTransfer($from, $to, $tokenAddress, $amount, null);
        if ($result['error'] !== null) {
            $record->update(['status' => 'failed', 'error_message' => $result['error']]);
            return redirect()->route('admin.transactions.index')
                ->with('error', 'Ошибка ноды: ' . $result['error']);
        }

        $record->update(['status' => 'sent']);
        return redirect()->route('admin.transactions.index')
            ->with('status', 'Перевод выполнен успешно.');
    }

    public function show(int $id): View
    {
        $transaction = Transaction::findOrFail($id);
        $txFromNode = null;
        $txError = null;
        if ($transaction->tx_hash && GndAdminApi::isConfigured()) {
            $result = GndAdminApi::getTransaction($transaction->tx_hash);
            $txFromNode = $result['data'];
            $txError = $result['error'];
        }
        return view('admin.transactions.show', [
            'transaction' => $transaction,
            'tx_from_node' => $txFromNode,
            'tx_error' => $txError,
        ]);
    }
}

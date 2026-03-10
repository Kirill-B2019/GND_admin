<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\FormatHelper;
use App\Models\Contract;
use App\Models\Token;
use App\Models\Transaction;
use App\Services\GndAdminApi;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $usersCount = User::count();
        $adminsCount = User::where('role', User::ROLE_ADMIN)->count();
        $walletsCount = 0;
        $blocksCount = null;
        $transactionsCount = null;
        $latestBlockTxCount = null;
        $totalSupplyFormatted = null;
        $circulatingSupplyFormatted = null;
        $totalSupplyGaniFormatted = null;
        $circulatingSupplyGaniFormatted = null;

        if (GndAdminApi::isConfigured()) {
            $list = GndAdminApi::listWallets(null);
            $walletsCount = isset($list['error']) ? 0 : count($list['wallets'] ?? []);

            $metrics = GndAdminApi::getMetrics();
            if (empty($metrics['error']) && ! empty($metrics['data'])) {
                $data = $metrics['data'];
                $blocksCount = $data['BlockMetrics']['TotalBlocks'] ?? $data['block_metrics']['total_blocks'] ?? null;
                $transactionsCount = $data['TransactionMetrics']['TotalTransactions'] ?? $data['transaction_metrics']['total_transactions'] ?? null;
            }
            $block = GndAdminApi::getBlockLatest();
            if (empty($block['error']) && ! empty($block['data'])) {
                $data = $block['data'];
                if ($blocksCount === null) {
                    $height = $data['Height'] ?? $data['height'] ?? null;
                    if ($height !== null) {
                        $blocksCount = $height + 1;
                    }
                }
                $txs = $data['Transactions'] ?? $data['transactions'] ?? [];
                $latestBlockTxCount = is_array($txs) ? count($txs) : 0;
            }

            $supplyGnd = GndAdminApi::getCoinSupply('GND');
            if (empty($supplyGnd['error']) && ! empty($supplyGnd['data'])) {
                $d = $supplyGnd['data'];
                $decimals = (int) ($d['decimals'] ?? 18);
                $totalSupplyFormatted = FormatHelper::balanceWithDecimals((string) ($d['total_supply'] ?? '0'), $decimals);
                $circulatingSupplyFormatted = FormatHelper::balanceWithDecimals((string) ($d['circulating_supply'] ?? '0'), $decimals);
            }
            $supplyGani = GndAdminApi::getCoinSupply('GANI');
            if (empty($supplyGani['error']) && ! empty($supplyGani['data'])) {
                $d = $supplyGani['data'];
                $decimals = (int) ($d['decimals'] ?? 6);
                $totalSupplyGaniFormatted = FormatHelper::balanceWithDecimals((string) ($d['total_supply'] ?? '0'), $decimals);
                $circulatingSupplyGaniFormatted = FormatHelper::balanceWithDecimals((string) ($d['circulating_supply'] ?? '0'), $decimals);
            }
        }

        if ($totalSupplyFormatted === null || $circulatingSupplyFormatted === null) {
            $genesis = Config::get('services.gnd.genesis', []);
            $genesisDecimals = (int) ($genesis['decimals'] ?? 18);
            $genesisTotalSupply = (string) ($genesis['total_supply'] ?? '0');
            $genesisCirculatingSupply = (string) ($genesis['circulating_supply'] ?? '0');
            $totalSupplyFormatted = $totalSupplyFormatted ?? FormatHelper::balanceWithDecimals($genesisTotalSupply, $genesisDecimals);
            $circulatingSupplyFormatted = $circulatingSupplyFormatted ?? FormatHelper::balanceWithDecimals($genesisCirculatingSupply, $genesisDecimals);
        }

        $tokensTotal = Token::count();
        $tokensViaContracts = Token::whereNotNull('token_address')->where('token_address', '!=', '')->count();
        $contractsCount = Contract::count();
        $contractsPercent = $tokensTotal > 0 ? round($tokensViaContracts / $tokensTotal * 100, 1) : 0;

        $transactionsTotalLocal = Transaction::count();

        return view('admin.dashboard', [
            'usersCount' => $usersCount,
            'adminsCount' => $adminsCount,
            'walletsCount' => $walletsCount,
            'genesisTotalSupplyFormatted' => $totalSupplyFormatted,
            'genesisCirculatingSupplyFormatted' => $circulatingSupplyFormatted,
            'totalSupplyGaniFormatted' => $totalSupplyGaniFormatted,
            'circulatingSupplyGaniFormatted' => $circulatingSupplyGaniFormatted,
            'blocksCount' => $blocksCount,
            'transactionsCount' => $transactionsCount ?? $transactionsTotalLocal,
            'latestBlockTxCount' => $latestBlockTxCount,
            'transactionsTotalLocal' => $transactionsTotalLocal,
            'tokensTotal' => $tokensTotal,
            'tokensViaContracts' => $tokensViaContracts,
            'contractsCount' => $contractsCount,
            'contractsPercent' => $contractsPercent,
            'gndConfigured' => GndAdminApi::isConfigured(),
        ]);
    }
}

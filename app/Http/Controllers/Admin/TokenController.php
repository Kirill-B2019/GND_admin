<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Token;
use App\Services\GndAdminApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * CRUD токенов: расширенная информация, логотипы (файл или URL), периодическое обновление.
 * Деплой токена на ноду в один клик (owner необязателен — создаётся кошелёк).
 */
class TokenController extends Controller
{
    private const LOGO_DISK = 'public';
    private const LOGO_DIR = 'token_logos';

    public function deployForm(): View
    {
        $wallets = [];
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::listWallets(null);
            $wallets = $result['wallets'] ?? [];
        }
        return view('admin.tokens.deploy', compact('wallets'));
    }

    public function deploySubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:32',
            'decimals' => 'nullable|integer|min:0|max:255',
            'total_supply' => 'required|string|max:78',
            'owner' => 'nullable|string|max:255',
            'deploy_wallet' => 'nullable|string|max:255',
        ]);
        $decimals = (int) ($request->input('decimals', 18));
        $owner = $request->filled('owner') ? trim($request->input('owner')) : null;
        $deployWallet = $request->filled('deploy_wallet') ? trim($request->input('deploy_wallet')) : null;
        $result = GndAdminApi::deployToken(
            $request->input('name'),
            $request->input('symbol'),
            $decimals,
            $request->input('total_supply'),
            $owner !== '' ? $owner : null,
            $deployWallet !== '' ? $deployWallet : null
        );
        if ($result['error'] !== null) {
            return redirect()->route('admin.tokens.deploy')
                ->withInput()
                ->with('error', $result['error']);
        }
        return redirect()->route('admin.tokens.deploy')
            ->with('deploy_result', $result['data'])
            ->with('status', 'Токен успешно выпущен на ноде.');
    }

    public function index(): View
    {
        $tokens = Token::orderBy('sort_order')->orderBy('symbol')->paginate(15);
        return view('admin.tokens.index', compact('tokens'));
    }

    public function create(): View
    {
        $contracts = Contract::whereNotNull('address')->where('address', '!=', '')->orderBy('name')->get(['id', 'name', 'address']);
        $wallets = [];
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::listWallets(null);
            $wallets = $result['wallets'] ?? [];
        }
        $gndselfAddress = (string) config('services.gnd.gndself_address', '');

        return view('admin.tokens.create', compact('contracts', 'wallets', 'gndselfAddress'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token_address' => 'required|string|max:255|unique:tokens,token_address',
            'symbol' => 'required|string|max:32',
            'name' => 'required|string|max:255',
            'decimals' => 'nullable|integer|min:0|max:255',
            'standard' => 'nullable|string|max:32',
            'total_supply' => 'nullable|string|max:78',
            'description' => 'nullable|string|max:5000',
            'logo_file' => 'nullable|image|max:2048',
            'logo_url' => 'nullable|url|max:512',
            'metadata_extra' => 'nullable|string|max:10000',
            'is_verified' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'ship_to_wallet' => 'nullable|string|max:255',
            'is_system' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'token_address', 'symbol', 'name', 'decimals', 'standard', 'total_supply', 'description', 'is_verified', 'sort_order',
        ]);
        $data['decimals'] = (int) ($data['decimals'] ?? 18);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_verified'] = $request->boolean('is_verified');

        $gndselfAddress = (string) config('services.gnd.gndself_address', '');
        if ($request->boolean('is_system') && $gndselfAddress !== '') {
            $data['owner_address'] = $gndselfAddress;
        } else {
            $shipTo = $request->filled('ship_to_wallet') ? trim($request->input('ship_to_wallet')) : null;
            $data['owner_address'] = $shipTo !== '' ? $shipTo : null;
        }

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store(self::LOGO_DIR, self::LOGO_DISK);
            $data['logo_path'] = $path;
            $data['logo_url'] = null;
        } elseif ($request->filled('logo_url')) {
            $data['logo_url'] = $request->logo_url;
            $data['logo_path'] = null;
        }

        $extra = $request->input('metadata_extra');
        if (is_string($extra) && $extra !== '') {
            $decoded = json_decode($extra, true);
            $data['metadata_json'] = is_array($decoded) ? $decoded : null;
        }

        Token::create($data);

        return redirect()->route('admin.tokens.index')->with('status', 'Токен создан.');
    }

    public function show(Token $token): View
    {
        return view('admin.tokens.show', compact('token'));
    }

    public function edit(Token $token): View
    {
        return view('admin.tokens.edit', compact('token'));
    }

    public function update(Request $request, Token $token): RedirectResponse
    {
        $request->validate([
            'token_address' => 'required|string|max:255|unique:tokens,token_address,' . $token->id,
            'symbol' => 'required|string|max:32',
            'name' => 'required|string|max:255',
            'decimals' => 'nullable|integer|min:0|max:255',
            'standard' => 'nullable|string|max:32',
            'total_supply' => 'nullable|string|max:78',
            'description' => 'nullable|string|max:5000',
            'logo_file' => 'nullable|image|max:2048',
            'logo_url' => 'nullable|url|max:512',
            'metadata_extra' => 'nullable|string|max:10000',
            'is_verified' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'remove_logo' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'token_address', 'symbol', 'name', 'decimals', 'standard', 'total_supply', 'description', 'is_verified', 'sort_order',
        ]);
        $data['decimals'] = (int) ($data['decimals'] ?? 18);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_verified'] = $request->boolean('is_verified');

        if ($request->boolean('remove_logo')) {
            if ($token->logo_path) {
                Storage::disk(self::LOGO_DISK)->delete($token->logo_path);
            }
            $data['logo_path'] = null;
            $data['logo_url'] = null;
        } elseif ($request->hasFile('logo_file')) {
            if ($token->logo_path) {
                Storage::disk(self::LOGO_DISK)->delete($token->logo_path);
            }
            $path = $request->file('logo_file')->store(self::LOGO_DIR, self::LOGO_DISK);
            $data['logo_path'] = $path;
            $data['logo_url'] = null;
        } elseif ($request->filled('logo_url')) {
            if ($token->logo_path) {
                Storage::disk(self::LOGO_DISK)->delete($token->logo_path);
            }
            $data['logo_path'] = null;
            $data['logo_url'] = $request->logo_url;
        }

        $extra = $request->input('metadata_extra');
        if (is_string($extra) && $extra !== '') {
            $decoded = json_decode($extra, true);
            $data['metadata_json'] = is_array($decoded) ? $decoded : null;
        } else {
            $data['metadata_json'] = null;
        }

        $token->update($data);
        return redirect()->route('admin.tokens.show', $token)->with('status', 'Токен обновлён.');
    }

    public function destroy(Token $token): RedirectResponse
    {
        if ($token->logo_path) {
            Storage::disk(self::LOGO_DISK)->delete($token->logo_path);
        }
        $token->delete();
        return redirect()->route('admin.tokens.index')->with('status', 'Токен удалён.');
    }
}

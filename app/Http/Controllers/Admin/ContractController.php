<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractDeployRequest;
use App\Helpers\FormatHelper;
use App\Models\Contract;
use App\Services\GndAdminApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD контрактов: загрузка кода, компиляция, отчёт по логике, деплой, привязка кошельков.
 */
class ContractController extends Controller
{
    public function index(): View
    {
        $contracts = Contract::withCount('managedWallets')->orderBy('name')->paginate(15);
        return view('admin.contracts.index', compact('contracts'));
    }

    /**
     * Синхронизация контрактов с нодой: для каждого контракта с адресом запрашивает id на ноде и сохраняет node_contract_id.
     */
    public function syncFromNode(Request $request): RedirectResponse
    {
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.contracts.index')
                ->withErrors(['sync' => 'Нода GND не настроена (GND_API_URL).']);
        }
        $contracts = Contract::whereNotNull('address')->where('address', '!=', '')->get();
        $updated = 0;
        $errors = [];
        foreach ($contracts as $contract) {
            $nodeId = GndAdminApi::getNodeContractIdByAddress($contract->address);
            if ($nodeId !== null) {
                $contract->update(['node_contract_id' => $nodeId]);
                $updated++;
            } else {
                $errors[] = $contract->name . ' (' . $contract->address . '): контракт не найден на ноде.';
            }
        }
        if ($updated > 0) {
            $msg = "Синхронизировано с нодой: обновлено id у {$updated} контрактов.";
            if (count($errors) > 0) {
                $msg .= ' Не найдены на ноде: ' . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $msg .= ' … (всего ' . count($errors) . ')';
                }
            }
            return redirect()->route('admin.contracts.index')->with('status', $msg);
        }
        if (count($errors) > 0) {
            return redirect()->route('admin.contracts.index')->withErrors(['sync' => implode(' ', array_slice($errors, 0, 3))]);
        }
        return redirect()->route('admin.contracts.index')->with('status', 'Нет контрактов с адресом для синхронизации.');
    }

    /**
     * Массово отправить локальный ABI на ноду для всех контрактов с адресом и abi_json (исправить abi = NULL в gnd_db).
     */
    public function syncAbiToNode(Request $request): RedirectResponse
    {
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.contracts.index')
                ->withErrors(['abi' => 'Нода GND не настроена.']);
        }
        $contracts = Contract::whereNotNull('address')->where('address', '!=', '')->whereNotNull('abi_json')->get();
        $updated = 0;
        $errors = [];
        foreach ($contracts as $contract) {
            $normalized = $this->normalizeAbiFromContract($contract->abi_json);
            $abi = $normalized['abi'] ?? [];
            if ($normalized['error'] !== null || empty($abi)) {
                if ($normalized['error'] !== null) {
                    $errors[] = $contract->name . ': ' . $normalized['error'];
                }
                continue;
            }
            $result = GndAdminApi::updateContractAbi($contract->address, $abi);
            if ($result['success'] ?? false) {
                $updated++;
            } else {
                $errors[] = $contract->name . ': ' . ($result['error'] ?? 'ошибка');
            }
        }
        if ($updated > 0) {
            $msg = "ABI на ноде обновлён у {$updated} контрактов.";
            if (count($errors) > 0) {
                $msg .= ' Ошибки: ' . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $msg .= ' … (всего ' . count($errors) . ')';
                }
            }
            return redirect()->route('admin.contracts.index')->with('status', $msg);
        }
        if (count($errors) > 0) {
            return redirect()->route('admin.contracts.index')->withErrors(['abi' => implode(' ', array_slice($errors, 0, 3))]);
        }
        return redirect()->route('admin.contracts.index')->with('status', 'Нет контрактов с адресом и ABI для обновления.');
    }

    public function verify(Request $request, Contract $contract): RedirectResponse
    {
        if (! $contract->isDeployed()) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['verify' => 'Контракт должен быть задеплоен перед верификацией.']);
        }

        $contract->update(['status' => Contract::STATUS_VERIFIED]);

        return redirect()->route('admin.contracts.show', $contract)
            ->with('status', 'Контракт помечен как верифицированный.');
    }

    /**
     * Отправить локальный ABI контракта на ноду (PATCH .../contracts/:address/abi). Исправляет abi = NULL в gnd_db.
     */
    public function updateAbiOnNode(Request $request, Contract $contract): RedirectResponse
    {
        if (! $contract->isDeployed() || empty($contract->address)) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['abi' => 'Контракт должен быть задеплоен и иметь адрес.']);
        }
        $normalized = $this->normalizeAbiFromContract($contract->abi_json);
        if ($normalized['error'] !== null) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['abi' => $normalized['error']]);
        }
        $abi = $normalized['abi'];
        if (empty($abi)) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['abi' => 'Нет локального ABI. Сначала скомпилируйте контракт.']);
        }
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['abi' => 'Нода GND не настроена.']);
        }
        $result = GndAdminApi::updateContractAbi($contract->address, $abi);
        if (! ($result['success'] ?? false)) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['abi' => $result['error'] ?? 'Ошибка обновления ABI на ноде.']);
        }
        return redirect()->route('admin.contracts.show', $contract)
            ->with('status', 'ABI успешно обновлён на ноде (поле abi в gnd_db.contracts).');
    }

    public function create(Request $request): View
    {
        $wallets = [];
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::listWallets(null);
            $wallets = $result['wallets'] ?? [];
        }
        $templates = $this->listDeployOrderTemplates();
        return view('admin.contracts.create', ['wallets' => $wallets, 'templates' => $templates]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => [
                'nullable',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    if ($value !== null && trim($value) !== '' && ! FormatHelper::isContractAddress($value)) {
                        $fail('Адрес контракта должен быть в формате ' . config('gnd.contract_address_prefix', 'GNDct') . ' + 32 hex-символа.');
                    }
                },
            ],
            'description' => 'nullable|string|max:5000',
            'source_code' => 'nullable|string|max:500000',
            'standard' => 'nullable|string|max:32',
            'metadata_license' => 'nullable|string|max:128',
            'metadata_extra' => 'nullable|string|max:10000',
        ]);
        $source = $request->input('source_code');
        if ($request->hasFile('source_file')) {
            $source = $request->file('source_file')->get();
            if (! is_string($source)) {
                $source = '';
            }
        }
        $metadataJson = $this->buildMetadataJson($request);
        $contract = Contract::create([
            'name' => $request->name,
            'address' => $request->address,
            'description' => $request->description,
            'source_code' => $source ?: null,
            'standard' => $request->input('standard', 'GND-st1'),
            'status' => Contract::STATUS_DRAFT,
            'metadata_json' => ! empty($metadataJson) ? $metadataJson : null,
        ]);
        $this->syncManagedWallets($contract, $request->input('managed_wallets', []));
        return redirect()->route('admin.contracts.show', $contract)->with('status', 'Контракт создан. Загрузите исходный код и выполните компиляцию или анализ.');
    }

    public function show(Request $request, Contract $contract): View|RedirectResponse
    {
        $blockIdParam = $request->query('block_id');
        if ($blockIdParam !== null && trim((string) $blockIdParam) === '') {
            return redirect()->route('admin.contracts.show', $contract);
        }

        $contract->load('managedWallets');
        $wallets = [];
        $gndConfigured = GndAdminApi::isConfigured();
        if ($gndConfigured) {
            $result = GndAdminApi::listWallets(null);
            $wallets = $result['wallets'] ?? [];
        }

        $accountState = null;
        $contractState = null;
        $storageData = null;
        $storageBlockId = null;
        $stateError = null;
        $accountStateError = null;
        $storageError = null;
        $stateChanged = false;
        $contractView = null;
        $contractViewError = null;

        if ($gndConfigured && $contract->address) {
            $viewResult = GndAdminApi::getContractView($contract->address);
            if ($viewResult['error'] === null && isset($viewResult['data'])) {
                $contractView = $viewResult['data'];
                $nodeHasMethods = ! empty($contractView['view_functions'] ?? null) || ! empty($contractView['write_functions'] ?? null);
                if (! $nodeHasMethods && ! empty($contract->abi_json)) {
                    $contractView = $this->mergeContractViewFromLocalAbi($contractView, $contract->abi_json);
                }
            } else {
                $contractViewError = $viewResult['error'] ?? null;
                if (! empty($contract->abi_json) && $contract->address) {
                    $contractView = $this->mergeContractViewFromLocalAbi([
                        'address' => $contract->address,
                        'source_code' => $contract->source_code,
                        'abi' => [],
                    ], $contract->abi_json);
                }
            }

            $addresses = $contract->managedWallets->pluck('wallet_address')->filter()->values()->all();
            $stateResult = GndAdminApi::getContractState($contract->address, $addresses);
            if ($stateResult['error'] === null && isset($stateResult['data'])) {
                $contractState = $stateResult['data'];
                $stateChanged = $this->contractStateDiffersFromSnapshot($contractState, $contract->state_snapshot);
            } else {
                $stateError = $this->normalizeStateError($stateResult['error'] ?? null, 'contract');
            }

            $stateResultAccount = GndAdminApi::getAccountStateCurrent($contract->address);
            if ($stateResultAccount['error'] === null && isset($stateResultAccount['data'])) {
                $accountState = $stateResultAccount['data'];
            } else {
                $accountStateError = $this->normalizeStateError($stateResultAccount['error'] ?? null, 'account');
            }

            $blockId = $request->query('block_id');
            if ($blockId !== null && $blockId !== '') {
                $blockId = (int) $blockId;
                if ($blockId > 0) {
                    $storageBlockId = $blockId;
                    $storageResult = GndAdminApi::getContractStorage($contract->address, $blockId);
                    if ($storageResult['error'] === null && isset($storageResult['data'])) {
                        $storageData = $storageResult['data'];
                    } else {
                        $storageError = $this->normalizeStateError($storageResult['error'] ?? null, 'storage');
                    }
                }
            }
        }

        // Всегда подставлять методы из локального ABI, если нода не вернула методы (или контракт без адреса) — для вкладок «Просмотр», «Чтение», «Запись»
        if (! empty($contract->abi_json)) {
            $hasMethods = ! empty($contractView['view_functions'] ?? []) || ! empty($contractView['write_functions'] ?? []);
            if (! $hasMethods) {
                $base = $contractView ?? [
                    'address' => $contract->address ?? '',
                    'source_code' => $contract->source_code ?? '',
                    'abi' => [],
                ];
                $contractView = $this->mergeContractViewFromLocalAbi($base, $contract->abi_json);
            }
        }

        // Убрать дубликаты методов по сигнатуре (чтобы totalSupply() и др. не отображались и не вызывались дважды)
        if (! empty($contractView['view_functions'])) {
            $contractView['view_functions'] = $this->deduplicateFunctionsBySignature($contractView['view_functions']);
        }
        if (! empty($contractView['write_functions'])) {
            $contractView['write_functions'] = $this->deduplicateFunctionsBySignature($contractView['write_functions']);
        }

        return view('admin.contracts.show', [
            'contract' => $contract,
            'wallets' => $wallets,
            'gnd_configured' => $gndConfigured,
            'contract_view' => $contractView ?? [],
            'contract_view_error' => $contractViewError,
            'account_state' => $accountState,
            'contract_state' => $contractState,
            'state_changed' => $stateChanged,
            'storage_data' => $storageData,
            'storage_block_id' => $storageBlockId,
            'state_error' => $stateError,
            'account_state_error' => $accountStateError,
            'storage_error' => $storageError,
        ]);
    }

    /**
     * Нормализует abi_json контракта (строка или массив) в массив ABI. Учитывает пустую строку, "null",
     * двойное кодирование и невалидный JSON.
     *
     * @param  string|array|null  $abiJson
     * @return array{abi: array|null, error: string|null}  error — сообщение при невалидном JSON; при пустом ABI error = null, abi = null
     */
    private function normalizeAbiFromContract($abiJson): array
    {
        if (is_array($abiJson)) {
            return ['abi' => $abiJson, 'error' => null];
        }
        if ($abiJson === null || $abiJson === '') {
            return ['abi' => null, 'error' => null];
        }
        if (! is_string($abiJson)) {
            return ['abi' => null, 'error' => 'Локальный ABI имеет неверный тип данных.'];
        }
        $decoded = json_decode($abiJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $msg = 'Локальный ABI не является валидным JSON.';
            $hint = json_last_error_msg();
            if ($hint !== '') {
                $msg .= ' (' . $hint . ')';
            }
            return ['abi' => null, 'error' => $msg];
        }
        if (is_array($decoded)) {
            return ['abi' => $decoded, 'error' => null];
        }
        if (is_string($decoded)) {
            $decoded2 = json_decode($decoded, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) {
                return ['abi' => $decoded2, 'error' => null];
            }
        }
        return ['abi' => null, 'error' => null];
    }

    /**
     * Парсит локальный ABI (JSON) и дополняет contractView списками view_functions и write_functions,
     * если нода не вернула методы. Формат как у ноды: view = stateMutability view/pure или constant.
     *
     * @param  array<string, mixed>  $contractView
     * @param  string|array  $abiJson
     * @return array<string, mixed>
     */
    private function mergeContractViewFromLocalAbi(array $contractView, $abiJson): array
    {
        $normalized = $this->normalizeAbiFromContract($abiJson);
        $abi = $normalized['abi'];
        if (! is_array($abi)) {
            return $contractView;
        }
        $contractView['abi'] = $abi;
        $viewFuncs = [];
        $writeFuncs = [];
        foreach ($abi as $item) {
            if (($item['type'] ?? '') !== 'function') {
                continue;
            }
            $stateMutability = $item['stateMutability'] ?? '';
            $constant = (bool) ($item['constant'] ?? false);
            $isView = $stateMutability === 'view' || $stateMutability === 'pure' || $constant;
            $inputs = [];
            foreach ($item['inputs'] ?? [] as $inp) {
                $inputs[] = [
                    'name' => $inp['name'] ?? '',
                    'type' => $inp['type'] ?? 'string',
                ];
            }
            $fn = [
                'name' => $item['name'] ?? '',
                'type' => 'function',
                'inputs' => $inputs,
            ];
            if ($isView) {
                $viewFuncs[] = $fn;
            } else {
                $writeFuncs[] = $fn;
            }
        }
        $contractView['view_functions'] = $this->deduplicateFunctionsBySignature(
            array_merge($contractView['view_functions'] ?? [], $viewFuncs)
        );
        $contractView['write_functions'] = $this->deduplicateFunctionsBySignature(
            array_merge($contractView['write_functions'] ?? [], $writeFuncs)
        );

        return $contractView;
    }

    /**
     * Убирает дубликаты функций по сигнатуре (name + типы inputs), чтобы totalSupply() и др. не вызывались дважды.
     *
     * @param  array<int, array{name?: string, inputs?: array}>  $functions
     * @return array<int, array>
     */
    private function deduplicateFunctionsBySignature(array $functions): array
    {
        $seen = [];
        $out = [];
        foreach ($functions as $fn) {
            $name = $fn['name'] ?? '';
            $inputs = $fn['inputs'] ?? [];
            $sig = $name . '(' . implode(',', array_column($inputs, 'type')) . ')';
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            $out[] = $fn;
        }
        return array_values($out);
    }

    /**
     * Возвращает id контракта на ноде для call/send. Использует node_contract_id или запрашивает с ноды по address и сохраняет.
     */
    private function resolveNodeContractId(Contract $contract): ?int
    {
        if ($contract->node_contract_id !== null) {
            return (int) $contract->node_contract_id;
        }
        if (empty($contract->address)) {
            return null;
        }
        $nodeId = GndAdminApi::getNodeContractIdByAddress($contract->address);
        if ($nodeId !== null) {
            $contract->update(['node_contract_id' => $nodeId]);
            return $nodeId;
        }

        return null;
    }

    /**
     * Вызов view/constant метода контракта (AJAX). Body: data (hex), from?.
     */
    public function contractCall(Request $request, Contract $contract)
    {
        $request->validate(['data' => 'required|string']);
        $nodeId = $this->resolveNodeContractId($contract);
        if ($nodeId === null) {
            return response()->json([
                'success' => false,
                'error' => 'Не удалось определить id контракта на ноде. Укажите адрес и выполните «Синхронизировать с нодой» в списке контрактов.',
            ], 400);
        }
        $result = GndAdminApi::contractCall($nodeId, $request->input('data'), $request->input('from'));
        if ($result['error'] !== null) {
            return response()->json(['success' => false, 'error' => $result['error']], 400);
        }
        return response()->json(['success' => true, 'data' => $result['data']]);
    }

    /**
     * Отправка транзакции вызова метода контракта (AJAX). Body: from, data, value?, gas_limit?.
     */
    public function contractSend(Request $request, Contract $contract)
    {
        $request->validate([
            'from' => 'required|string',
            'data' => 'required|string',
        ]);
        $nodeId = $this->resolveNodeContractId($contract);
        if ($nodeId === null) {
            return response()->json([
                'success' => false,
                'error' => 'Не удалось определить id контракта на ноде. Укажите адрес и выполните «Синхронизировать с нодой» в списке контрактов.',
            ], 400);
        }
        $result = GndAdminApi::contractSend(
            $nodeId,
            $request->input('from'),
            $request->input('data'),
            $request->input('value', '0'),
            (int) $request->input('gas_limit', 200000)
        );
        if ($result['error'] !== null) {
            return response()->json(['success' => false, 'error' => $result['error']], 400);
        }
        return response()->json(['success' => true, 'data' => $result['data']]);
    }

    /**
     * Сохранить текущее состояние контракта (снимок) — записать, если изменилось.
     */
    public function saveState(Request $request, Contract $contract): RedirectResponse
    {
        if (! $contract->address) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['state' => 'У контракта нет адреса.']);
        }

        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['state' => 'API ноды не настроен.']);
        }

        $addresses = $contract->managedWallets->pluck('wallet_address')->filter()->values()->all();
        $result = GndAdminApi::getContractState($contract->address, $addresses);
        if ($result['error'] !== null || ! isset($result['data'])) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['state' => $result['error'] ?? 'Не удалось получить состояние контракта.']);
        }

        $snapshot = $result['data'];
        $contract->update([
            'state_snapshot' => $snapshot,
            'state_snapshot_at' => now(),
        ]);

        return redirect()->route('admin.contracts.show', $contract)
            ->with('status', 'Состояние контракта сохранено.');
    }

    private function normalizeStateError(?string $error, string $context = 'contract'): string
    {
        if ($error === null || $error === '') {
            return '';
        }
        $is404 = stripos($error, '404') !== false || stripos($error, 'not found') !== false || stripos($error, 'page not found') !== false;
        if ($is404) {
            return match ($context) {
                'account' => 'Запись аккаунта в таблице accounts на ноде не найдена (404). Может появиться после обработки блоков.',
                'storage' => 'Слоты storage для указанного блока не найдены или запрос к ноде вернул 404.',
                default => 'Контракт не найден на ноде (404). Убедитесь, что нода запущена и контракт задеплоен в сети.',
            };
        }
        return $error;
    }

    private function contractStateDiffersFromSnapshot(?array $current, ?array $snapshot): bool
    {
        if ($snapshot === null || $snapshot === []) {
            return $current !== null && $current !== [];
        }
        if ($current === null || $current === []) {
            return true;
        }
        $keys = ['name', 'symbol', 'owner', 'decimals', 'total_supply', 'standard', 'balances'];
        foreach ($keys as $key) {
            $c = $current[$key] ?? null;
            $s = $snapshot[$key] ?? null;
            if (is_array($c) && is_array($s)) {
                ksort($c);
                ksort($s);
                if (json_encode($c) !== json_encode($s)) {
                    return true;
                }
            } elseif ((string) $c !== (string) $s) {
                return true;
            }
        }
        return false;
    }

    /**
     * Запись слота storage контракта (POST). Требуется адрес контракта и X-Admin-Token на ноде.
     */
    public function writeStorage(Request $request, Contract $contract): RedirectResponse
    {
        if (! $contract->address) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['state' => 'У контракта нет адреса (сначала задеплойте).']);
        }

        $request->validate([
            'block_id' => 'required|integer|min:1',
            'slot_key' => 'required|string|max:70',
            'slot_value' => 'required|string|max:70',
        ]);

        $result = GndAdminApi::writeContractStorageSlot(
            $contract->address,
            (int) $request->block_id,
            $request->slot_key,
            $request->slot_value
        );

        if ($result['ok']) {
            return redirect()->to(route('admin.contracts.show', $contract) . '?block_id=' . (int) $request->block_id)
                ->with('status', 'Слот storage записан.');
        }

        return redirect()->route('admin.contracts.show', $contract)
            ->withErrors(['state' => $result['error'] ?? 'Ошибка записи слота.']);
    }

    public function edit(Contract $contract): View
    {
        $contract->load('managedWallets');
        $wallets = [];
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::listWallets(null);
            $wallets = $result['wallets'] ?? [];
        }
        $templates = $this->listDeployOrderTemplates();
        return view('admin.contracts.edit', [
            'contract' => $contract,
            'wallets' => $wallets,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => [
                'nullable',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    if ($value !== null && trim($value) !== '' && ! FormatHelper::isContractAddress($value)) {
                        $fail('Адрес контракта должен быть в формате ' . config('gnd.contract_address_prefix', 'GNDct') . ' + 32 hex-символа.');
                    }
                },
            ],
            'description' => 'nullable|string|max:5000',
            'source_code' => 'nullable|string|max:500000',
            'standard' => 'nullable|string|max:32',
            'metadata_license' => 'nullable|string|max:128',
            'metadata_extra' => 'nullable|string|max:10000',
        ]);
        $data = $request->only('name', 'address', 'description', 'standard');
        if ($request->has('source_code')) {
            $data['source_code'] = $request->source_code;
        }
        $data['metadata_json'] = $this->buildMetadataJson($request, $contract);
        $contract->update($data);
        $this->syncManagedWallets($contract, $request->input('managed_wallets', []));
        return redirect()->route('admin.contracts.show', $contract)->with('status', 'Контракт обновлён.');
    }

    public function compile(Request $request, Contract $contract): RedirectResponse
    {
        $source = $contract->source_code;
        if (empty($source)) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['compile' => 'Загрузите исходный код контракта.']);
        }
        $result = GndAdminApi::compileContract($source, $contract->name, $contract->standard ?? 'GND-st1');
        if ($result['error'] !== null) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['compile' => $result['error']]);
        }
        $data = $result['data'];
        $contract->update([
            'bytecode_hex' => $data['bytecode'] ?? null,
            'abi_json' => is_string($data['abi'] ?? null) ? $data['abi'] : json_encode($data['abi'] ?? []),
            'compiler_version' => 'solc-0.8',
            'status' => Contract::STATUS_ANALYZED,
        ]);
        return redirect()->route('admin.contracts.show', $contract)->with('status', 'Контракт скомпилирован. Можно выполнить деплой.');
    }

    public function analyze(Request $request, Contract $contract): RedirectResponse
    {
        $source = $contract->source_code;
        if (empty($source)) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['analyze' => 'Загрузите исходный код контракта.']);
        }
        $result = GndAdminApi::analyzeContract($source);
        if ($result['error'] !== null) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['analyze' => $result['error']]);
        }
        $issues = $result['data']['issues'] ?? [];
        $contract->update([
            'analysis_report' => $issues,
            'status' => Contract::STATUS_ANALYZED,
        ]);
        return redirect()->route('admin.contracts.show', $contract)->with('status', 'Анализ безопасности выполнен. Найдено замечаний: ' . count($issues));
    }

    public function deploy(ContractDeployRequest $request, Contract $contract): RedirectResponse
    {
        $bytecode = $contract->bytecode_hex;
        $abiJson = $contract->abi_json;
        // Если ABI нет, но есть исходный код — компилируем, чтобы при деплое отправить ABI на ноду (иначе в gnd_db.contracts.abi будет NULL)
        if (empty($abiJson) && ! empty($contract->source_code) && GndAdminApi::isConfigured()) {
            $compileResult = GndAdminApi::compileContract(
                $contract->source_code,
                $contract->name,
                $contract->standard ?? 'GND-st1'
            );
            if ($compileResult['error'] === null && ! empty($compileResult['data'])) {
                $data = $compileResult['data'];
                $abiJson = is_string($data['abi'] ?? null) ? $data['abi'] : json_encode($data['abi'] ?? []);
                $contract->update([
                    'abi_json' => $abiJson,
                    'bytecode_hex' => $data['bytecode'] ?? $contract->bytecode_hex,
                ]);
                $bytecode = $contract->bytecode_hex;
            }
        }
        if (empty($bytecode)) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['deploy' => 'Сначала выполните компиляцию контракта.']);
        }
        $params = [
            'from' => $request->deployer_address,
            'bytecode' => $bytecode,
            'name' => $contract->name,
            'standard' => $contract->standard ?? 'GND-st1',
            'owner' => $request->deployer_address,
            'source_code' => $contract->source_code,
            'compiler' => $contract->compiler_version ?? 'solc',
            'version' => '1.0',
        ];
        $abiForDeploy = $contract->abi_json;
        if (! empty($abiForDeploy)) {
            $params['abi'] = is_string($abiForDeploy) ? json_decode($abiForDeploy, true) : $abiForDeploy;
        }
        if (! empty($contract->metadata_json)) {
            $params['metadata'] = $contract->metadata_json;
        }
        // Параметры конструктора для ноды (ABI-кодируются на ноде и дополняют bytecode)
        $constructorParams = $this->collectConstructorParams($request);
        if (! empty($constructorParams)) {
            $params['params'] = $constructorParams;
        }
        $result = GndAdminApi::deployContract($params);
        if ($result['error'] !== null) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['deploy' => $result['error']]);
        }
        $address = $result['data']['address'] ?? null;
        if (empty($address)) {
            return redirect()->route('admin.contracts.show', $contract)
                ->withErrors(['deploy' => 'Нода не вернула адрес контракта.']);
        }
        $updates = [
            'address' => $address,
            'deployer_address' => $request->deployer_address,
            'status' => Contract::STATUS_DEPLOYED,
        ];
        $nodeId = GndAdminApi::getNodeContractIdByAddress($address);
        if ($nodeId !== null) {
            $updates['node_contract_id'] = $nodeId;
        }
        $contract->update($updates);
        return redirect()->route('admin.contracts.show', $contract)->with('status', 'Контракт задеплоен. Адрес: ' . $address);
    }

    public function updateWallets(Request $request, Contract $contract): RedirectResponse
    {
        $request->validate(['managed_wallets' => 'nullable|array', 'managed_wallets.*' => 'string|max:255']);
        $this->syncManagedWallets($contract, $request->input('managed_wallets', []));
        return redirect()->route('admin.contracts.show', $contract)->with('status', 'Список кошельков обновлён.');
    }

    /**
     * Отвязать один кошелёк от контракта. Редирект на страницу контракта.
     */
    public function removeWallet(Request $request, Contract $contract): RedirectResponse
    {
        $request->validate(['address' => 'required|string|max:255']);
        $address = $request->input('address');
        $contract->managedWallets()->where('wallet_address', $address)->delete();
        return redirect()->route('admin.contracts.show', $contract)->with('status', 'Кошелёк отвязан от контракта.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $contract->delete();
        return redirect()->route('admin.contracts.index')->with('status', 'Контракт удалён.');
    }

    /**
     * Собирает параметры конструктора из запроса для передачи на ноду (params).
     * Поддерживаются constructor_params (массив по имени поля) и constructor_params_json (JSON-строка).
     */
    private function collectConstructorParams(Request $request): array
    {
        $json = $request->input('constructor_params_json');
        if (is_string($json) && trim($json) !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return array_filter($decoded, function ($v) {
                    return $v !== '' && $v !== null;
                });
            }
        }
        $arr = $request->input('constructor_params', []);
        if (! is_array($arr)) {
            return [];
        }
        $out = [];
        foreach ($arr as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $out[$key] = $value;
        }
        return $out;
    }

    private function syncManagedWallets(Contract $contract, array $addresses): void
    {
        $addresses = array_filter(array_unique($addresses));
        $contract->managedWallets()->delete();
        foreach ($addresses as $addr) {
            $contract->managedWallets()->create(['wallet_address' => $addr]);
        }
    }

    private function buildMetadataJson(Request $request, ?Contract $contract = null): ?array
    {
        $base = array_filter([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'standard' => $request->input('standard', 'GND-st1'),
            'license' => $request->input('metadata_license') ?: null,
        ]);
        if ($contract && $contract->compiler_version) {
            $base['compiler'] = $contract->compiler_version;
        }
        $extra = $request->input('metadata_extra');
        if (is_string($extra) && $extra !== '') {
            $decoded = json_decode($extra, true);
            if (is_array($decoded)) {
                $base = array_merge($base, $decoded);
            }
        }
        return $base ?: null;
    }

    /**
     * Список шаблонов из deploy_order (если путь настроен).
     *
     * @return array<string, string> [filename => label]
     */
    private function listDeployOrderTemplates(): array
    {
        $path = config('gnd.deploy_order_path');
        if (! is_string($path) || $path === '') {
            return [];
        }
        $dir = str_starts_with($path, '/') || preg_match('#^[A-Za-z]:#', $path)
            ? $path
            : base_path($path);
        if (! is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/*.sol');
        if ($files === false) {
            return [];
        }
        $out = [];
        foreach ($files as $f) {
            $name = basename($f);
            $out[$name] = $name;
        }
        ksort($out);
        return $out;
    }

    /**
     * Загрузка шаблона из deploy_order (JSON).
     */
    public function loadTemplate(Request $request): JsonResponse
    {
        $filename = $request->input('template');
        if (! is_string($filename) || $filename === '' || ! preg_match('/^[a-zA-Z0-9_\-\.]+\.sol$/', $filename)) {
            return response()->json(['error' => 'Недопустимое имя шаблона'], 400);
        }
        $path = config('gnd.deploy_order_path');
        if (! is_string($path) || $path === '') {
            return response()->json(['error' => 'Путь deploy_order не настроен'], 400);
        }
        $dir = str_starts_with($path, '/') || preg_match('#^[A-Za-z]:#', $path)
            ? $path
            : base_path($path);
        $resolvedDir = realpath($dir);
        if ($resolvedDir === false || ! is_dir($resolvedDir)) {
            return response()->json(['error' => 'Каталог deploy_order не найден'], 400);
        }
        $fullPath = realpath($resolvedDir . DIRECTORY_SEPARATOR . $filename);
        $prefix = rtrim($resolvedDir, '/\\') . DIRECTORY_SEPARATOR;
        if ($fullPath === false || ! str_starts_with($fullPath, $prefix)) {
            return response()->json(['error' => 'Шаблон не найден'], 404);
        }
        $content = @file_get_contents($fullPath);
        if ($content === false) {
            return response()->json(['error' => 'Не удалось прочитать файл'], 500);
        }
        return response()->json(['source_code' => $content]);
    }
}

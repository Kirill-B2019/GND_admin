<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$contract->name">
            <x-slot name="actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-outline-primary btn-sm">Изменить</a>
                <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary btn-sm">К списку</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <x-admin.breadcrumbs :items="[
                ['label' => 'Обзор', 'url' => route('admin.dashboard')],
                ['label' => 'Контракты', 'url' => route('admin.contracts.index')],
                ['label' => $contract->name],
            ]" />
            @if ($errors->any())
                <x-admin.alert type="danger" title="Проверьте форму">
                    @foreach ($errors->all() as $e) {{ $e }}<br>@endforeach
                </x-admin.alert>
            @endif

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card bg-dark border-secondary">
                        <div class="card-header border-secondary text-light">Информация</div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-secondary">Название</dt>
                                <dd class="col-sm-8 text-light">{{ $contract->name }}</dd>
                                <dt class="col-sm-4 text-secondary">Статус</dt>
                                <dd class="col-sm-8">
                                    @if($contract->status === 'deployed' || $contract->status === 'verified')
                                        <span class="badge bg-success">{{ $contract->status }}</span>
                                    @elseif($contract->status === 'analyzed')
                                        <span class="badge bg-info">{{ $contract->status }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $contract->status ?? 'draft' }}</span>
                                    @endif
                                </dd>
                                <dt class="col-sm-4 text-secondary">Адрес</dt>
                                <dd class="col-sm-8 font-monospace small">{{ $contract->address ?? '—' }}</dd>
                                <dt class="col-sm-4 text-secondary">Деплойер</dt>
                                <dd class="col-sm-8 font-monospace small text-secondary">{{ $contract->deployer_address ?? '—' }}</dd>
                                <dt class="col-sm-4 text-secondary">Стандарт</dt>
                                <dd class="col-sm-8 text-secondary">{{ $contract->standard ?? '—' }}</dd>
                                <dt class="col-sm-4 text-secondary">Описание</dt>
                                <dd class="col-sm-8 text-light">{{ $contract->description ? nl2br(e($contract->description)) : '—' }}</dd>
                                <dt class="col-sm-4 text-secondary">Создан / Обновлён</dt>
                                <dd class="col-sm-8 text-secondary">{{ $contract->created_at->format('d.m.Y H:i') }} / {{ $contract->updated_at->format('d.m.Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    @if($contract->source_code || ($contract->isDeployed() && $contract->address))
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header border-secondary text-light d-flex justify-content-between align-items-center">
                            <span>Действия</span>
                        </div>
                        <div class="card-body">
                            @if($contract->source_code)
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <form action="{{ route('admin.contracts.compile', $contract) }}" method="post" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info btn-sm">{{ $contract->isDeployed() ? 'Компилировать (обновить ABI в карточке)' : '1. Компилировать' }}</button>
                                    </form>
                                    @if(!$contract->isDeployed())
                                        <form action="{{ route('admin.contracts.analyze', $contract) }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-sm">2. Анализ безопасности</button>
                                        </form>
                                    @endif

                                    @if($contract->isDeployed() && $contract->status !== \App\Models\Contract::STATUS_VERIFIED)
                                        <form action="{{ route('admin.contracts.verify', $contract) }}" method="post" class="d-inline" data-confirm="Пометить контракт как верифицированный?">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm">Отметить как верифицированный</button>
                                        </form>
                                    @endif
                                    @if($contract->isDeployed() && $contract->address && $gnd_configured)
                                        <form action="{{ route('admin.contracts.update-abi-on-node', $contract) }}" method="post" class="d-inline" data-confirm="Отправить локальный ABI на ноду (исправить abi = NULL в gnd_db)?">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info btn-sm">Обновить ABI на ноде</button>
                                        </form>
                                        @if(empty($contract->abi_json))
                                            <span class="text-warning small">Сначала нажмите «Компилировать» — в карточке появится ABI.</span>
                                        @endif
                                    @endif
                                </div>

                                @if(!$contract->isDeployed() && $contract->bytecode_hex && $gnd_configured)
                                    <hr class="border-secondary my-3">
                                    <p class="text-secondary small mb-2">Деплой на ноду:</p>
                                    <form action="{{ route('admin.contracts.deploy', $contract) }}" method="post">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="form-label text-light small">Кошелёк (оплачивает газ) <span class="text-danger">*</span></label>
                                            <select name="deployer_address" required class="form-select form-select-sm bg-dark text-light border-secondary">
                                                <option value="">— Выберите кошелёк —</option>
                                                @foreach($wallets as $w)
                                                    @php
                                                        $addr = is_array($w) ? ($w['address'] ?? '') : ($w->address ?? '');
                                                        $nm = is_array($w) ? ($w['name'] ?? '') : ($w->name ?? '');
                                                    @endphp
                                                    @if($addr)
                                                        <option value="{{ $addr }}">{{ $nm ? $nm . ' — ' . $addr : $addr }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        @php $cn = $contract->name ?? ''; @endphp
                                        @if($cn === 'NativeTokensController')
                                            <div class="mb-2">
                                                <label class="form-label text-light small">owner_ (адрес владельца, gndself_address)</label>
                                                <input type="text" name="constructor_params[owner_]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="GN_BSP... или GNDct..." value="{{ old('constructor_params.owner_') }}">
                                            </div>
                                        @elseif($cn === 'GNDToken')
                                            <div class="mb-2">
                                                <label class="form-label text-light small">initialSupply (uint256)</label>
                                                <input type="text" name="constructor_params[initialSupply]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="1000000000000000000000000000" value="{{ old('constructor_params.initialSupply', '1000000000000000000000000000') }}">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label text-light small">bridgeAddress (address)</label>
                                                <input type="text" name="constructor_params[bridgeAddress]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="0x0 или GNDct..." value="{{ old('constructor_params.bridgeAddress', '0x0000000000000000000000000000000000000000') }}">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label text-light small">controllerContract (address)</label>
                                                <input type="text" name="constructor_params[controllerContract]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="GNDct... адрес контроллера" value="{{ old('constructor_params.controllerContract') }}">
                                            </div>
                                        @elseif($cn === 'GANIToken')
                                            <div class="mb-2">
                                                <label class="form-label text-light small">controllerContract (address)</label>
                                                <input type="text" name="constructor_params[controllerContract]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="GNDct... адрес NativeTokensController" value="{{ old('constructor_params.controllerContract') }}">
                                            </div>
                                        @elseif($cn === 'GNDRWAToken')
                                            <div class="mb-2">
                                                <label class="form-label text-light small">controllerContract (address)</label>
                                                <input type="text" name="constructor_params[controllerContract]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="GNDct..." value="{{ old('constructor_params.controllerContract') }}">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label text-light small">bridgeAddress (address)</label>
                                                <input type="text" name="constructor_params[bridgeAddress]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="0x0 или GNDct..." value="{{ old('constructor_params.bridgeAddress', '0x0000000000000000000000000000000000000000') }}">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label text-light small">name_ (string)</label>
                                                    <input type="text" name="constructor_params[name_]" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="RWA Token" value="{{ old('constructor_params.name_', 'RWA Token') }}">
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label text-light small">symbol_ (string)</label>
                                                    <input type="text" name="constructor_params[symbol_]" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="RWA" value="{{ old('constructor_params.symbol_', 'RWA') }}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label text-light small">decimals_ (uint8)</label>
                                                    <input type="number" name="constructor_params[decimals_]" class="form-control form-control-sm bg-dark text-light border-secondary" min="0" max="255" value="{{ old('constructor_params.decimals_', 18) }}">
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label text-light small">maxSupply_ (uint256)</label>
                                                    <input type="text" name="constructor_params[maxSupply_]" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace" placeholder="0 = без лимита" value="{{ old('constructor_params.maxSupply_', '0') }}">
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-2">
                                                <label class="form-label text-light small">Параметры конструктора (JSON, опционально)</label>
                                                <textarea name="constructor_params_json" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace small" rows="2" placeholder='{"owner_": "GN_BSP...", "controllerContract": "GNDct..."}'>{{ old('constructor_params_json') }}</textarea>
                                                <p class="text-secondary small mt-1 mb-0">Ключи — имена аргументов из ABI конструктора. Для deploy_order контрактов выше выводятся отдельные поля.</p>
                                            </div>
                                        @endif
                                        <button type="submit" class="btn btn-primary btn-sm">Задеплоить на ноду</button>
                                    </form>
                                @endif
                            @else
                                <p class="text-secondary small mb-2">Нет исходного кода в карточке. Добавьте в <a href="{{ route('admin.contracts.edit', $contract) }}">редактировании</a>, сохраните, затем «Компилировать» и «Обновить ABI на ноде».</p>
                                @if($contract->isDeployed() && $contract->address && $gnd_configured)
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <form action="{{ route('admin.contracts.update-abi-on-node', $contract) }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info btn-sm">Обновить ABI на ноде</button>
                                        </form>
                                        @if(empty($contract->abi_json))
                                            <span class="text-warning small">Сначала добавьте исходный код и нажмите «Компилировать».</span>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    @endif

                    <details class="border border-secondary rounded mb-4" id="details-wallets">
                        <summary class="bg-dark text-light border-secondary px-3 py-2 rounded user-select-none cursor-pointer">Управляемые кошельки ({{ $contract->managedWallets->count() }})</summary>
                        <div class="p-3 pt-0">
                            @php
                                $walletNamesByAddress = collect($wallets)->keyBy(fn ($w) => is_array($w) ? ($w['address'] ?? '') : ($w->address ?? ''))->map(fn ($w) => is_array($w) ? ($w['name'] ?? null) : ($w->name ?? null));
                            @endphp
                            @if($contract->managedWallets->count() > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($contract->managedWallets as $cw)
                                        @php $name = $walletNamesByAddress->get($cw->wallet_address); @endphp
                                        <li class="text-secondary d-flex justify-content-between align-items-center gap-2 small mb-1">
                                            <span class="font-monospace">{{ $name ? $name . ' — ' . $cw->wallet_address : $cw->wallet_address }}</span>
                                            <span class="d-flex gap-1">
                                                <form action="{{ route('admin.wallets.disable', ['address' => $cw->wallet_address]) }}" method="post" class="d-inline" data-confirm="Отключить подписание?">@csrf<button type="submit" class="btn btn-outline-warning btn-sm">Выкл</button></form>
                                                <form action="{{ route('admin.wallets.enable', ['address' => $cw->wallet_address]) }}" method="post" class="d-inline" data-confirm="Включить подписание?">@csrf<button type="submit" class="btn btn-outline-success btn-sm">Вкл</button></form>
                                                <form action="{{ route('admin.contracts.wallets.remove', $contract) }}" method="post" class="d-inline" data-confirm="Отвязать кошелёк?">@csrf<input type="hidden" name="address" value="{{ $cw->wallet_address }}"><button type="submit" class="btn btn-outline-danger btn-sm">×</button></form>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-secondary small mb-0">Нет. Настроить в <a href="{{ route('admin.contracts.edit', $contract) }}">редактировании</a>.</p>
                            @endif
                        </div>
                    </details>
                </div>
            </div>

            @if($contract->address && $gnd_configured)
            <div class="row g-4">
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-header border-secondary text-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span>Методы контракта</span>
                            @if(!empty($contract_state) && ($state_changed ?? false))
                                <form action="{{ route('admin.contracts.state.save-snapshot', $contract) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Сохранить состояние</button>
                                </form>
                            @endif
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-tabs-dark border-secondary mb-3" id="contractTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active text-light" id="tab-view" data-bs-toggle="tab" data-bs-target="#panel-view" type="button" role="tab">Просмотр</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-light" id="tab-read" data-bs-toggle="tab" data-bs-target="#panel-read" type="button" role="tab">Чтение</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-light" id="tab-write" data-bs-toggle="tab" data-bs-target="#panel-write" type="button" role="tab">Запись</button>
                                </li>
                            </ul>
                            @if($state_error || ($contract_view_error ?? null))
                                <div class="alert alert-warning small mb-3">{{ e($state_error ?? $contract_view_error) }}</div>
                            @endif
                            <div class="tab-content" id="contractPanels">
                                {{-- Просмотр контракта: код на Solidity и методы/функции самого контракта (из ABI) --}}
                                <div class="tab-pane fade show active" id="panel-view" role="tabpanel">
                                    @if(!empty($contract_view))
                                        <p class="text-secondary small mb-2">Адрес: <span class="font-monospace text-light">{{ $contract_view['address'] ?? '—' }}</span></p>
                                        @php $sourceCode = $contract_view['source_code'] ?? $contract->source_code ?? ''; @endphp
                                        @if($sourceCode !== '')
                                            <pre class="bg-dark border border-secondary rounded p-2 font-monospace small text-light mb-3" style="max-height: 18rem; overflow-y: auto; white-space: pre-wrap;">{{ e($sourceCode) }}</pre>
                                        @else
                                            <p class="text-secondary small mb-2">Исходный код на ноде не сохранён. Ниже в блоке «Дополнительно» — код из карточки.</p>
                                        @endif
                                        @if(!empty($contract_view['view_functions']) || !empty($contract_view['write_functions']))
                                            @if(!empty($contract_view['view_functions']))
                                                <p class="text-info small mb-1">View: {{ implode(', ', array_column($contract_view['view_functions'], 'name')) }}</p>
                                            @endif
                                            @if(!empty($contract_view['write_functions']))
                                                <p class="text-warning small mb-2">Write: {{ implode(', ', array_column($contract_view['write_functions'], 'name')) }}</p>
                                            @endif
                                        @endif
                                        @if(!empty($contract_view['abi']) && is_array($contract_view['abi']) && count($contract_view['abi']) > 0)
                                            <details class="border border-secondary rounded mt-2" id="details-abi">
                                                <summary class="bg-dark text-light border-secondary px-3 py-2 rounded cursor-pointer user-select-none">ABI контракта (JSON)</summary>
                                                <div class="p-3 pt-0">
                                                    <pre class="mb-0 font-monospace small text-break bg-dark text-light p-2 rounded" style="max-height: 16rem; overflow-y: auto;">{{ json_encode($contract_view['abi'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            </details>
                                        @endif
                                    @else
                                        <p class="text-secondary small mb-0">Загрузите данные с ноды (адрес контракта и нода должны быть доступны).</p>
                                    @endif
                                </div>
                                {{-- Чтение: все view/pure методы сразу, у каждого — поля и значение (read only) --}}
                                <div class="tab-pane fade" id="panel-read" role="tabpanel">
                                    @if(!empty($contract_view['view_functions']))
                                    <div id="read-contract-methods">
                                        @foreach($contract_view['view_functions'] as $idx => $fn)
                                            @php
                                                $sigRead = $fn['name'] . '(' . implode(', ', array_map(fn($i) => ($i['type'] ?? '') . ' ' . ($i['name'] ?? ''), $fn['inputs'] ?? [])) . ')';
                                                $inputs = $fn['inputs'] ?? [];
                                            @endphp
                                            <div class="read-method-block border border-secondary rounded p-3 mb-3 bg-dark" data-method="{{ $sigRead }}" data-method-name="{{ $fn['name'] }}">
                                                <div class="small font-monospace text-info mb-2">{{ $fn['name'] }}({{ implode(', ', array_map(fn($i) => ($i['type'] ?? '') . ' ' . ($i['name'] ?? ''), $inputs)) }})</div>
                                                <div class="read-method-inputs mb-2" id="read-inputs-{{ $idx }}" data-inputs="{{ json_encode($inputs) }}"></div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <button type="button" class="btn btn-outline-info btn-sm btn-call-read" data-index="{{ $idx }}">Вызвать</button>
                                                    <span class="read-method-result font-monospace small text-success" id="read-result-{{ $idx }}"></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mb-2">
                                        <button type="button" id="btn-call-read-all" class="btn btn-outline-secondary btn-sm">Вызвать все методы чтения</button>
                                    </div>
                                    @else
                                        <div class="alert alert-warning small mb-0">Методы чтения не найдены. Загрузите контракт с ноды (адрес задан) и убедитесь, что ABI содержит view/pure функции.</div>
                                    @endif
                                </div>
                                {{-- Запись: все write-методы сразу, у каждого — поля и кнопка «Отправить» --}}
                                <div class="tab-pane fade" id="panel-write" role="tabpanel">
                                    @if(empty($contract_view['write_functions']))
                                        <div class="alert alert-warning small mb-0">Методы записи не найдены. Нода не вернула список методов (возможно, в БД ноды нет ABI для этого контракта). Если контракт компилировался в этой админке — ABI сохранён у контракта и методы подставятся автоматически. Иначе загрузите исходный код и нажмите «Компилировать», затем обновите страницу.</div>
                                    @else
                                    <div class="mb-3">
                                        <label class="form-label small text-secondary mb-0">Кошелёк (from)</label>
                                        <select id="write-from-wallet" class="form-select form-select-sm bg-dark text-light border-secondary">
                                            <option value="">— Выберите кошелёк —</option>
                                            @foreach($wallets as $w)
                                                @php $addr = is_array($w) ? ($w['address'] ?? '') : ($w->address ?? ''); $nm = is_array($w) ? ($w['name'] ?? '') : ($w->name ?? ''); @endphp
                                                @if($addr)
                                                    <option value="{{ $addr }}">{{ $nm ? $nm . ' — ' . $addr : $addr }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="write-contract-methods">
                                        @foreach($contract_view['write_functions'] as $idx => $fn)
                                            @php
                                                $sig = $fn['name'] . '(' . implode(', ', array_map(fn($i) => ($i['type'] ?? '') . ' ' . ($i['name'] ?? ''), $fn['inputs'] ?? [])) . ')';
                                                $inputs = $fn['inputs'] ?? [];
                                            @endphp
                                            <div class="write-method-block border border-secondary rounded p-3 mb-3 bg-dark" data-method="{{ $sig }}" data-method-name="{{ $fn['name'] }}">
                                                <div class="small font-monospace text-warning mb-2">{{ $fn['name'] }}({{ implode(', ', array_map(fn($i) => ($i['type'] ?? '') . ' ' . ($i['name'] ?? ''), $inputs)) }})</div>
                                                <div class="write-method-inputs mb-2" id="write-inputs-{{ $idx }}" data-inputs="{{ json_encode($inputs) }}"></div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-send-write" data-index="{{ $idx }}">Отправить</button>
                                                    <span class="write-method-result font-monospace small" id="write-result-{{ $idx }}"></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row g-4 mt-0">
                <div class="col-12">
                    <details class="border border-secondary rounded bg-dark" id="details-extra">
                        <summary class="text-light border-secondary px-3 py-2 rounded user-select-none cursor-pointer">Дополнительно (код, метаданные, отчёт)</summary>
                        <div class="p-3 pt-0">
                            @if($contract->source_code)
                                <p class="text-secondary small mb-1">Исходный код (Solidity):</p>
                                <pre class="bg-dark border border-secondary rounded p-2 font-monospace small text-light mb-3" style="max-height: 16rem; overflow-y: auto; white-space: pre-wrap;">{{ e($contract->source_code) }}</pre>
                            @endif
                            @if(!empty($contract->metadata_json))
                                <p class="text-secondary small mb-1">Метаданные:</p>
                                <dl class="row mb-3 small">
                                    @foreach($contract->metadata_json as $key => $value)
                                        <dt class="col-sm-3 text-secondary">{{ e($key) }}</dt>
                                        <dd class="col-sm-9 text-light">{{ is_scalar($value) ? e($value) : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd>
                                    @endforeach
                                </dl>
                            @endif
                            @if(!empty($contract->analysis_report))
                                <p class="text-secondary small mb-1">Отчёт по безопасности:</p>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($contract->analysis_report as $issue)
                                        @php
                                            $sev = is_array($issue) ? ($issue['Severity'] ?? 'info') : ($issue->Severity ?? 'info');
                                            $badge = $sev === 'critical' ? 'danger' : ($sev === 'high' ? 'warning' : ($sev === 'medium' ? 'info' : 'secondary'));
                                        @endphp
                                        <li class="mb-1"><span class="badge bg-{{ $badge }} me-1">{{ $sev }}</span> {{ is_array($issue) ? ($issue['Title'] ?? '') : ($issue->Title ?? '') }} — {{ is_array($issue) ? ($issue['Description'] ?? '') : ($issue->Description ?? '') }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if(!$contract->source_code && empty($contract->metadata_json) && empty($contract->analysis_report))
                                <p class="text-secondary small mb-0">Нет дополнительных данных.</p>
                            @endif
                        </div>
                    </details>
                </div>
            </div>

            <div class="mt-4">
                <form action="{{ route('admin.contracts.destroy', $contract) }}" method="post" class="d-inline" data-confirm="Удалить контракт?">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Удалить контракт</button>
                </form>
            </div>
        </div>
    </div>

    @if($contract->address && $gnd_configured && !empty($contract_view))
    @php
        $contractShowJsConfig = [
            'contractView' => $contract_view,
            'callUrl' => route('admin.contracts.call', $contract),
            'sendUrl' => route('admin.contracts.send', $contract),
            'gnd' => [
                'contractAddressPrefix' => config('gnd.contract_address_prefix', 'GNDct'),
                'contractAddressLength' => (int) config('gnd.contract_address_length', 37),
                'contractAddressHexLength' => (int) config('gnd.contract_address_hex_length', 32),
            ],
        ];
    @endphp
    <script type="application/json" id="contract-show-config">@json($contractShowJsConfig)</script>
    @vite(['resources/js/contract-show.js'])
    @endif
</x-app-layout>

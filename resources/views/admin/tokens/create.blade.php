<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Добавить токен</h2>
            <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 42rem;">
            @if ($errors->any())
                <div class="alert alert-danger">Исправьте ошибки в форме.</div>
            @endif
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tokens.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3 position-relative">
                            <label for="token_address" class="form-label text-light">Контракт токена <span class="text-danger">*</span></label>
                            <input type="text" name="token_address" id="token_address" value="{{ old('token_address') }}" required
                                class="form-control bg-dark text-light border-secondary font-monospace" placeholder="Введите название или адрес контракта..."
                                maxlength="255" autocomplete="off">
                            <div id="token_address_list" class="list-group position-absolute top-100 start-0 end-0 mt-1 rounded shadow-lg border border-secondary overflow-hidden" style="z-index: 1000; max-height: 220px; overflow-y: auto; display: none;"></div>
                            <p class="text-secondary small mt-1 mb-0">Начните вводить название контракта или его адрес — выберите из списка или вставьте адрес вручную.</p>
                            @error('token_address')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <script type="application/json" id="token_contracts_data">@json($contracts->map(fn($c) => ['name' => $c->name ?? 'Без названия', 'address' => $c->address ?? ''])->values())</script>
                        <div class="mb-3">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <label for="ship_to_wallet" class="form-label text-light mb-0">Выгрузить токен в кошелёк</label>
                                <div class="form-check mb-0">
                                    <input type="hidden" name="is_system" value="0">
                                    <input type="checkbox" name="is_system" id="is_system_token" value="1" {{ old('is_system') ? 'checked' : '' }} class="form-check-input">
                                    <label for="is_system_token" class="form-check-label text-light">Системный</label>
                                </div>
                            </div>
                            <p class="text-secondary small mb-2">Кошелёк-владелец (куда выгружается токен). При отмеченном «Системный» владелец всегда gndself_address.</p>
                            @if(count($wallets) > 0)
                                <div class="position-relative">
                                    <input type="text" name="ship_to_wallet" id="ship_to_wallet" value="{{ old('ship_to_wallet') }}"
                                        class="form-control bg-dark text-light border-secondary font-monospace" placeholder="Введите название или адрес кошелька..."
                                        maxlength="255" autocomplete="off">
                                    <div id="ship_to_wallet_list" class="list-group position-absolute top-100 start-0 end-0 mt-1 rounded shadow-lg border border-secondary overflow-hidden" style="z-index: 1000; max-height: 220px; overflow-y: auto; display: none;"></div>
                                </div>
                            @else
                                <input type="text" name="ship_to_wallet" id="ship_to_wallet" value="{{ old('ship_to_wallet') }}"
                                    class="form-control bg-dark text-light border-secondary font-monospace" placeholder="Адрес кошелька" maxlength="255">
                                <p class="text-secondary small mt-1 mb-0">Список кошельков с ноды недоступен — введите адрес вручную.</p>
                            @endif
                            @error('ship_to_wallet')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="symbol" class="form-label text-light">Символ <span class="text-danger">*</span></label>
                                <input type="text" name="symbol" id="symbol" value="{{ old('symbol') }}" required class="form-control bg-dark text-light border-secondary" placeholder="GND">
                                @error('symbol')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label text-light">Название <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-control bg-dark text-light border-secondary" placeholder="GANIMED Coin">
                                @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="decimals" class="form-label text-light">Decimals</label>
                                <input type="number" name="decimals" id="decimals" value="{{ old('decimals', 18) }}" min="0" max="255" class="form-control bg-dark text-light border-secondary">
                                @error('decimals')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="standard" class="form-label text-light">Стандарт</label>
                                <select name="standard" id="standard" class="form-select bg-dark text-light border-secondary">
                                    <option value="">—</option>
                                    <option value="GND-st1" {{ old('standard') === 'GND-st1' ? 'selected' : '' }}>GND-st1</option>
                                    <option value="erc20" {{ old('standard') === 'erc20' ? 'selected' : '' }}>ERC-20</option>
                                    <option value="trc20" {{ old('standard') === 'trc20' ? 'selected' : '' }}>TRC-20</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="total_supply" class="form-label text-light">Total supply</label>
                            <input type="text" name="total_supply" id="total_supply" value="{{ old('total_supply') }}" class="form-control bg-dark text-light border-secondary" placeholder="1000000000">
                            @error('total_supply')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label text-light">Описание</label>
                            <textarea name="description" id="description" rows="2" class="form-control bg-dark text-light border-secondary">{{ old('description') }}</textarea>
                            @error('description')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="logo_file" class="form-label text-light">Логотип (файл)</label>
                            <input type="file" name="logo_file" id="logo_file" accept="image/*" class="form-control bg-dark text-light border-secondary">
                            <p class="text-secondary small mt-1 mb-0">PNG, JPG до 2 МБ. Или укажите ссылку ниже.</p>
                            @error('logo_file')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="logo_url" class="form-label text-light">Логотип (ссылка)</label>
                            <input type="url" name="logo_url" id="logo_url" value="{{ old('logo_url') }}" class="form-control bg-dark text-light border-secondary" placeholder="https://...">
                            @error('logo_url')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="metadata_extra" class="form-label text-light">Доп. метаданные (JSON)</label>
                            <textarea name="metadata_extra" id="metadata_extra" rows="2" class="form-control bg-dark text-light border-secondary font-monospace small" placeholder='{"website": ""}'>{{ old('metadata_extra') }}</textarea>
                            @error('metadata_extra')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="is_verified" value="0">
                                    <input type="checkbox" name="is_verified" id="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }} class="form-check-input">
                                    <label for="is_verified" class="form-check-label text-light">Верифицирован</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label text-light">Порядок</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="form-control bg-dark text-light border-secondary">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="application/json" id="wallets_data">@json(array_map(fn($w) => ['name' => $w['name'] ?? '', 'address' => $w['address'] ?? ''], $wallets))</script>
    <script>
(function() {
    var input = document.getElementById('token_address');
    var listEl = document.getElementById('token_address_list');
    var dataEl = document.getElementById('token_contracts_data');
    if (!input || !listEl || !dataEl) return;

    var contracts = [];
    try { contracts = JSON.parse(dataEl.textContent || '[]'); } catch (e) {}

    function showList(query) {
        var q = (query || '').trim().toLowerCase();
        listEl.innerHTML = '';
        var shown = 0;
        contracts.forEach(function(c) {
            var name = (c.name || '').toString();
            var addr = (c.address || '').toString();
            if (q && name.toLowerCase().indexOf(q) === -1 && addr.toLowerCase().indexOf(q) === -1) return;
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action bg-dark text-light border-secondary text-start';
            item.textContent = name + ' — ' + addr;
            item.dataset.address = addr;
            item.addEventListener('click', function() {
                input.value = addr;
                listEl.style.display = 'none';
                input.focus();
            });
            listEl.appendChild(item);
            shown++;
        });
        listEl.style.display = shown ? 'block' : 'none';
    }

    function hideList() { setTimeout(function() { listEl.style.display = 'none'; }, 200); }

    input.addEventListener('focus', function() { showList(input.value); });
    input.addEventListener('input', function() { showList(input.value); });
    input.addEventListener('blur', hideList);
    listEl.addEventListener('mousedown', function(e) { e.preventDefault(); });
})();

(function() {
    var input = document.getElementById('ship_to_wallet');
    var listEl = document.getElementById('ship_to_wallet_list');
    var dataEl = document.getElementById('wallets_data');
    if (!input || !listEl || !dataEl) return;

    var wallets = [];
    try { wallets = JSON.parse(dataEl.textContent || '[]'); } catch (e) {}

    function showList(query) {
        var q = (query || '').trim().toLowerCase();
        listEl.innerHTML = '';
        var shown = 0;
        wallets.forEach(function(w) {
            var name = (w.name || '').toString();
            var addr = (w.address || '').toString();
            if (q && name.toLowerCase().indexOf(q) === -1 && addr.toLowerCase().indexOf(q) === -1) return;
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action bg-dark text-light border-secondary text-start';
            item.textContent = name ? name + ' — ' + addr : addr;
            item.dataset.address = addr;
            item.addEventListener('click', function() {
                input.value = addr;
                listEl.style.display = 'none';
                input.focus();
            });
            listEl.appendChild(item);
            shown++;
        });
        listEl.style.display = shown ? 'block' : 'none';
    }

    function hideList() { setTimeout(function() { listEl.style.display = 'none'; }, 200); }

    input.addEventListener('focus', function() { showList(input.value); });
    input.addEventListener('input', function() { showList(input.value); });
    input.addEventListener('blur', hideList);
    listEl.addEventListener('mousedown', function(e) { e.preventDefault(); });
})();
    </script>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Новый перевод</h2>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 36rem;">
            @if(!($gnd_configured ?? false))
                <div class="alert alert-warning">Задайте в .env <code>GND_NODE_URL</code> и <code>GND_ADMIN_SECRET</code> для отправки переводов на ноду.</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <p class="text-secondary mb-4">Перевод с кошелька на кошелёк. Обязательны: отправитель, получатель, токен или монета, сумма. Подтверждение перед отправкой.</p>

                    @php
                        $walletsList = array_map(function($w) { $w = (object)$w; return ['name' => $w->name ?? '', 'address' => $w->address ?? '']; }, $wallets);
                    @endphp
                    <script type="application/json" id="wallets_data">@json($walletsList)</script>
                    <form id="transaction-form" method="POST" action="{{ route('admin.transactions.store') }}" data-confirm="Отправить перевод? Проверьте сумму и адреса.">
                        @csrf
                        <div class="mb-3 position-relative">
                            <label for="from_address" class="form-label text-light">От (кошелёк) <span class="text-danger">*</span></label>
                            <input type="text" name="from_address" id="from_address" value="{{ old('from_address') }}" required
                                class="form-control bg-dark text-light border-secondary font-monospace" placeholder="Введите имя или адрес кошелька..."
                                maxlength="255" autocomplete="off">
                            <div id="from_address_list" class="list-group position-absolute top-100 start-0 end-0 mt-1 rounded shadow-lg border border-secondary overflow-hidden" style="z-index: 1000; max-height: 220px; overflow-y: auto; display: none;"></div>
                            <p class="text-secondary small mt-1 mb-0">Начните вводить имя кошелька или адрес — выберите из списка или вставьте адрес вручную.</p>
                            @error('from_address')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="to_address" class="form-label text-light">Кому (кошелёк) <span class="text-danger">*</span></label>
                            <input type="text" name="to_address" id="to_address" value="{{ old('to_address') }}" required
                                class="form-control bg-dark text-light border-secondary font-monospace" placeholder="Введите имя или адрес кошелька..."
                                maxlength="255" autocomplete="off">
                            <div id="to_address_list" class="list-group position-absolute top-100 start-0 end-0 mt-1 rounded shadow-lg border border-secondary overflow-hidden" style="z-index: 1000; max-height: 220px; overflow-y: auto; display: none;"></div>
                            <p class="text-secondary small mt-1 mb-0">Начните вводить имя кошелька или адрес — выберите из списка или вставьте адрес вручную.</p>
                            @error('to_address')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light">Токен или монета <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mb-2">
                                <label class="form-check-label text-light">
                                    <input type="radio" name="asset_type" value="coin" class="form-check-input" {{ old('asset_type', 'token') === 'coin' ? 'checked' : '' }}> Монета (GND/GANI)
                                </label>
                                <label class="form-check-label text-light">
                                    <input type="radio" name="asset_type" value="token" class="form-check-input" {{ old('asset_type', 'token') !== 'coin' ? 'checked' : '' }}> Токен
                                </label>
                            </div>
                            <div id="coin-field" class="mb-2" style="display: {{ old('asset_type', 'token') === 'coin' ? 'block' : 'none' }};">
                                <select name="coin_symbol" class="form-select bg-dark text-light border-secondary" aria-label="Выбор нативной монеты">
                                    @foreach($coins as $c)
                                        <option value="{{ $c['symbol'] }}" {{ old('coin_symbol') === $c['symbol'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                                    @endforeach
                                </select>
                                <small class="text-secondary d-block mt-1">Нативные монеты GND и GANI — активы протокола L1; перевод выполняется через ноду без отдельного контракта.</small>
                            </div>
                            <div id="token-field" style="display: {{ old('asset_type', 'token') !== 'coin' ? 'block' : 'none' }};">
                                <select name="token_address" class="form-select bg-dark text-light border-secondary" id="token_address_select" aria-label="Выбор контрактного токена" {{ old('asset_type', 'token') !== 'coin' ? 'required' : '' }}>
                                    <option value="">— Выберите токен —</option>
                                    @foreach($tokens as $t)
                                        <option value="{{ $t->token_address }}" {{ old('token_address') === $t->token_address ? 'selected' : '' }}>{{ $t->symbol }} ({{ $t->name }}) — {{ Str::limit($t->token_address, 16) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light">Сумма <span class="text-danger">*</span></label>
                            <input type="text" name="amount" class="form-control bg-dark text-light border-secondary" value="{{ old('amount') }}" placeholder="Целое число (в wei/наименьших единицах)" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-light">Nonce (необязательно)</label>
                            <input type="number" name="nonce" class="form-control bg-dark text-light border-secondary" value="{{ old('nonce') }}" min="0" placeholder="Оставьте пустым для авто">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submit-transaction">Отправить</button>
                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
(function() {
    var wallets = [];
    try { wallets = JSON.parse((document.getElementById('wallets_data') || {}).textContent || '[]'); } catch (e) {}

    function makeWalletList(inputId, listId) {
        var input = document.getElementById(inputId);
        var listEl = document.getElementById(listId);
        if (!input || !listEl) return;
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
    }
    makeWalletList('from_address', 'from_address_list');
    makeWalletList('to_address', 'to_address_list');

    var assetCoin = document.querySelector('input[name="asset_type"][value="coin"]');
    var assetToken = document.querySelector('input[name="asset_type"][value="token"]');
    var coinField = document.getElementById('coin-field');
    var tokenField = document.getElementById('token-field');
    var tokenSelect = document.querySelector('select[name="token_address"]');
    if (assetCoin) assetCoin.addEventListener('change', function() {
        if (coinField) coinField.style.display = 'block';
        if (tokenField) tokenField.style.display = 'none';
        if (tokenSelect) { tokenSelect.removeAttribute('required'); tokenSelect.value = ''; tokenSelect.disabled = true; }
    });
    if (assetToken) assetToken.addEventListener('change', function() {
        if (coinField) coinField.style.display = 'none';
        if (tokenField) tokenField.style.display = 'block';
        if (tokenSelect) { tokenSelect.setAttribute('required', 'required'); tokenSelect.disabled = false; }
    });
    if (document.querySelector('input[name="asset_type"][value="coin"]:checked') && tokenSelect) tokenSelect.disabled = true;
})();
    </script>
    @endpush
</x-app-layout>

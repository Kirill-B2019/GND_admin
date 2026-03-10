<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Деплой токена на ноду</h2>
            <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary btn-sm">← К списку токенов</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 42rem;">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('deploy_result'))
                @php $r = session('deploy_result'); @endphp
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-header border-secondary text-light">Результат выпуска токена</div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary">Адрес контракта</dt>
                            <dd class="col-sm-8 font-monospace text-light">{{ $r['address'] ?? '—' }}</dd>
                            <dt class="col-sm-4 text-secondary">Владелец (owner)</dt>
                            <dd class="col-sm-8 font-monospace text-light">{{ $r['owner'] ?? '—' }}</dd>
                            @if (!empty($r['owner_wallet_created']))
                                <dt class="col-sm-4 text-secondary">Кошелёк создан</dt>
                                <dd class="col-sm-8 text-info">Да, кошелёк создан автоматически. Сохраните адрес владельца для доступа.</dd>
                            @endif
                            <dt class="col-sm-4 text-secondary">Символ</dt>
                            <dd class="col-sm-8 text-light">{{ $r['symbol'] ?? '—' }}</dd>
                            <dt class="col-sm-4 text-secondary">Название</dt>
                            <dd class="col-sm-8 text-light">{{ $r['name'] ?? '—' }}</dd>
                            <dt class="col-sm-4 text-secondary">Total supply</dt>
                            <dd class="col-sm-8 font-monospace text-light">{{ $r['total_supply'] ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
            <div class="card bg-dark border-secondary">
                <div class="card-header border-secondary text-light">Параметры токена</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tokens.deploy.submit') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label text-light">Название <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-control bg-dark text-light border-secondary" placeholder="My Token">
                                @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="symbol" class="form-label text-light">Символ <span class="text-danger">*</span></label>
                                <input type="text" name="symbol" id="symbol" value="{{ old('symbol') }}" required class="form-control bg-dark text-light border-secondary" placeholder="MTK">
                                @error('symbol')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="decimals" class="form-label text-light">Decimals</label>
                                <input type="number" name="decimals" id="decimals" value="{{ old('decimals', 18) }}" min="0" max="255" class="form-control bg-dark text-light border-secondary">
                                @error('decimals')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="total_supply" class="form-label text-light">Total supply <span class="text-danger">*</span></label>
                                <input type="text" name="total_supply" id="total_supply" value="{{ old('total_supply') }}" required class="form-control bg-dark text-light border-secondary" placeholder="1000000000000000000000000">
                                @error('total_supply')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="deploy_wallet" class="form-label text-light">Кошелёк деплоя (оплачивает газ)</label>
                            @if(count($wallets) > 0)
                                <select name="deploy_wallet" id="deploy_wallet" class="form-select bg-dark text-light border-secondary">
                                    <option value="">— Выберите кошелёк деплоя (или оставьте пустым для владельца/автосоздания)</option>
                                    @foreach($wallets as $w)
                                        <option value="{{ $w['address'] ?? '' }}" {{ old('deploy_wallet') === ($w['address'] ?? '') ? 'selected' : '' }}>
                                            {{ ($w['name'] ?? '') ? $w['name'] . ' — ' : '' }}{{ $w['address'] ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="deploy_wallet" id="deploy_wallet" value="{{ old('deploy_wallet') }}" class="form-control bg-dark text-light border-secondary font-monospace" placeholder="GND... или оставьте пустым">
                            @endif
                            <p class="text-secondary small mt-1 mb-0">Если оставить поле пустым и указать владельца ниже — деплой и владение будут от одного кошелька. Если оба поля пустые — нода создаст новый кошелёк для владельца и деплоя.</p>
                            @error('deploy_wallet')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="owner" class="form-label text-light">Владелец токена (owner, опционально)</label>
                            @if(count($wallets) > 0)
                                <select name="owner" id="owner" class="form-select bg-dark text-light border-secondary">
                                    <option value="">— Не привязывать владельца (системный токен)</option>
                                    @foreach($wallets as $w)
                                        <option value="{{ $w['address'] ?? '' }}" {{ old('owner') === ($w['address'] ?? '') ? 'selected' : '' }}>
                                            {{ ($w['name'] ?? '') ? $w['name'] . ' — ' : '' }}{{ $w['address'] ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="owner" id="owner" value="{{ old('owner') }}" class="form-control bg-dark text-light border-secondary font-monospace" placeholder="GND... или оставьте пустым">
                            @endif
                            <p class="text-secondary small mt-1 mb-0">Оставьте пустым, чтобы токен считался системным (без привязки к владельцу в админке).</p>
                            @error('owner')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Выпустить токен на ноде</button>
                            <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

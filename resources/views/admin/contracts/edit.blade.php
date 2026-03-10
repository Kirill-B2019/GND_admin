<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Редактировать контракт</h2>
            <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-outline-secondary btn-sm">← К контракту</a>
            <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary btn-sm">К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 42rem;">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.contracts.update', $contract) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="name" class="form-label text-light">Название</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $contract->name) }}" required class="form-control bg-dark text-light border-secondary">
                            @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="standard" class="form-label text-light">Стандарт</label>
                            <select name="standard" id="standard" class="form-select bg-dark text-light border-secondary">
                                <option value="GND-st1" {{ old('standard', $contract->standard) === 'GND-st1' ? 'selected' : '' }}>GND-st1</option>
                                <option value="erc20" {{ old('standard', $contract->standard) === 'erc20' ? 'selected' : '' }}>ERC-20</option>
                                <option value="trc20" {{ old('standard', $contract->standard) === 'trc20' ? 'selected' : '' }}>TRC-20</option>
                                <option value="custom" {{ old('standard', $contract->standard) === 'custom' ? 'selected' : '' }}>custom</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label text-light">Адрес (в сети)</label>
                            <input type="text" name="address" id="address" value="{{ old('address', $contract->address) }}" class="form-control bg-dark text-light border-secondary" placeholder="0x..." {{ $contract->isDeployed() ? 'readonly' : '' }}>
                            @error('address')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label text-light">Описание</label>
                            <textarea name="description" id="description" rows="4" class="form-control bg-dark text-light border-secondary">{{ old('description', $contract->description) }}</textarea>
                            @error('description')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="source_code" class="form-label text-light">Исходный код (Solidity)</label>
                            @if(!empty($templates))
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <select id="template_select" class="form-select form-select-sm bg-dark text-light border-secondary" style="max-width: 16rem;">
                                    <option value="">— Выберите шаблон —</option>
                                    @foreach($templates as $f => $label)
                                        <option value="{{ $f }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" id="load_template_btn" class="btn btn-outline-secondary btn-sm">Загрузить шаблон</button>
                            </div>
                            @endif
                            <textarea name="source_code" id="source_code" rows="10" class="form-control bg-dark text-light border-secondary font-monospace small" placeholder="pragma solidity ^0.8.16; ...">{{ old('source_code', $contract->source_code) }}</textarea>
                            @error('source_code')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="accordion accordion-flush mb-4" id="accordionEditExtra">
                            <div class="accordion-item bg-dark border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-dark text-light border-secondary py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEditExtra">Лицензия, метаданные, кошельки</button>
                                </h2>
                                <div id="collapseEditExtra" class="accordion-collapse collapse" data-bs-parent="#accordionEditExtra">
                                    <div class="accordion-body">
                                        <div class="mb-3">
                                            <label for="metadata_license" class="form-label text-light small">Лицензия</label>
                                            <input type="text" name="metadata_license" id="metadata_license" value="{{ old('metadata_license', $contract->metadata_json['license'] ?? '') }}" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="MIT, Apache-2.0">
                                        </div>
                                        <div class="mb-3">
                                            <label for="metadata_extra" class="form-label text-light small">Доп. метаданные (JSON)</label>
                                            @php
                                                $meta = $contract->metadata_json ?? [];
                                                unset($meta['name'], $meta['description'], $meta['standard'], $meta['license']);
                                                $metaExtra = empty($meta) ? '' : json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                            @endphp
                                            <textarea name="metadata_extra" id="metadata_extra" rows="2" class="form-control form-control-sm bg-dark text-light border-secondary font-monospace small" placeholder='{"version": "1.0"}'>{{ old('metadata_extra', $metaExtra) }}</textarea>
                                        </div>
                                        @if(!empty($wallets))
                                        <div class="mb-0">
                                            <label for="managed_wallets" class="form-label text-light small">Управляемые кошельки</label>
                                            <select name="managed_wallets[]" id="managed_wallets" multiple class="form-select form-select-sm bg-dark text-light border-secondary">
                                                @php $selected = $contract->managedWallets->pluck('wallet_address')->toArray(); @endphp
                                                @foreach($wallets as $w)
                                                    @php $addr = is_array($w) ? ($w['address'] ?? '') : ($w->address ?? ''); $nm = is_array($w) ? ($w['name'] ?? '') : ($w->name ?? ''); @endphp
                                                    @if($addr)<option value="{{ $addr }}" {{ in_array($addr, $selected) ? 'selected' : '' }}>{{ $nm ? $nm . ' — ' . $addr : $addr }}</option>@endif
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if(!empty($templates))
    <script>
        document.getElementById('load_template_btn').addEventListener('click', function () {
            const sel = document.getElementById('template_select');
            const tpl = sel?.value;
            if (!tpl) return;
            fetch('{{ route("admin.contracts.template") }}?template=' + encodeURIComponent(tpl))
                .then(r => r.json())
                .then(data => {
                    if (data.source_code) {
                        document.getElementById('source_code').value = data.source_code;
                    } else {
                        alert(data.error || 'Ошибка загрузки');
                    }
                })
                .catch(() => alert('Ошибка загрузки шаблона'));
        });
    </script>
    @endif
</x-app-layout>

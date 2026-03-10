<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Создать контракт</h2>
            <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 42rem;">
            @if ($errors->any())
                <div class="alert alert-danger">Исправьте ошибки в форме.</div>
            @endif
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.contracts.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label text-light">Название</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-control bg-dark text-light border-secondary">
                            @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="standard" class="form-label text-light">Стандарт</label>
                            <select name="standard" id="standard" class="form-select bg-dark text-light border-secondary">
                                <option value="GND-st1" {{ old('standard', 'GND-st1') === 'GND-st1' ? 'selected' : '' }}>GND-st1</option>
                                <option value="erc20" {{ old('standard') === 'erc20' ? 'selected' : '' }}>ERC-20</option>
                                <option value="trc20" {{ old('standard') === 'trc20' ? 'selected' : '' }}>TRC-20</option>
                                <option value="custom" {{ old('standard') === 'custom' ? 'selected' : '' }}>custom</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label text-light">Адрес (если уже задеплоен)</label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}" class="form-control bg-dark text-light border-secondary" placeholder="0x...">
                            @error('address')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label text-light">Описание</label>
                            <textarea name="description" id="description" rows="2" class="form-control bg-dark text-light border-secondary">{{ old('description') }}</textarea>
                            @error('description')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="metadata_license" class="form-label text-light">Лицензия (метаданные)</label>
                            <input type="text" name="metadata_license" id="metadata_license" value="{{ old('metadata_license') }}" class="form-control bg-dark text-light border-secondary" placeholder="MIT, Apache-2.0, ...">
                        </div>
                        <div class="mb-3">
                            <label for="metadata_extra" class="form-label text-light">Доп. метаданные (JSON)</label>
                            <textarea name="metadata_extra" id="metadata_extra" rows="3" class="form-control bg-dark text-light border-secondary font-monospace small" placeholder='{"author": "", "version": "1.0"}'>{{ old('metadata_extra') }}</textarea>
                            <p class="text-secondary small mt-1 mb-0">Произвольные пары ключ-значение в формате JSON. Сохраняются на нашей стороне и передаются на ноду при деплое.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light">Исходный код (Solidity)</label>
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
                            <input type="file" name="source_file" accept=".sol" class="form-control bg-dark text-light border-secondary">
                            <p class="text-secondary small mt-1 mb-0">или вставьте код в поле ниже</p>
                        </div>
                        <div class="mb-3">
                            <textarea name="source_code" id="source_code" rows="12" class="form-control bg-dark text-light border-secondary font-monospace small" placeholder="// SPDX-License-Identifier: MIT&#10;pragma solidity ^0.8.0;&#10;...">{{ old('source_code') }}</textarea>
                            @error('source_code')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        @if(!empty($wallets))
                        <div class="mb-4">
                            <label for="managed_wallets" class="form-label text-light">Управляемые кошельки</label>
                            <select name="managed_wallets[]" id="managed_wallets" multiple class="form-select bg-dark text-light border-secondary">
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
                            <p class="text-secondary small mt-1 mb-0">Выберите заранее созданные кошельки, которыми управляет этот контракт</p>
                        </div>
                        @endif
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Отмена</a>
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

<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Редактировать токен">
            <x-slot name="actions">
                <a href="{{ route('admin.tokens.show', $token) }}" class="btn btn-outline-secondary btn-sm">← К токену</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 42rem;">
            <x-admin.breadcrumbs :items="[
                ['label' => 'Обзор', 'url' => route('admin.dashboard')],
                ['label' => 'Токены', 'url' => route('admin.tokens.index')],
                ['label' => $token->symbol ?? 'Токен', 'url' => route('admin.tokens.show', $token)],
                ['label' => 'Редактирование'],
            ]" />
            @if ($errors->any())
                <div class="alert alert-danger">Исправьте ошибки в форме.</div>
            @endif
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tokens.update', $token) }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="token_address" class="form-label text-light">Адрес контракта токена <span class="text-danger">*</span></label>
                            <input type="text" name="token_address" id="token_address" value="{{ old('token_address', $token->token_address) }}" required class="form-control bg-dark text-light border-secondary font-monospace">
                            @error('token_address')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="symbol" class="form-label text-light">Символ <span class="text-danger">*</span></label>
                                <input type="text" name="symbol" id="symbol" value="{{ old('symbol', $token->symbol) }}" required class="form-control bg-dark text-light border-secondary">
                                @error('symbol')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label text-light">Название <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $token->name) }}" required class="form-control bg-dark text-light border-secondary">
                                @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="decimals" class="form-label text-light">Decimals</label>
                                <input type="number" name="decimals" id="decimals" value="{{ old('decimals', $token->decimals) }}" min="0" max="255" class="form-control bg-dark text-light border-secondary">
                                @error('decimals')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="standard" class="form-label text-light">Стандарт</label>
                                <select name="standard" id="standard" class="form-select bg-dark text-light border-secondary">
                                    <option value="">—</option>
                                    <option value="GND-st1" {{ old('standard', $token->standard) === 'GND-st1' ? 'selected' : '' }}>GND-st1</option>
                                    <option value="erc20" {{ old('standard', $token->standard) === 'erc20' ? 'selected' : '' }}>ERC-20</option>
                                    <option value="trc20" {{ old('standard', $token->standard) === 'trc20' ? 'selected' : '' }}>TRC-20</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="total_supply" class="form-label text-light">Total supply</label>
                            <input type="text" name="total_supply" id="total_supply" value="{{ old('total_supply', $token->total_supply) }}" class="form-control bg-dark text-light border-secondary">
                            @error('total_supply')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label text-light">Описание</label>
                            <textarea name="description" id="description" rows="2" class="form-control bg-dark text-light border-secondary">{{ old('description', $token->description) }}</textarea>
                            @error('description')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            @if($logo = $token->logoUrl())
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="{{ $logo }}" alt="" class="rounded" style="width: 48px; height: 48px; object-fit: contain; background: #2d3338;">
                                    <span class="text-secondary small">Текущий логотип</span>
                                </div>
                            @endif
                            <div class="form-check mb-2">
                                <input type="hidden" name="remove_logo" value="0">
                                <input type="checkbox" name="remove_logo" id="remove_logo" value="1" {{ old('remove_logo') ? 'checked' : '' }} class="form-check-input">
                                <label for="remove_logo" class="form-check-label text-light">Удалить логотип</label>
                            </div>
                            <label for="logo_file" class="form-label text-light">Новый логотип (файл)</label>
                            <input type="file" name="logo_file" id="logo_file" accept="image/*" class="form-control bg-dark text-light border-secondary">
                            <p class="text-secondary small mt-1 mb-0">PNG, JPG до 2 МБ. Или укажите ссылку ниже.</p>
                            @error('logo_file')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="logo_url" class="form-label text-light">Логотип (ссылка)</label>
                            <input type="url" name="logo_url" id="logo_url" value="{{ old('logo_url', $token->logo_url) }}" class="form-control bg-dark text-light border-secondary" placeholder="https://...">
                            @error('logo_url')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="metadata_extra" class="form-label text-light">Доп. метаданные (JSON)</label>
                            @php
                                $meta = $token->metadata_json ?? [];
                                $metaStr = empty($meta) ? '' : json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            @endphp
                            <textarea name="metadata_extra" id="metadata_extra" rows="2" class="form-control bg-dark text-light border-secondary font-monospace small">{{ old('metadata_extra', $metaStr) }}</textarea>
                            @error('metadata_extra')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="is_verified" value="0">
                                    <input type="checkbox" name="is_verified" id="is_verified" value="1" {{ old('is_verified', $token->is_verified) ? 'checked' : '' }} class="form-check-input">
                                    <label for="is_verified" class="form-check-label text-light">Верифицирован</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label text-light">Порядок</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $token->sort_order) }}" class="form-control bg-dark text-light border-secondary">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.tokens.show', $token) }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

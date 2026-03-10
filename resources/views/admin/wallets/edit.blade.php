<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Имя и роль кошелька</h2>
            <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 32rem;">
            @if (isset($gnd_configured) && !$gnd_configured)
                <div class="alert alert-warning mb-4">Задайте <code>GND_ADMIN_SECRET</code> в .env для изменения имени и роли на ноде.</div>
            @endif
            @error('gnd')
                <div class="alert alert-danger mb-4">{{ $message }}</div>
            @enderror
            <div class="card bg-dark border-secondary mb-3">
                <div class="card-body">
                    <p class="text-secondary small mb-0">Адрес: <code class="text-light">{{ $address }}</code></p>
                </div>
            </div>
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.wallets.update', ['address' => $address]) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="name" class="form-label text-light">Имя (необязательно)</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $current_name ?? '') }}" placeholder="Например: Treasury" class="form-control bg-dark text-light border-secondary">
                            @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label text-light">Роль (необязательно)</label>
                            <input type="text" name="role" id="role" value="{{ old('role', $current_role ?? '') }}" placeholder="validator, treasury, fee_collector" class="form-control bg-dark text-light border-secondary">
                            @error('role')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="account_id" class="form-label text-light">Аккаунт (необязательно)</label>
                            <select name="account_id" id="account_id" class="form-select bg-dark text-light border-secondary">
                                <option value="">— Без аккаунта</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ old('account_id', $current_account_id ?? null) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                @endforeach
                            </select>
                            @error('account_id')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" @if(isset($gnd_configured) && !$gnd_configured) disabled @endif>Сохранить</button>
                            <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

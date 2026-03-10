<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Создать ключ приложения</h2>
            <a href="{{ route('admin.keys.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 32rem;">
            @if (isset($gnd_configured) && !$gnd_configured)
                <div class="alert alert-warning mb-4">Задайте в .env переменные <code>GND_NODE_URL</code> и <code>GND_ADMIN_SECRET</code> для создания ключа на ноде GND.</div>
            @endif
            @error('gnd')
                <div class="alert alert-danger mb-4">{{ $message }}</div>
            @enderror
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.keys.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label text-light">Название</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Например: Laravel Backend" class="form-control bg-dark text-light border-secondary">
                            @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="expires_at" class="form-label text-light">Истекает (необязательно)</label>
                            <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" class="form-control bg-dark text-light border-secondary">
                            @error('expires_at')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" @if(isset($gnd_configured) && !$gnd_configured) disabled @endif>Создать ключ</button>
                            <a href="{{ route('admin.keys.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

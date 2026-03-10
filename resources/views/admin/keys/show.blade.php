<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Ключ создан</h2>
            <a href="{{ route('admin.keys.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 32rem;">
            @if (session('status'))
                <div class="alert alert-warning">{{ session('status') }}</div>
            @endif
            @if (!empty($new_key_plain))
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-body">
                        <p class="text-secondary small mb-2">Скопируйте ключ и сохраните в безопасном месте. Он больше не будет показан.</p>
                        <div class="bg-dark border border-secondary rounded p-3 font-monospace small text-break user-select-all" id="key-value">{{ $new_key_plain }}</div>
                        <p class="mt-3 text-secondary small mb-0">Используйте этот ключ в заголовке <code>X-API-Key</code> при запросах к ноде GND.</p>
                    </div>
                </div>
            @else
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-body text-secondary">
                        Ключ уже был показан при создании. Для нового ключа создайте его заново в разделе «Ключи приложений».
                    </div>
                </div>
            @endif
            <a href="{{ route('admin.keys.index') }}" class="btn btn-primary">К списку ключей</a>
        </div>
    </div>
</x-app-layout>

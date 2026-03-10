<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Создать кошелёк</h2>
            <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 32rem;">
            @error('gnd')
                <div class="alert alert-danger mb-4">{{ $message }}</div>
            @enderror
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    @if(!($node_configured ?? false))
                        <p class="text-warning mb-0">Задайте в .env переменные <code>GND_NODE_URL</code> и <code>GND_API_KEY</code>, затем обновите страницу.</p>
                    @else
                        <p class="text-secondary mb-4">Кошелёк будет создан на ноде GND ({{ config('services.gnd.node_url') }}) и появится в списке кошельков.</p>
                        <form method="POST" action="{{ route('admin.wallets.store') }}">
                            @csrf
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Создать кошелёк</button>
                                <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary">Отмена</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

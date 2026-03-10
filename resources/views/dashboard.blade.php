<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-light">Панель</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <p class="text-light mb-0">Вы вошли в систему.</p>
                    @if(auth()->user()->isAdmin())
                        <p class="mt-3 mb-0">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm">Перейти в админ-панель →</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

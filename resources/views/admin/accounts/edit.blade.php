<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Редактировать аккаунт: {{ $account->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.accounts.show', $account) }}" class="btn btn-outline-secondary btn-sm">← К аккаунту</a>
                <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary btn-sm">К списку</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 32rem;">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.accounts.update', $account) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="name" class="form-label text-light">Название</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $account->name) }}" required class="form-control bg-dark text-light border-secondary">
                            @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="description" class="form-label text-light">Описание (необязательно)</label>
                            <textarea name="description" id="description" rows="3" class="form-control bg-dark text-light border-secondary">{{ old('description', $account->description) }}</textarea>
                            @error('description')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.accounts.show', $account) }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

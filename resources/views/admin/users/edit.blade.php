<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Роль пользователя: ' . $user->name">
            <x-slot name="actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 32rem;">
            <x-admin.breadcrumbs :items="[
                ['label' => 'Обзор', 'url' => route('admin.dashboard')],
                ['label' => 'Пользователи', 'url' => route('admin.users.index')],
                ['label' => $user->name],
            ]" />
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <p class="text-secondary small mb-4">{{ $user->email }}</p>
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="role" class="form-label text-light">Роль</label>
                            <select name="role" id="role" class="form-select bg-dark text-light border-secondary">
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>user</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
                            </select>
                            @error('role')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

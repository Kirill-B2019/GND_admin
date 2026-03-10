<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Создать пользователя</h2>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 32rem;">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label text-light">Имя</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-control bg-dark text-light border-secondary">
                            @error('name')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label text-light">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="form-control bg-dark text-light border-secondary">
                            @error('email')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-light">Пароль</label>
                            <input type="password" name="password" id="password" required autocomplete="new-password" class="form-control bg-dark text-light border-secondary">
                            @error('password')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label text-light">Подтверждение пароля</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="form-control bg-dark text-light border-secondary">
                        </div>
                        <div class="mb-4">
                            <label for="role" class="form-label text-light">Роль</label>
                            <select name="role" id="role" class="form-select bg-dark text-light border-secondary">
                                <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>user</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>admin</option>
                            </select>
                            @error('role')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

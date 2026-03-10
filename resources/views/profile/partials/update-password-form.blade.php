<section>
    <header class="mb-4">
        <h2 class="h6 text-light">Смена пароля</h2>
        <p class="text-secondary small mb-0">Используйте надёжный случайный пароль.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <x-input-label for="update_password_current_password" value="Текущий пароль" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="w-100" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password" value="Новый пароль" />
            <x-text-input id="update_password_password" name="password" type="password" class="w-100" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password_confirmation" value="Подтверждение пароля" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="w-100" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="d-flex align-items-center gap-2">
            <x-primary-button>Сохранить</x-primary-button>
            @if (session('status') === 'password-updated')
                <span class="text-success small" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)">Сохранено.</span>
            @endif
        </div>
    </form>
</section>

<x-guest-layout>
    <p class="text-secondary small mb-4">
        Это защищённая страница. Подтвердите пароль для продолжения.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="password" value="Пароль" />
            <x-text-input id="password" class="w-100" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button>Подтвердить</x-primary-button>
    </form>
</x-guest-layout>

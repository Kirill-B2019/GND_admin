<x-guest-layout>
    <p class="text-secondary small mb-4">
        Забыли пароль? Укажите email — мы отправим ссылку для сброса пароля.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="w-100" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a class="text-primary text-decoration-none small" href="{{ route('login') }}">Вернуться к входу</a>
            <x-primary-button>Отправить ссылку для сброса пароля</x-primary-button>
        </div>
    </form>
</x-guest-layout>

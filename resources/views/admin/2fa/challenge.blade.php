<x-guest-layout>
    <div class="mb-3">
        <h5 class="text-light">Двухфакторная аутентификация</h5>
        <p class="text-secondary small">Введите код из приложения-аутентификатора.</p>
    </div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.2fa.verify') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="code" value="Код" />
            <x-text-input
                id="code"
                class="w-100 text-center"
                type="text"
                name="code"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="6"
                autocomplete="one-time-code"
                autofocus
                placeholder="000000"
            />
            <x-input-error :messages="$errors->get('code')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>Продолжить</x-primary-button>
        </div>
    </form>

    <div class="mt-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link link-secondary p-0 small">Выйти</button>
        </form>
    </div>
</x-guest-layout>

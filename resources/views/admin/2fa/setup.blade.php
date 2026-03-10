<x-guest-layout>
    <div class="mb-3">
        <h5 class="text-light">Настройка двухфакторной аутентификации</h5>
        <p class="text-secondary small">Отсканируйте QR-код в приложении (Google Authenticator, Authy и т.п.) или введите секрет вручную. Затем введите полученный код.</p>
    </div>

    <div class="mb-3 d-flex justify-content-center">
        <img src="{{ $qrDataUri }}" alt="QR-код 2FA" width="200" height="200" />
    </div>

    <div class="mb-3">
        <x-input-label value="Секрет (если не сканируете QR)" />
        <code class="d-block bg-dark border border-secondary rounded p-2 small text-break text-secondary">{{ $secret }}</code>
    </div>

    <form method="POST" action="{{ route('admin.2fa.confirm-setup') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="code" value="Код из приложения" />
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
            <x-primary-button>Включить 2FA</x-primary-button>
        </div>
    </form>

    <div class="mt-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link link-secondary p-0 small">Выйти</button>
        </form>
    </div>
</x-guest-layout>

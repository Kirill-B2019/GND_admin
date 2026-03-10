<x-guest-layout>
    <p class="text-secondary small mb-4">
        Подтвердите email: перейдите по ссылке из письма. Если письмо не пришло — мы отправим ещё раз.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small mb-4">
            Новая ссылка для подтверждения отправлена на ваш email.
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
            @csrf
            <x-primary-button>Отправить письмо повторно</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Выход</button>
        </form>
    </div>
</x-guest-layout>

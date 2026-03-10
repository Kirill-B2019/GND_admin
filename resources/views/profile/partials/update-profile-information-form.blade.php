<section>
    <header class="mb-4">
        <h2 class="h6 text-light">Данные профиля</h2>
        <p class="text-secondary small mb-0">Измените имя и email.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" value="Имя" />
            <x-text-input id="name" name="name" type="text" class="w-100" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="w-100" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="text-secondary small mt-2 mb-0">
                    Email не подтверждён.
                    <button form="send-verification" type="submit" class="btn btn-link btn-sm p-0 align-baseline">Отправить письмо повторно</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="text-success small mt-2 mb-0">Новая ссылка для подтверждения отправлена на ваш email.</p>
                @endif
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <x-primary-button>Сохранить</x-primary-button>
            @if (session('status') === 'profile-updated')
                <span class="text-success small" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)">Сохранено.</span>
            @endif
        </div>
    </form>
</section>

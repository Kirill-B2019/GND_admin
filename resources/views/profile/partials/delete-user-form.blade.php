<section>
    <header class="mb-4">
        <h2 class="h6 text-light">Удаление аккаунта</h2>
        <p class="text-secondary small mb-3">После удаления все данные будут удалены безвозвратно. Сохраните нужные данные заранее.</p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Удалить аккаунт</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4">
            @csrf
            @method('delete')

            <h2 class="h6 text-light mb-2">Вы уверены, что хотите удалить аккаунт?</h2>
            <p class="text-secondary small mb-4">Все данные будут удалены безвозвратно. Введите пароль для подтверждения.</p>

            <div class="mb-4">
                <x-input-label for="password" value="Пароль" class="visually-hidden" />
                <x-text-input id="password" name="password" type="password" class="w-100" placeholder="Пароль" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="d-flex justify-content-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Отмена</x-secondary-button>
                <x-danger-button>Удалить аккаунт</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>

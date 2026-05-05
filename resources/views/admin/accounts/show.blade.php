<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Аккаунт: ' . $account->name">
            <x-slot name="actions">
                <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
                <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-outline-primary btn-sm">Изменить</a>
                <form action="{{ route('admin.accounts.destroy', $account) }}" method="post" class="d-inline" data-confirm="Удалить аккаунт? Кошельки будут отвязаны.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                </form>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <x-admin.breadcrumbs :items="[
                ['label' => 'Обзор', 'url' => route('admin.dashboard')],
                ['label' => 'Аккаунты', 'url' => route('admin.accounts.index')],
                ['label' => $account->name],
            ]" />
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary">
                    <h3 class="h6 mb-0 text-light">Данные аккаунта</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-secondary">ID</dt>
                        <dd class="col-sm-9 text-light">{{ $account->id }}</dd>
                        <dt class="col-sm-3 text-secondary">Название</dt>
                        <dd class="col-sm-9 text-light">{{ $account->name }}</dd>
                        @if($account->description)
                            <dt class="col-sm-3 text-secondary">Описание</dt>
                            <dd class="col-sm-9 text-light">{{ $account->description }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card bg-dark border-secondary">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <h3 class="h6 mb-0 text-light">Кошельки ({{ $account->wallets->count() }})</h3>
                    <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-primary btn-sm">Привязать кошелёк</a>
                </div>
                <div class="card-body">
                    @if($account->wallets->isEmpty())
                        <p class="text-secondary mb-0">К аккаунту пока не привязан ни один кошелёк. Перейдите в раздел «Кошельки», откройте кошелёк и выберите этот аккаунт при редактировании.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Адрес</th>
                                        <th>Signer ID</th>
                                        <th class="text-end">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($account->wallets as $wallet)
                                        <tr>
                                            <td class="font-monospace small">
                                                <a href="{{ route('admin.wallets.show', ['address' => $wallet->address]) }}" class="text-primary text-decoration-none">{{ $wallet->address }}</a>
                                            </td>
                                            <td class="text-secondary small">{{ $wallet->signer_wallet_id ?? '—' }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.wallets.show', ['address' => $wallet->address]) }}" class="btn btn-outline-primary btn-sm">Подробнее</a>
                                                <a href="{{ route('admin.wallets.edit', ['address' => $wallet->address]) }}" class="btn btn-outline-secondary btn-sm">Изменить</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

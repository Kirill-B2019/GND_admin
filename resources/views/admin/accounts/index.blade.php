<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Аккаунты</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary btn-sm">Создать аккаунт</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card bg-dark border-secondary">
                <div class="table-responsive table-mobile-wrap">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Описание</th>
                                <th>Кошельков</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accounts as $account)
                                <tr>
                                    <td>{{ $account->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.accounts.show', $account) }}" class="text-primary text-decoration-none">{{ $account->name }}</a>
                                    </td>
                                    <td class="text-secondary small">{{ Str::limit($account->description, 60) ?: '—' }}</td>
                                    <td>{{ $account->wallets_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.accounts.show', $account) }}" class="btn btn-outline-primary btn-sm me-1">Подробнее</a>
                                        <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-outline-secondary btn-sm me-1">Изменить</a>
                                        <form action="{{ route('admin.accounts.destroy', $account) }}" method="post" class="d-inline" data-confirm="Удалить аккаунт? Кошельки будут отвязаны.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Нет аккаунтов. Создайте аккаунт и привяжите к нему кошельки в разделе «Кошельки».</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-dark border-secondary">{{ $accounts->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Контракты</h2>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <form action="{{ route('admin.contracts.sync-from-node') }}" method="post" class="d-inline" data-confirm="Запросить с ноды id контрактов по адресу и сохранить? Контракты без адреса будут пропущены.">
                    @csrf
                    <button type="submit" class="btn btn-outline-info btn-sm">Синхронизировать с нодой</button>
                </form>
                <form action="{{ route('admin.contracts.sync-abi-to-node') }}" method="post" class="d-inline" data-confirm="Отправить локальный ABI на ноду для всех контрактов с адресом и ABI (исправить abi = NULL в gnd_db)?">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Обновить ABI на ноде (все)</button>
                </form>
                <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary btn-sm">Создать контракт</a>
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
                                <th>Название</th>
                                <th>Статус</th>
                                <th>Адрес</th>
                                <th>ID на ноде</th>
                                <th>Кошельки</th>
                                <th>Описание</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contracts as $contract)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.contracts.show', $contract) }}" class="text-primary text-decoration-none">{{ $contract->name }}</a>
                                    </td>
                                    <td>
                                        @if($contract->status === 'deployed' || $contract->status === 'verified')
                                            <span class="badge bg-success">{{ $contract->status }}</span>
                                        @elseif($contract->status === 'analyzed')
                                            <span class="badge bg-info">{{ $contract->status }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $contract->status ?? 'draft' }}</span>
                                        @endif
                                    </td>
                                    <td class="font-monospace small text-secondary">{{ $contract->address ?? '—' }}</td>
                                    <td class="text-secondary">{{ $contract->node_contract_id ?? '—' }}</td>
                                    <td class="text-secondary">{{ $contract->managed_wallets_count ?? 0 }}</td>
                                    <td class="text-secondary small">{{ Str::limit($contract->description, 60) ?: '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-outline-primary btn-sm me-1">Подробнее</a>
                                        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-outline-secondary btn-sm me-1">Изменить</a>
                                        <form action="{{ route('admin.contracts.destroy', $contract) }}" method="post" class="d-inline" data-confirm="Удалить контракт?">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">Нет контрактов. <a href="{{ route('admin.contracts.create') }}" class="text-primary">Создать</a></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($contracts->hasPages())
                    <div class="card-footer border-secondary">
                        {{ $contracts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

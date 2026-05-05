<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Кошельки">
            <x-slot name="actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <a href="{{ route('admin.wallets.create') }}" class="btn btn-primary btn-sm">Создать кошелёк</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <x-admin.breadcrumbs :items="[
                ['label' => 'Обзор', 'url' => route('admin.dashboard')],
                ['label' => 'Кошельки'],
            ]" />
            @if (isset($gnd_error) && $gnd_error)
                @php
                    $gndParsed = \App\Support\GndNodeError::parse($gnd_error);
                @endphp
                <x-admin.alert type="danger" title="Ошибка ноды GND">
                    <p class="mb-1">{{ $gndParsed['user_message'] }}</p>
                    @if(!empty($gndParsed['hint']))
                        <p class="small mb-2 mb-md-3">{{ $gndParsed['hint'] }}</p>
                    @endif
                    @if($gndParsed['raw'] !== '')
                        <details class="small mb-0">
                            <summary class="text-secondary">Технические детали</summary>
                            <pre class="mt-2 mb-0 small text-break">{{ $gndParsed['raw'] }}</pre>
                        </details>
                    @endif
                </x-admin.alert>
            @endif
            @if (isset($gnd_configured) && !$gnd_configured)
                <x-admin.alert type="warning" title="Нода не подключена">
                    Задайте в .env переменные <code>GND_NODE_URL</code> и <code>GND_ADMIN_SECRET</code> для просмотра списка кошельков.
                </x-admin.alert>
            @endif
            <div class="card bg-dark border-secondary">
                <div class="table-responsive table-mobile-wrap">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Адрес</th>
                                <th scope="col">Имя</th>
                                <th scope="col">Роль</th>
                                <th scope="col">Аккаунт</th>
                                <th scope="col">Статус</th>
                                <th scope="col">Signer ID</th>
                                <th scope="col">Создан</th>
                                <th scope="col" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($wallets as $wallet)
                                @php $wallet = (object) $wallet; @endphp
                                <tr>
                                    <td class="font-monospace small">
                                        @if(!empty($wallet->address))
                                            <a href="{{ route('admin.wallets.show', ['address' => $wallet->address]) }}" class="text-primary text-decoration-none">{{ $wallet->address }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-secondary">{{ $wallet->name ?? '—' }}</td>
                                    <td>
                                        @if(!empty($wallet->role))
                                            <span class="badge bg-info">{{ $wallet->role }}</span>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $local = $accountByAddress->get($wallet->address ?? ''); @endphp
                                        @if($local && $local->account)
                                            <a href="{{ route('admin.accounts.show', $local->account) }}" class="text-primary text-decoration-none">{{ $local->account->name }}</a>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($wallet->blocked))
                                            <span class="badge bg-warning text-dark">Заблокирован</span>
                                        @else
                                            <span class="badge bg-success">Активен</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary small">{{ $wallet->signer_wallet_id ?? '—' }}</td>
                                    <td class="text-secondary">
                                        @if(!empty($wallet->created_at))
                                            {{ \Carbon\Carbon::parse($wallet->created_at)->format('d.m.Y H:i') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($gnd_configured ?? false)
                                            <a href="{{ route('admin.wallets.show', ['address' => $wallet->address ?? '']) }}" class="btn btn-outline-primary btn-sm me-1">Подробнее</a>
                                            <a href="{{ route('admin.wallets.edit', ['address' => $wallet->address ?? '']) }}" class="btn btn-outline-secondary btn-sm me-1">Имя/роль</a>
                                            @if(!empty($wallet->signer_wallet_id))
                                                @if(!empty($wallet->blocked))
                                                    <form action="{{ route('admin.wallets.enable', ['address' => $wallet->address]) }}" method="post" class="d-inline" data-confirm="Разблокировать подписание для этого кошелька?">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm me-1">Разблокировать</button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.wallets.disable', ['address' => $wallet->address]) }}" method="post" class="d-inline" data-confirm="Заблокировать подписание кошелька?">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning btn-sm me-1">Блокировать</button>
                                                    </form>
                                                @endif
                                            @endif
                                            <form action="{{ route('admin.wallets.destroy', ['address' => $wallet->address]) }}" method="post" class="d-inline" data-confirm="Удалить кошелёк из списка (мягкое удаление)?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                            </form>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-4">Нет кошельков или нода недоступна. Создайте кошелёк (требуется GND_API_KEY) или проверьте GND_ADMIN_SECRET.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

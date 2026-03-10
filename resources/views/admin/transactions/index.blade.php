<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Транзакции</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary btn-sm">Новый перевод</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            @if ($mempool_error ?? null)
                <div class="alert alert-warning">Мемпул ноды: {{ $mempool_error }}</div>
            @endif
            @if ($node_error ?? null)
                <div class="alert alert-warning">Транзакции с ноды: {{ $node_error }}</div>
            @endif

            {{-- Транзакции из gnd_db (нода) — основной список --}}
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-light">Транзакции в блокчейне (gnd_db, с ноды)</span>
                    @if(isset($node_total))
                        <span class="text-secondary small">Всего: {{ $node_total }}</span>
                    @endif
                </div>
                <div class="table-responsive table-mobile-wrap">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Блок</th>
                                <th scope="col">Хеш</th>
                                <th scope="col">От</th>
                                <th scope="col">Кому</th>
                                <th scope="col">Тип</th>
                                <th scope="col">Статус</th>
                                <th scope="col">Дата</th>
                                <th scope="col" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($node_list ?? [] as $tx)
                                <tr>
                                    <td class="small">{{ $tx['id'] ?? '—' }}</td>
                                    <td class="small">{{ $tx['block_id'] ?? '—' }}</td>
                                    <td class="font-monospace small text-break">{{ Str::limit($tx['hash'] ?? '', 18) }}</td>
                                    <td class="font-monospace small">{{ Str::limit($tx['sender'] ?? '', 14) }}</td>
                                    <td class="font-monospace small">{{ Str::limit($tx['recipient'] ?? '', 14) }}</td>
                                    <td><span class="badge bg-info">{{ $tx['type'] ?? '—' }}</span></td>
                                    <td>
                                        @if(($tx['status'] ?? '') === 'confirmed')
                                            <span class="badge bg-success">Подтверждена</span>
                                        @elseif(($tx['status'] ?? '') === 'pending')
                                            <span class="badge bg-warning text-dark">Ожидание</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $tx['status'] ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary small">{{ isset($tx['timestamp']) ? \Carbon\Carbon::parse($tx['timestamp'])->format('d.m.Y H:i') : '—' }}</td>
                                    <td class="text-end">
                                        @php $node_base = rtrim(config('services.gnd.node_url', ''), '/'); @endphp
                                        @if(!empty($tx['hash']) && $node_base !== '')
                                            <a href="{{ $node_base }}/api/v1/transaction/{{ $tx['hash'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">На ноде</a>
                                        @elseif(!empty($tx['hash']))
                                            <span class="btn btn-outline-secondary btn-sm disabled" title="Укажите GND_NODE_URL в .env">На ноде</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-secondary py-4">
                                        @if(\App\Services\GndAdminApi::isConfigured())
                                            Нет транзакций в БД ноды (gnd_db.transactions). Создание кошельков, деплой контрактов и другие действия записывают транзакции на ноде.
                                        @else
                                            Нода не настроена (GND_NODE_URL). Настройте подключение в .env.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Локальная история переводов (форма «Новый перевод») --}}
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <span class="text-light">История переводов (локальные записи)</span>
                </div>
                <div class="table-responsive table-mobile-wrap">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">От</th>
                                <th scope="col">Кому</th>
                                <th scope="col">Актив</th>
                                <th scope="col">Сумма</th>
                                <th scope="col">Статус</th>
                                <th scope="col">Дата</th>
                                <th scope="col" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $tx)
                                <tr>
                                    <td class="font-monospace small">{{ Str::limit($tx->from_address, 16) }}</td>
                                    <td class="font-monospace small">{{ Str::limit($tx->to_address, 16) }}</td>
                                    <td>{{ $tx->coin_symbol ?? Str::limit($tx->token_address, 12) }}</td>
                                    <td>{{ $tx->amount }}</td>
                                    <td>
                                        @if($tx->status === 'sent')
                                            <span class="badge bg-success">Отправлено</span>
                                        @elseif($tx->status === 'failed')
                                            <span class="badge bg-danger">Ошибка</span>
                                        @else
                                            <span class="badge bg-secondary">Ожидание</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary">{{ $tx->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.transactions.show', $tx->id) }}" class="btn btn-outline-primary btn-sm">Подробнее</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">Нет записей. Создайте перевод через кнопку «Новый перевод».</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="card-footer border-secondary">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

            @if(isset($mempool) && is_array($mempool) && !empty($mempool['pending_hashes'] ?? []))
                <div class="card bg-dark border-secondary">
                    <div class="card-header border-secondary text-light">Мемпул ноды (ожидающие)</div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 font-monospace small">
                            @php $node_base = rtrim(config('services.gnd.node_url', ''), '/'); @endphp
                            @foreach(array_slice($mempool['pending_hashes'] ?? [], 0, 10) as $hash)
                                <li>@if($node_base !== '')<a href="{{ $node_base }}/api/v1/transaction/{{ $hash }}" target="_blank" rel="noopener" class="text-primary">{{ Str::limit($hash, 20) }}</a>@else<span class="text-secondary">{{ Str::limit($hash, 20) }}</span>@endif</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

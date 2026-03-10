<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Информация о кошельке</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
                <a href="{{ route('admin.wallets.edit', ['address' => $wallet->address]) }}" class="btn btn-outline-primary btn-sm">Имя/роль</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            {{-- Основные данные кошелька --}}
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary">
                    <h3 class="h6 mb-0 text-light">Данные кошелька</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-secondary">Адрес</dt>
                        <dd class="col-sm-9">
                            <code class="text-break text-light">{{ $wallet->address }}</code>
                        </dd>
                        @if(!empty($wallet->name))
                            <dt class="col-sm-3 text-secondary">Имя</dt>
                            <dd class="col-sm-9 text-light">{{ $wallet->name }}</dd>
                        @endif
                        @if(!empty($wallet->role))
                            <dt class="col-sm-3 text-secondary">Роль</dt>
                            <dd class="col-sm-9">
                                <span class="badge bg-info">{{ $wallet->role }}</span>
                            </dd>
                        @endif
                        @if(!empty($wallet->signer_wallet_id))
                            <dt class="col-sm-3 text-secondary">Signer Wallet ID</dt>
                            <dd class="col-sm-9 font-monospace small text-secondary">{{ $wallet->signer_wallet_id }}</dd>
                        @endif
                        @if(!empty($wallet->created_at))
                            <dt class="col-sm-3 text-secondary">Создан</dt>
                            <dd class="col-sm-9 text-secondary">{{ \Carbon\Carbon::parse($wallet->created_at)->format('d.m.Y H:i') }}</dd>
                        @endif
                        @if(!empty($wallet->signer_wallet_id))
                            <dt class="col-sm-3 text-secondary">Подписание</dt>
                            <dd class="col-sm-9">
                                @if(!empty($wallet->blocked))
                                    <span class="badge bg-warning text-dark">Заблокировано</span>
                                @else
                                    <span class="badge bg-success">Активно</span>
                                @endif
                            </dd>
                        @endif
                        @if(!empty($node_request_urls))
                            <dt class="col-sm-3 text-secondary">URL запроса к ноде</dt>
                            <dd class="col-sm-9">
                                <ul class="list-unstyled mb-0 small">
                                    @if(!empty($node_request_urls['balance']))
                                        <li class="mb-1">
                                            <span class="text-secondary">Баланс (все токены и нативные):</span>
                                            <br><code class="text-break text-light small">{{ $node_request_urls['balance'] }}</code>
                                        </li>
                                    @endif
                                    @if(!empty($node_request_urls['coin_balance_gnd']))
                                        <li class="mb-1">
                                            <span class="text-secondary">GND баланс:</span>
                                            <br><code class="text-break text-light small">{{ $node_request_urls['coin_balance_gnd'] }}</code>
                                        </li>
                                    @endif
                                    @if(!empty($node_request_urls['coin_balance_gani']))
                                        <li class="mb-1">
                                            <span class="text-secondary">GANI баланс:</span>
                                            <br><code class="text-break text-light small">{{ $node_request_urls['coin_balance_gani'] }}</code>
                                        </li>
                                    @endif
                                    @if(!empty($node_request_urls['coin_supply_gnd']))
                                        <li class="mb-1">
                                            <span class="text-secondary">GND supply:</span>
                                            <br><code class="text-break text-light small">{{ $node_request_urls['coin_supply_gnd'] }}</code>
                                        </li>
                                    @endif
                                    @if(!empty($node_request_urls['coin_supply_gani']))
                                        <li class="mb-1">
                                            <span class="text-secondary">GANI supply:</span>
                                            <br><code class="text-break text-light small">{{ $node_request_urls['coin_supply_gani'] }}</code>
                                        </li>
                                    @endif
                                    @if(!empty($node_request_urls['admin_wallets']))
                                        <li>
                                            <span class="text-secondary">Список кошельков (GET):</span>
                                            <br><code class="text-break text-light small">{{ $node_request_urls['admin_wallets'] }}</code>
                                        </li>
                                    @endif
                                </ul>
                            </dd>
                        @endif
                    </dl>
                </div>
                <div class="card-footer border-secondary d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.wallets.edit', ['address' => $wallet->address]) }}" class="btn btn-outline-primary btn-sm">Изменить имя/роль</a>
                    @if(!empty($wallet->signer_wallet_id))
                        @if(!empty($wallet->blocked))
                            <form action="{{ route('admin.wallets.enable', ['address' => $wallet->address]) }}" method="post" class="d-inline" data-confirm="Разблокировать подписание для этого кошелька?">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm">Разблокировать</button>
                            </form>
                        @else
                            <form action="{{ route('admin.wallets.disable', ['address' => $wallet->address]) }}" method="post" class="d-inline" data-confirm="Заблокировать подписание кошелька?">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">Блокировать</button>
                            </form>
                        @endif
                    @endif
                    <form action="{{ route('admin.wallets.destroy', ['address' => $wallet->address]) }}" method="post" class="d-inline" data-confirm="Удалить кошелёк из списка (мягкое удаление)?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                    </form>
                </div>
            </div>

            {{-- Балансы: нативные монеты (GND, GANI) + контрактные токены — GET /api/v1/wallet/:address/balance --}}
            <div class="card bg-dark border-secondary">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h3 class="h6 mb-0 text-light">Балансы (нативные монеты и токены)</h3>
                    @if($balance_data !== null)
                        <span class="badge bg-secondary">адрес: {{ $balance_data['address'] ?? $wallet->address }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if(isset($balance_error) && $balance_error)
                        <div class="alert alert-danger mb-0">Ошибка ноды при запросе баланса: {{ $balance_error }}</div>
                    @elseif($balance_data === null)
                        <p class="text-secondary mb-0">Задайте <code>GND_NODE_URL</code> в .env для загрузки балансов.</p>
                    @else
                        @php
                            $balances = $balance_data['balances'] ?? [];
                        @endphp
                        @if(count($balances) === 0)
                            <p class="text-secondary mb-0">Нет записей о балансах по этому адресу.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0" role="table">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 48px;">Лого</th>
                                            <th scope="col">Токен</th>
                                            <th scope="col">Символ</th>
                                            <th scope="col">Стандарт</th>
                                            <th scope="col" class="text-end">Баланс</th>
                                            <th scope="col">Десятичных</th>
                                            <th scope="col">Верифицирован</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($balances as $b)
                                            @php $b = (object) $b; @endphp
                                            <tr>
                                                <td class="align-middle">
                                                    @php $logoUrl = \App\Helpers\FormatHelper::logoUrlForBalanceItem($b); @endphp
                                                    @if($logoUrl)
                                                        <img src="{{ $logoUrl }}" alt="" class="rounded" style="width: 40px; height: 40px; object-fit: contain; background: #2d3338;" onerror="this.style.display='none'; this.nextElementSibling?.classList.remove('d-none');">
                                                        <span class="text-secondary small d-none">—</span>
                                                    @else
                                                        <span class="text-secondary small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-light">{{ $b->name ?? '—' }}</span>
                                                    @if(!empty($b->token_address))
                                                        <br><code class="small text-secondary">{{ $b->token_address }}</code>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $b->symbol ?? '—' }}</span>
                                                    @if(empty($b->token_address) && in_array(strtoupper($b->symbol ?? ''), ['GND', 'GANI'], true))
                                                        <span class="badge bg-info text-dark ms-1" title="Нативная монета протокола L1">L1</span>
                                                    @endif
                                                </td>
                                                <td class="text-secondary small">{{ $b->standard ?? '—' }}</td>
                                                <td class="text-end font-monospace text-light">{{ \App\Helpers\FormatHelper::balanceWithDecimals((string)($b->balance ?? '0'), (int)($b->decimals ?? 0)) }}</td>
                                                <td class="text-secondary">{{ $b->decimals ?? '—' }}</td>
                                                <td>
                                                    @if(!empty($b->is_verified))
                                                        <span class="badge bg-success">да</span>
                                                    @else
                                                        <span class="badge bg-secondary">нет</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

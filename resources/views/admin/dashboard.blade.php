<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-light">
            Обзор админ-панели
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="row g-4">
                {{-- Обзор: пользователи, админы, кошельки --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <h3 class="h6 text-light mb-4">Обзор</h3>
                            <dl class="row mb-0">
                                <div class="col-sm-6 col-md-4">
                                    <dt class="text-secondary small">Пользователей</dt>
                                    <dd class="fs-4 text-light mb-0">{{ number_format($usersCount, 0, ',', ' ') }}</dd>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <dt class="text-secondary small">Администраторов</dt>
                                    <dd class="fs-4 text-light mb-0">{{ number_format($adminsCount, 0, ',', ' ') }}</dd>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <dt class="text-secondary small">Кошельков</dt>
                                    <dd class="fs-4 text-light mb-0">{{ number_format($walletsCount ?? 0, 0, ',', ' ') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Цепочка: блоки, транзакции (без предложения) --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <h3 class="h6 text-light mb-4">Цепочка (данные ноды)</h3>
                            @if(!($gndConfigured ?? false))
                                <p class="text-secondary mb-0">Задайте <code>GND_NODE_URL</code> и <code>GND_ADMIN_SECRET</code> в .env для загрузки данных с ноды.</p>
                            @else
                                <dl class="row mb-0">
                                    <div class="col-sm-6 col-md-4">
                                        <dt class="text-secondary small">Блоков всего</dt>
                                        <dd class="fs-4 text-light mb-0 font-monospace">{{ $blocksCount !== null ? number_format($blocksCount, 0, ',', ' ') : '—' }}</dd>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <dt class="text-secondary small">Транзакций всего</dt>
                                        <dd class="fs-4 text-light mb-0 font-monospace">{{ $transactionsCount !== null ? number_format($transactionsCount, 0, ',', ' ') : number_format($transactionsTotalLocal ?? 0, 0, ',', ' ') }}</dd>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <dt class="text-secondary small">Транзакций в последнем блоке</dt>
                                        <dd class="fs-4 text-light mb-0 font-monospace">{{ $latestBlockTxCount !== null ? number_format($latestBlockTxCount, 0, ',', ' ') : '—' }}</dd>
                                    </div>
                                </dl>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Отдельная карточка: total_supply и циркуляция GND/GANI --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <h3 class="h6 text-light mb-4">Предложение (total_supply и циркуляция)</h3>
                            @if(!($gndConfigured ?? false))
                                <p class="text-secondary mb-0">Данные с ноды недоступны. Настройте подключение в .env.</p>
                            @else
                                <dl class="row mb-0">
                                    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                                        <dt class="text-secondary small">GND — total_supply</dt>
                                        <dd class="fs-5 text-light mb-0 font-monospace">{{ $genesisTotalSupplyFormatted ?? '—' }}</dd>
                                    </div>
                                    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                                        <dt class="text-secondary small">GND — циркуляция</dt>
                                        <dd class="fs-5 text-light mb-0 font-monospace">{{ $genesisCirculatingSupplyFormatted ?? '—' }}</dd>
                                    </div>
                                    @if(isset($totalSupplyGaniFormatted) || isset($circulatingSupplyGaniFormatted))
                                        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                                            <dt class="text-secondary small">GANI — total_supply</dt>
                                            <dd class="fs-5 text-light mb-0 font-monospace">{{ $totalSupplyGaniFormatted ?? '—' }}</dd>
                                        </div>
                                        <div class="col-sm-6 col-lg-3">
                                            <dt class="text-secondary small">GANI — циркуляция</dt>
                                            <dd class="fs-5 text-light mb-0 font-monospace">{{ $circulatingSupplyGaniFormatted ?? '—' }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Карточка: контракты и распределение эмиссии --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <h3 class="h6 text-light mb-4">Контракты и распределение эмиссии</h3>
                            <dl class="row mb-0">
                                <div class="col-sm-6 col-md-3">
                                    <dt class="text-secondary small">Контрактов (в каталоге)</dt>
                                    <dd class="fs-4 text-light mb-0">{{ number_format($contractsCount ?? 0, 0, ',', ' ') }}</dd>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <dt class="text-secondary small">Токенов всего (в каталоге)</dt>
                                    <dd class="fs-4 text-light mb-0">{{ number_format($tokensTotal ?? 0, 0, ',', ' ') }}</dd>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <dt class="text-secondary small">Токенов по контрактам</dt>
                                    <dd class="fs-4 text-light mb-0">{{ number_format($tokensViaContracts ?? 0, 0, ',', ' ') }}</dd>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <dt class="text-secondary small">Доля распределения эмиссии, %</dt>
                                    <dd class="fs-4 text-light mb-0">{{ number_format($contractsPercent ?? 0, 1, ',', ' ') }}%</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Разделы --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <h3 class="h6 text-light mb-4">Разделы</h3>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <a href="{{ route('admin.users.index') }}" class="text-primary text-decoration-none">Пользователи и роли →</a>
                                </li>
                                <li class="mb-2">
                                    <a href="{{ route('admin.accounts.index') }}" class="text-primary text-decoration-none">Аккаунты →</a>
                                </li>
                                <li class="mb-2">
                                    <a href="{{ route('admin.wallets.index') }}" class="text-primary text-decoration-none">Кошельки →</a>
                                </li>
                                <li class="mb-2">
                                    <a href="{{ route('admin.contracts.index') }}" class="text-primary text-decoration-none">Контракты →</a>
                                </li>
                                <li class="mb-2">
                                    <a href="{{ route('admin.tokens.index') }}" class="text-primary text-decoration-none">Токены →</a>
                                </li>
                                <li class="mb-2">
                                    <a href="{{ route('admin.transactions.index') }}" class="text-primary text-decoration-none">Транзакции →</a>
                                </li>
                                <li class="mb-2">
                                    <a href="{{ route('admin.keys.index') }}" class="text-primary text-decoration-none">Ключи приложений →</a>
                                </li>
                                <li class="mb-2">
                                    <a href="{{ route('admin.api.index') }}" class="text-primary text-decoration-none">API (REST, WS, RPC) →</a>
                                </li>
                                <li class="mb-0">
                                    <a href="{{ route('admin.instruction.index') }}" class="text-primary text-decoration-none">Инструкции (пошаговая и сводная настройка) →</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

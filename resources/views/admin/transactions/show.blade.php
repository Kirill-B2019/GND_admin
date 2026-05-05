<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Транзакция #' . $transaction->id">
            <x-slot name="actions">
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary btn-sm">← К списку</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid" style="max-width: 40rem;">
            <x-admin.breadcrumbs :items="[
                ['label' => 'Обзор', 'url' => route('admin.dashboard')],
                ['label' => 'Транзакции', 'url' => route('admin.transactions.index')],
                ['label' => '#' . $transaction->id],
            ]" />
            <div class="card bg-dark border-secondary">
                <div class="card-header border-secondary text-light">Детали перевода</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary">От</dt>
                        <dd class="col-sm-8 font-monospace">{{ $transaction->from_address }}</dd>
                        <dt class="col-sm-4 text-secondary">Кому</dt>
                        <dd class="col-sm-8 font-monospace">{{ $transaction->to_address }}</dd>
                        <dt class="col-sm-4 text-secondary">Токен / монета</dt>
                        <dd class="col-sm-8">{{ $transaction->coin_symbol ?? $transaction->token_address ?? '—' }}</dd>
                        <dt class="col-sm-4 text-secondary">Сумма</dt>
                        <dd class="col-sm-8">{{ $transaction->amount }}</dd>
                        <dt class="col-sm-4 text-secondary">Nonce</dt>
                        <dd class="col-sm-8">{{ $transaction->nonce ?? '—' }}</dd>
                        <dt class="col-sm-4 text-secondary">Статус</dt>
                        <dd class="col-sm-8">
                            @if($transaction->status === 'sent')
                                <span class="badge bg-success">Отправлено</span>
                            @elseif($transaction->status === 'failed')
                                <span class="badge bg-danger">Ошибка</span>
                            @else
                                <span class="badge bg-secondary">Ожидание</span>
                            @endif
                        </dd>
                        @if($transaction->tx_hash)
                            <dt class="col-sm-4 text-secondary">Хеш</dt>
                            <dd class="col-sm-8 font-monospace small">{{ $transaction->tx_hash }}</dd>
                        @endif
                        @if($transaction->error_message)
                            <dt class="col-sm-4 text-secondary">Ошибка</dt>
                            <dd class="col-sm-8 text-danger">{{ $transaction->error_message }}</dd>
                        @endif
                        <dt class="col-sm-4 text-secondary">Создано</dt>
                        <dd class="col-sm-8">{{ $transaction->created_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
            @if($tx_from_node)
                <div class="card bg-dark border-secondary mt-3">
                    <div class="card-header border-secondary text-light">Данные с ноды</div>
                    <div class="card-body">
                        <pre class="mb-0 text-light small">{{ json_encode($tx_from_node, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif
            @if($tx_error && $transaction->tx_hash)
                <div class="alert alert-warning mt-3">Нода: {{ $tx_error }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

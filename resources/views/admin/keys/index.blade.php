<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Ключи приложений</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <a href="{{ route('admin.keys.create') }}" class="btn btn-primary btn-sm">Создать ключ</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            @if (isset($gnd_error) && $gnd_error)
                <div class="visually-hidden" data-flash="error">Ошибка ноды GND: {{ $gnd_error }}</div>
            @endif
            @if (isset($gnd_configured) && !$gnd_configured)
                <div class="alert alert-warning">Задайте в .env переменные <code>GND_NODE_URL</code> и <code>GND_ADMIN_SECRET</code> для работы с ключами через ноду.</div>
            @endif
            <div class="card bg-dark border-secondary">
                <div class="table-responsive table-mobile-wrap">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Ключ</th>
                                <th>Истекает</th>
                                <th>Статус</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($keys as $key)
                                @php
                                    $key = (object) $key;
                                    $expiresAt = $key->expires_at ?? null;
                                    $expiresStr = $expiresAt ? \Carbon\Carbon::parse($expiresAt)->format('d.m.Y') : '—';
                                @endphp
                                <tr class="{{ ($key->disabled ?? false) ? 'opacity-75' : '' }}">
                                    <td>{{ $key->id }}</td>
                                    <td>{{ $key->name ?? '—' }}</td>
                                    <td class="font-monospace text-secondary">…{{ $key->key_prefix ?? '—' }}</td>
                                    <td class="text-secondary">{{ $expiresStr }}</td>
                                    <td>
                                        @if($key->disabled ?? false)
                                            <span class="badge bg-danger">отозван</span>
                                        @else
                                            <span class="badge bg-success">активен</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!($key->disabled ?? false))
                                            <form action="{{ route('admin.keys.revoke', $key->id) }}" method="post" class="d-inline" data-confirm="Отозвать ключ? После отзыва он перестанет работать.">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm">Отозвать</button>
                                            </form>
                                        @else
                                            <span class="text-secondary small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">Нет ключей приложений. Создайте ключ через ноду GND (требуется GND_ADMIN_SECRET).</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

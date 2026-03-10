<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Токены</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <a href="{{ route('admin.tokens.deploy') }}" class="btn btn-outline-info btn-sm">Деплой на ноду</a>
                <a href="{{ route('admin.tokens.create') }}" class="btn btn-primary btn-sm">Добавить токен</a>
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
                                <th style="width: 56px;">Лого</th>
                                <th>Символ</th>
                                <th>Название</th>
                                <th>Адрес</th>
                                <th>Стандарт</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tokens as $token)
                                <tr>
                                    <td class="align-middle">
                                        @if($logo = $token->logoUrl())
                                            <img src="{{ $logo }}" alt="" class="rounded" style="width: 40px; height: 40px; object-fit: contain; background: #2d3338;">
                                        @else
                                            <span class="text-secondary small">—</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">{{ $token->symbol }}</td>
                                    <td>
                                        <a href="{{ route('admin.tokens.show', $token) }}" class="text-primary text-decoration-none">{{ $token->name }}</a>
                                    </td>
                                    <td class="font-monospace small text-secondary">{{ Str::limit($token->token_address, 20) }}</td>
                                    <td class="text-secondary">{{ $token->standard ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.tokens.show', $token) }}" class="btn btn-outline-primary btn-sm me-1">Подробнее</a>
                                        <a href="{{ route('admin.tokens.edit', $token) }}" class="btn btn-outline-secondary btn-sm me-1">Изменить</a>
                                        <form action="{{ route('admin.tokens.destroy', $token) }}" method="post" class="d-inline" data-confirm="Удалить токен?">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">Нет токенов. <a href="{{ route('admin.tokens.create') }}" class="text-primary">Добавить</a></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tokens->hasPages())
                    <div class="card-footer border-secondary">
                        {{ $tokens->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

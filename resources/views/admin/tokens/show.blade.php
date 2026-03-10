<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">{{ $token->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary btn-sm">← Список</a>
                <a href="{{ route('admin.tokens.edit', $token) }}" class="btn btn-primary btn-sm">Изменить</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <div class="row">
                <div class="col-lg-8">
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header border-secondary text-light">Информация о токене</div>
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                @if($logo = $token->logoUrl())
                                    <img src="{{ $logo }}" alt="" class="rounded flex-shrink-0" style="width: 64px; height: 64px; object-fit: contain; background: #2d3338;">
                                @endif
                                <div>
                                    <h3 class="h5 text-light mb-1">{{ $token->symbol }} — {{ $token->name }}</h3>
                                    <p class="font-monospace small text-secondary mb-0">{{ $token->token_address }}</p>
                                    @if($token->standard)
                                        <span class="badge bg-secondary mt-1">{{ $token->standard }}</span>
                                    @endif
                                    @if($token->is_verified)
                                        <span class="badge bg-success mt-1">Верифицирован</span>
                                    @endif
                                </div>
                            </div>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-secondary">Decimals</dt>
                                <dd class="col-sm-8 text-light">{{ $token->decimals }}</dd>
                                @if($token->total_supply)
                                    <dt class="col-sm-4 text-secondary">Total supply</dt>
                                    <dd class="col-sm-8 text-light font-monospace">{{ $token->total_supply }}</dd>
                                @endif
                                @if($token->last_synced_at)
                                    <dt class="col-sm-4 text-secondary">Обновлено (синхр.)</dt>
                                    <dd class="col-sm-8 text-light">{{ $token->last_synced_at->format('d.m.Y H:i') }}</dd>
                                @endif
                            </dl>
                            @if($token->description)
                                <hr class="border-secondary my-3">
                                <p class="text-light mb-0">{{ $token->description }}</p>
                            @endif
                        </div>
                    </div>
                    @if(!empty($token->metadata_json))
                        <div class="card bg-dark border-secondary mb-4">
                            <div class="card-header border-secondary text-light">Доп. метаданные</div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    @foreach($token->metadata_json as $key => $value)
                                        <dt class="col-sm-4 text-secondary">{{ e($key) }}</dt>
                                        <dd class="col-sm-8 text-light">{{ is_scalar($value) ? e($value) : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd>
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-lg-4">
                    <div class="card bg-dark border-secondary">
                        <div class="card-header border-secondary text-light">Действия</div>
                        <div class="card-body d-flex flex-column gap-2">
                            <a href="{{ route('admin.tokens.edit', $token) }}" class="btn btn-outline-primary">Редактировать</a>
                            <form action="{{ route('admin.tokens.destroy', $token) }}" method="post" data-confirm="Удалить токен?">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-outline-danger w-100">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

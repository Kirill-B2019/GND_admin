<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-light">
            API ноды ГАНИМЕД
        </h2>
        <p class="text-secondary small mb-0 mt-1">REST, WebSocket и RPC — все варианты подключения к ноде</p>
    </x-slot>

    <div class="py-2">
        <div class="container-fluid">
            <div class="row g-4">
                {{-- Где нужны ключи --}}
                <div class="col-12">
                    <div class="alert alert-info border-secondary mb-0" role="region" aria-label="Ключи API">
                        <h4 class="alert-heading h6 text-light">Где требуются ключи</h4>
                        <ul class="mb-0 small text-secondary">
                            <li><strong>X-API-Key</strong> — заголовок для запросов к ноде. Нужен для: <code>POST /api/v1/wallet</code> (создание кошелька), <code>POST /api/v1/token/deploy</code> (выпуск токена).</li>
                            <li>Ключ задаётся в <code>.env</code> как <code>GND_API_KEY</code> или создаётся в разделе <a href="{{ route('admin.keys.index') }}" class="alert-link">Ключи приложений</a> и подставляется нодой.</li>
                            @if($apiKeyConfigured ?? false)
                                <li class="text-success mt-1">Ключ задан в конфиге — при отправке запросов к этим эндпоинтам он подставится автоматически.</li>
                            @else
                                <li class="text-warning mt-1">Ключ в конфиге не задан — запросы к <code>POST /wallet</code> и <code>POST /token/deploy</code> могут вернуть 401.</li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- Админское API (по docs/admin-api.md) --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-header bg-dark border-secondary d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h3 class="h6 text-light mb-0">Админское API</h3>
                            <code class="text-info small text-break">{{ $restBase }}/admin</code>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary small mb-3">
                                Отдельные маршруты для управления <strong>API-ключами</strong> (создание, список, отзыв) и <strong>кошельками</strong> (имена, роли, блокировка подписания, мягкое удаление). Защищаются <strong>не</strong> <code>X-API-Key</code>, а заголовком <strong>X-Admin-Token</strong> — значение переменной окружения <code>GND_ADMIN_SECRET</code> на ноде. В админ-панели используется <code>GND_ADMIN_SECRET</code> из .env.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-dark table-sm mb-0" role="table">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap">Метод</th>
                                            <th scope="col" class="text-nowrap">Путь</th>
                                            <th scope="col">Описание</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/keys</td><td class="text-secondary small">Создать API-ключ (ключ в ответе один раз)</td></tr>
                                        <tr><td><span class="badge bg-success">GET</span></td><td class="font-monospace small">/api/v1/admin/keys</td><td class="text-secondary small">Список ключей (без поля key)</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/keys/:id/revoke</td><td class="text-secondary small">Отозвать ключ</td></tr>
                                        <tr><td><span class="badge bg-danger">DELETE</span></td><td class="font-monospace small">/api/v1/admin/keys/:id</td><td class="text-secondary small">То же (отзыв)</td></tr>
                                        <tr><td><span class="badge bg-success">GET</span></td><td class="font-monospace small">/api/v1/admin/wallets</td><td class="text-secondary small">Список кошельков (без удалённых; ?role=)</td></tr>
                                        <tr><td><span class="badge bg-warning text-dark">PATCH</span></td><td class="font-monospace small">/api/v1/admin/wallets/:address</td><td class="text-secondary small">Обновить name и/или role кошелька</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/wallets/:address/disable</td><td class="text-secondary small">Блокировка подписания (signer_wallets.disabled), запись транзакции</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/wallets/:address/enable</td><td class="text-secondary small">Разблокировка подписания, запись транзакции</td></tr>
                                        <tr><td><span class="badge bg-danger">DELETE</span></td><td class="font-monospace small">/api/v1/admin/wallets/:address</td><td class="text-secondary small">Мягкое удаление (wallets.disabled), запись транзакции</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/wallets/:address/delete</td><td class="text-secondary small">То же (мягкое удаление)</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/record-transaction</td><td class="text-secondary small">Запись произвольной транзакции (type, sender, recipient, payload)</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/contracts/:address/disable</td><td class="text-secondary small">Отключение контракта (транзакция в gnd_db.transactions)</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/contracts/:address/delete</td><td class="text-secondary small">Удаление контракта (транзакция)</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/tokens/:id/disable</td><td class="text-secondary small">Отключение токена (транзакция)</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/tokens/:id/delete</td><td class="text-secondary small">Удаление токена (транзакция)</td></tr>
                                        <tr><td><span class="badge bg-primary">POST</span></td><td class="font-monospace small">/api/v1/admin/state/contract/:address/storage</td><td class="text-secondary small">Запись слота storage контракта (block_id, slot_key, slot_value), транзакция contract_storage_write</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-dark border-secondary text-secondary small">
                            Все маршруты требуют заголовок <strong>X-Admin-Token</strong> равный <code>GND_ADMIN_SECRET</code>. Полное описание: <code>GND_v1/docs/admin-api.md</code>.
                        </div>
                    </div>
                </div>

                {{-- REST API --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-header bg-dark border-secondary d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h3 class="h6 text-light mb-0">REST API</h3>
                            <code class="text-info small text-break">{{ $restBase }}</code>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0" role="table" id="rest-api-table">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap">Метод</th>
                                            <th scope="col" class="text-nowrap">Путь</th>
                                            <th scope="col">Описание</th>
                                            <th scope="col" class="text-nowrap" style="width: 140px;">Действие</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($restEndpoints as $idx => $ep)
                                            @php
                                                $pathRaw = ltrim($ep['path'] ?? '', '/');
                                            @endphp
                                            <tr class="api-row" data-index="{{ $idx }}" data-method="{{ $ep['method'] ?? 'GET' }}" data-path="{{ $pathRaw }}">
                                                <td class="text-nowrap">
                                                    @if(($ep['method'] ?? '') === 'GET')
                                                        <span class="badge bg-success">GET</span>
                                                    @elseif(($ep['method'] ?? '') === 'POST')
                                                        <span class="badge bg-primary">POST</span>
                                                    @elseif(($ep['method'] ?? '') === 'PATCH')
                                                        <span class="badge bg-warning text-dark">PATCH</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $ep['method'] ?? '' }}</span>
                                                    @endif
                                                    @if(!empty($ep['auth']))
                                                        <span class="badge bg-info ms-1" title="Требуется X-API-Key">API-Key</span>
                                                    @endif
                                                </td>
                                                <td class="font-monospace small text-break">{{ $ep['path'] ?? '' }}</td>
                                                <td class="text-secondary small">{{ $ep['desc'] ?? '' }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary api-send-btn" data-index="{{ $idx }}" aria-label="Отправить запрос для этого эндпоинта">
                                                        Отправить запрос
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="api-response-row table-secondary" id="api-response-{{ $idx }}" style="display: none;" aria-live="polite">
                                                <td colspan="4" class="p-2">
                                                    <div class="card bg-dark border-secondary mb-0">
                                                        <div class="card-header py-2 d-flex align-items-center justify-content-between">
                                                            <span class="small text-light">Ответ</span>
                                                            <span class="small text-secondary api-response-status"></span>
                                                        </div>
                                                        <div class="card-body py-2 small">
                                                            <pre class="api-response-body mb-0 text-break small" style="max-height: 280px; overflow: auto;"></pre>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-dark border-secondary text-secondary small">
                            Базовый URL ноды задаётся в <code>GND_NODE_URL</code>. Кнопка «Отправить запрос» вызывает эндпоинт через прокси; для путей с параметрами подставляются значения по умолчанию (GND, 0).
                        </div>
                    </div>
                </div>

                {{-- WebSocket API --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-header bg-dark border-secondary d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h3 class="h6 text-light mb-0">WebSocket API</h3>
                            <code class="text-info small text-break">{{ $wsUrl }}</code>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary small mb-3">Подключение по WebSocket для подписки на события в реальном времени. Протокол JSON-RPC 2.0, подписки: <code>blocks</code>, <code>transactions</code>, <code>events</code>.</p>
                            <table class="table table-dark table-sm mb-0" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Канал</th>
                                        <th scope="col">Описание</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wsSubscriptions as $sub)
                                        <tr>
                                            <td class="font-monospace">{{ $sub['channel'] }}</td>
                                            <td class="text-secondary small">{{ $sub['desc'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-dark border-secondary text-secondary small">
                            Порт WebSocket по умолчанию — 8183. При использовании HTTPS ноды подключайтесь по <strong>wss://</strong>.
                        </div>
                    </div>
                </div>

                {{-- RPC API --}}
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-header bg-dark border-secondary d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h3 class="h6 text-light mb-0">RPC API</h3>
                            <code class="text-info small text-break">{{ $rpcUrl }}</code>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0" role="table">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap">Метод</th>
                                            <th scope="col" class="text-nowrap">Путь / действие</th>
                                            <th scope="col">Описание</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rpcEndpoints as $ep)
                                            <tr>
                                                <td class="text-nowrap">
                                                    @if(($ep['method'] ?? '') === 'GET')
                                                        <span class="badge bg-success">GET</span>
                                                    @else
                                                        <span class="badge bg-primary">POST</span>
                                                    @endif
                                                </td>
                                                <td class="font-monospace small text-break">{{ $ep['path'] ?? '' }}</td>
                                                <td class="text-secondary small">{{ $ep['desc'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-dark border-secondary text-secondary small">
                            RPC-сервер ноды по умолчанию слушает порт 8181. Подробности — в документации ноды <code>docs/api.md</code>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const sendUrl = '{{ route("admin.api.send-request") }}';

    function buildPath(template) {
        return template
            .replace(/:symbol/g, 'GND')
            .replace(/:owner/g, 'GND')
            .replace(/:number/g, '0')
            .replace(/:blockId/g, '0')
            .replace(/:id/g, '1')
            .replace(/:address/g, 'GND')
            .replace(/:hash/g, '0');
    }

    document.querySelectorAll('.api-send-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const index = this.getAttribute('data-index');
            const row = document.querySelector('.api-row[data-index="' + index + '"]');
            const method = row.getAttribute('data-method');
            const pathTemplate = row.getAttribute('data-path');
            const path = buildPath(pathTemplate);

            // Скрыть все карточки ответов
            document.querySelectorAll('.api-response-row').forEach(function (r) {
                r.style.display = 'none';
            });

            const responseRow = document.getElementById('api-response-' + index);
            const statusEl = responseRow.querySelector('.api-response-status');
            const bodyEl = responseRow.querySelector('.api-response-body');
            statusEl.textContent = 'Загрузка…';
            bodyEl.textContent = '';
            responseRow.style.display = 'table-row';

            const body = { method: method, path: path, _token: csrf };
            if (method !== 'GET') {
                body.body = {};
            }

            fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(body)
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                statusEl.textContent = 'HTTP ' + (data.statusCode || '—') + (data.usedApiKey ? ' · использован X-API-Key' : '');
                if (data.body !== undefined && data.body !== null) {
                    bodyEl.textContent = typeof data.body === 'string' ? data.body : JSON.stringify(data.body, null, 2);
                } else if (data.error) {
                    bodyEl.textContent = data.error;
                }
            })
            .catch(function (err) {
                statusEl.textContent = 'Ошибка';
                bodyEl.textContent = err.message || 'Не удалось выполнить запрос';
            });
        });
    });
})();
    </script>
    @endpush
</x-app-layout>

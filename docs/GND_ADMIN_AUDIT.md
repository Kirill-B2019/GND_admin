# Аудит GND_admin (рекомендации и внесённые правки)

Краткое резюме внутреннего аудита UI/архитектуры админки и связанных изменений на ноде.

## Проблемы и решения

### 1. Дубликаты функций в списке ABI (например `totalSupply`)

**Причина:** в JSON ABI одна и та же функция могла встречаться дважды (интерфейс + реализация). Нода строила списки view/write без объединения.

**Решение (GND_v1):** в `api/abi_helper.go` функция `ParseABIFunctions` дедуплицирует записи по ключу сигнатуры `name(type1,type2,…)`; сохраняется первое вхождение. Добавлены unit-тесты в `api/abi_helper_test.go`.

### 2. Inline JavaScript на странице контракта

**Причина:** большой блок скрипта в `resources/views/admin/contracts/show.blade.php`, ethers с CDN.

**Решение:** логика вынесена в `resources/js/contract-show.js`, подключение через Vite (`vite.config.js` — отдельный entry). Зависимость `ethers@^5.7.2` в `package.json`. Конфигурация (ABI view, URL вызовов, префикс адреса GND) передаётся через `<script type="application/json" id="contract-show-config">`.

### 3. Валидация деплоя в контроллере

**Причина:** правила валидации были встроены в `ContractController::deploy`.

**Решение:** класс `App\Http\Requests\ContractDeployRequest`; метод `deploy` принимает его вместо общего `Request`.

## Сборка и проверки

- Нода: `go test ./api/...` и `go build` из корня `GND_v1`.
- Админка: `npm install`, `npm run build` в каталоге `GND_admin`.

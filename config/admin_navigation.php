<?php

/**
 * Единый список пунктов бокового меню админки (десктоп + мобильный offcanvas).
 * pattern — аргумент для request()->routeIs().
 */
return [
    'main' => [
        ['label' => 'Обзор', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard'],
        ['label' => 'Пользователи', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*'],
        ['label' => 'Аккаунты', 'route' => 'admin.accounts.index', 'pattern' => 'admin.accounts.*'],
        ['label' => 'Кошельки', 'route' => 'admin.wallets.index', 'pattern' => 'admin.wallets.*'],
        ['label' => 'Контракты', 'route' => 'admin.contracts.index', 'pattern' => 'admin.contracts.*'],
        ['label' => 'Токены', 'route' => 'admin.tokens.index', 'pattern' => 'admin.tokens.*'],
        ['label' => 'Транзакции', 'route' => 'admin.transactions.index', 'pattern' => 'admin.transactions.*'],
        ['label' => 'Ключи приложений', 'route' => 'admin.keys.index', 'pattern' => 'admin.keys.*'],
        ['label' => 'API (REST, WS, RPC)', 'route' => 'admin.api.index', 'pattern' => 'admin.api.*'],
    ],
    'instruction' => [
        ['label' => 'Инструкции', 'route' => 'admin.instruction.index', 'pattern' => 'admin.instruction.index'],
        ['label' => 'Пошаговая инструкция', 'route' => 'admin.instruction.steps', 'pattern' => 'admin.instruction.steps', 'item_class' => 'ps-3 small'],
        ['label' => 'Сводная настройка', 'route' => 'admin.instruction.setup', 'pattern' => 'admin.instruction.setup', 'item_class' => 'ps-3 small'],
    ],
];

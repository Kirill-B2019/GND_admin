<?php

return [
    /*
    | Формат адреса смарт-контракта в сети ГАНИМЕД (совпадает с GND_v1 types.ContractAddressPrefix).
    | Префикс используется для однозначного определения контракта (а не перевода на кошелёк и т.д.).
    */
    'contract_address_prefix' => env('GND_CONTRACT_ADDRESS_PREFIX', 'GNDct'),

    /*
    | Длина адреса контракта: префикс + 32 hex-символа (например 5 + 32 = 37).
    */
    'contract_address_length' => (int) env('GND_CONTRACT_ADDRESS_LENGTH', 37),

    /*
    | Длина hex-суффикса адреса контракта (32 символа).
    */
    'contract_address_hex_length' => 32,

    /*
    | Путь к каталогу deploy_order (GND_v1/tokens/standards/deploy_order).
    | Если задан — в формах создания/редактирования контрактов доступна загрузка шаблонов.
    | Абсолютный путь или относительный от base_path().
    */
    'deploy_order_path' => env('GND_DEPLOY_ORDER_PATH', ''),
];

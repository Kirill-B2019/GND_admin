<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;

/**
 * Форматирование чисел для отображения (балансы токенов).
 * Количество знаков после запятой — отсчитать с конца строки и поставить запятую.
 */
class FormatHelper
{
    /**
     * Возвращает URL логотипа для отображения в админке.
     * Если URL относительный (начинается с /), дополняет базовым URL ноды GND.
     */
    public static function logoUrlForDisplay(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        $base = rtrim((string) Config::get('services.gnd.node_url', ''), '/');
        // Относительный путь без базового URL ноды ведёт к 404 (запрос уйдёт на админку)
        if ($base === '') {
            return null;
        }
        return $base . (str_starts_with($url, '/') ? $url : '/' . $url);
    }

    /**
     * URL логотипа для элемента баланса (из API ноды).
     * Использует logo_url из ответа; для нативных монет GND/GANI — запасной путь, если logo_url пустой.
     *
     * @param  array|object  $balanceItem  элемент из data.balances (balance, symbol, name, logo_url, ...)
     */
    public static function logoUrlForBalanceItem($balanceItem): ?string
    {
        $item = is_array($balanceItem) ? (object) $balanceItem : $balanceItem;
        $logoUrl = $item->logo_url ?? null;
        if ($logoUrl !== null && trim((string) $logoUrl) !== '') {
            return self::logoUrlForDisplay(trim((string) $logoUrl));
        }
        $symbol = $item->symbol ?? '';
        $symbol = strtoupper(trim((string) $symbol));
        if (in_array($symbol, ['GND', 'GANI'], true)) {
            return self::logoUrlForDisplay('/api/v1/uploads/coin_logos/' . strtolower($symbol) . '.png');
        }
        return null;
    }

    /**
     * Разбивает целую часть числа пробелами по разрядам (тысячи, миллионы).
     * Например: "1000000" → "1 000 000". Работает со строкой, без потери точности для длинных чисел.
     */
    public static function thousandsWithSpaces(string $number): string
    {
        $number = trim((string) $number);
        if ($number === '' || ! preg_match('/^\d+$/', $number)) {
            return '0';
        }
        $len = strlen($number);
        if ($len <= 3) {
            return $number;
        }
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            if ($i > 0 && ($len - $i) % 3 === 0) {
                $result .= ' ';
            }
            $result .= $number[$i];
        }
        return $result;
    }

    /**
     * Форматирует баланс: строка целых единиц (например "1000000000000000000")
     * и decimals (например 18) → "1,000000000000000000". Целая часть разбивается пробелами по тысячам.
     */
    public static function balanceWithDecimals(string $balance, int $decimals, string $separator = ','): string
    {
        $balance = trim((string) $balance);
        if ($balance === '') {
            $balance = '0';
        }
        if (! preg_match('/^\d+$/', $balance)) {
            $balance = '0';
        }
        if ($decimals <= 0) {
            return self::thousandsWithSpaces($balance);
        }
        $len = strlen($balance);
        if ($len > $decimals) {
            $frac = substr($balance, -$decimals);
            $int = substr($balance, 0, $len - $decimals) ?: '0';
            return self::thousandsWithSpaces($int) . $separator . $frac;
        }
        $frac = str_pad($balance, $decimals, '0', STR_PAD_LEFT);
        return '0' . $separator . $frac;
    }

    /**
     * Проверяет, является ли строка адресом смарт-контракта ГАНИМЕД (формат: префикс + 32 hex).
     * Соответствует GND_v1 types.IsContractAddress.
     */
    public static function isContractAddress(?string $address): bool
    {
        $address = $address === null ? '' : trim($address);
        if ($address === '') {
            return false;
        }
        $prefix = config('gnd.contract_address_prefix', 'GNDct');
        $length = config('gnd.contract_address_length', 37);
        $hexLen = config('gnd.contract_address_hex_length', 32);
        if (strlen($address) !== $length || ! str_starts_with($address, $prefix)) {
            return false;
        }
        $suffix = substr($address, strlen($prefix));

        return strlen($suffix) === $hexLen && ctype_xdigit($suffix);
    }
}

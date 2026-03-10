<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Локальная запись о переводе (кошелёк → кошелёк): токен или монета, сумма, nonce (опционально).
 */
class Transaction extends Model
{
    protected $fillable = [
        'from_address',
        'to_address',
        'token_address',
        'coin_symbol',
        'amount',
        'nonce',
        'tx_hash',
        'status',
        'error_message',
    ];
}

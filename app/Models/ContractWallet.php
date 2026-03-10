<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractWallet extends Model
{
    protected $fillable = ['contract_id', 'wallet_address'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}

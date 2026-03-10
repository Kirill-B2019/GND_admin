<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        'name',
        'address',
        'node_contract_id',
        'description',
        'source_code',
        'bytecode_hex',
        'abi_json',
        'status',
        'compiler_version',
        'metadata_cid',
        'source_cid',
        'bytecode_cid',
        'deployer_address',
        'block_id',
        'tx_id',
        'analysis_report',
        'standard',
        'metadata_json',
        'state_snapshot',
        'state_snapshot_at',
    ];

    protected $casts = [
        'analysis_report' => 'array',
        'metadata_json' => 'array',
        'state_snapshot' => 'array',
        'state_snapshot_at' => 'datetime',
        'node_contract_id' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ANALYZED = 'analyzed';
    public const STATUS_DEPLOYED = 'deployed';
    public const STATUS_VERIFIED = 'verified';

    public function managedWallets(): HasMany
    {
        return $this->hasMany(ContractWallet::class);
    }

    public function isDeployed(): bool
    {
        return in_array($this->status, [self::STATUS_DEPLOYED, self::STATUS_VERIFIED], true)
            && ! empty($this->address);
    }
}

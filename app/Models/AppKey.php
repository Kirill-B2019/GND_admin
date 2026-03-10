<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AppKey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'key_hash',
        'key_prefix',
        'expires_at',
        'disabled',
        'created_by',
    ];

    protected $casts = [
        'disabled' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateKey(): string
    {
        return 'gnd_'.Str::random(48);
    }

    public static function hashKey(string $key): string
    {
        return hash('sha256', $key);
    }

    public static function keyPrefix(string $key): string
    {
        return strlen($key) >= 4 ? substr($key, -4) : '****';
    }
}

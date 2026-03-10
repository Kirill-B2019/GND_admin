<?php

namespace App\Models;

use App\Helpers\FormatHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Token extends Model
{
    protected $fillable = [
        'token_address',
        'owner_address',
        'symbol',
        'name',
        'decimals',
        'standard',
        'total_supply',
        'description',
        'logo_path',
        'logo_url',
        'metadata_json',
        'last_synced_at',
        'is_verified',
        'sort_order',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'last_synced_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function logoUrl(): ?string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }
        if (! empty(trim((string) $this->logo_url))) {
            return FormatHelper::logoUrlForDisplay($this->logo_url);
        }
        // Запасной путь для нативных монет (логотипы на ноде)
        $symbol = strtoupper(trim((string) ($this->symbol ?? '')));
        if (in_array($symbol, ['GND', 'GANI'], true)) {
            return FormatHelper::logoUrlForDisplay('/api/v1/uploads/coin_logos/' . strtolower($symbol) . '.png');
        }
        return null;
    }
}

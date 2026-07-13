<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskWeight extends Model
{
    protected $fillable = [
        'weather_weight', 'inflation_weight', 'political_weight',
        'currency_weight', 'is_active', 'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function active(): self
    {
        return static::where('is_active', true)->latest()->first()
            ?? static::create([
                'weather_weight' => 30,
                'inflation_weight' => 20,
                'political_weight' => 40,
                'currency_weight' => 10,
                'is_active' => true,
            ]);
    }
}

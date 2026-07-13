<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id', 'weather_score', 'inflation_score', 'political_score',
        'currency_score', 'total_score', 'risk_level', 'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}

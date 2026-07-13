<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'temperature',
        'rainfall',
        'wind_speed',
        'storm_risk',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at'  => 'datetime',
        'temperature' => 'float',
        'rainfall'    => 'float',
        'wind_speed'  => 'float',
        'storm_risk'  => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
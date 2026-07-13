<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryEconomicData extends Model
{
    use HasFactory;

    protected $table = 'country_economic_data';

    protected $fillable = [
        'country_id',
        'year',
        'gdp',
        'inflation',
        'population',
        'exports',
        'imports',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
        'gdp'        => 'float',
        'inflation'  => 'float',
        'population' => 'integer',
        'exports'    => 'float',
        'imports'    => 'float',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
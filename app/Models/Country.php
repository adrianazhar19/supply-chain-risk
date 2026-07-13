<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Country extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'code',
        'currency',
        'region',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];


    // Data ekonomi
    public function economic()
    {
        return $this->hasOne(
            CountryEconomicData::class
        );
    }


    // Data cuaca
    public function weatherSnapshots()
    {
        return $this->hasMany(
            WeatherSnapshot::class
        );
    }


    // Kurs
    public function exchangeRates()
    {
        return $this->hasMany(
            ExchangeRate::class
        );
    }


    // Berita
    public function newsArticles()
    {
        return $this->hasMany(
            NewsArticle::class
        );
    }


    // Risk Score
    public function riskScores()
    {
        return $this->hasMany(
            RiskScore::class
        );
    }


    // Watchlist
    public function watchlists()
    {
        return $this->hasMany(
            Watchlist::class
        );
    }


    // Pelabuhan
    public function ports()
    {
        return $this->hasMany(
            Port::class
        );
    }

}
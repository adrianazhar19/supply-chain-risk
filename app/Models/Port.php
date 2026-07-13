<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'wpi_code',
        'latitude',
        'longitude',
        'harbor_size',
        'harbor_type',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
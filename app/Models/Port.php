<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'latitude',
        'longitude',
        'harbor_type',
        'harbor_size',
        'wpi_code'
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
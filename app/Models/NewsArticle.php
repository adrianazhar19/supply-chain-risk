<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'title',
        'description',
        'url',
        'source_name',
        'image_url',
        'category',
        'published_at',
        'positive_score',
        'negative_score',
        'sentiment',
        'fetched_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at'   => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
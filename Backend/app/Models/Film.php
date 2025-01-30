<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
/** @use HasFactory<\Database\Factories\FilmFactory> */
use HasFactory;

//Ha nem lennének tiestamps mezők
//public $timestamps = false;

protected $fillable = [
    'id',
    'title',
    'production',
    'length',
    'presentation',
    'imbdLink',
];

protected function casts(): array
{
    return [
        'presentation' => 'date',
    ];
}
}

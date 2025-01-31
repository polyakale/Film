<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    /** @use HasFactory<\Database\Factories\PersonFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'gender',
        'photo',
        'imdbLink',
    ];

    protected function casts(): array
    {
        return [
            'gender' => 'boolean',
        ];
    }
}

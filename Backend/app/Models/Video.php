<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'filmId',
        'link',
        'embedLink',
    ];
}

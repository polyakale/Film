<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Favourite extends Model
{
    /** @use HasFactory<\Database\Factories\FavouriteFactory> */
    use HasFactory, Notifiable;
    public $timestamps = true;
    protected $fillable = ['id', 'userId', 'filmId', 'evaluation'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function film()
    {
        return $this->belongsTo(Film::class, 'filmId');
    }
}
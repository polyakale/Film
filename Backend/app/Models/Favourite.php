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
    //protected $guarded = [];
    protected $fillable = ['id', 'userId', 'filmId', 'evaluation'];
}

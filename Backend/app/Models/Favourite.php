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
    protected $fillable = ['id', 'userId', 'filmId', 'evaluation', 'content'];

    // Define the relationship to the user
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    // Define the relationship to the film
    public function film()
    {
        return $this->belongsTo(Film::class, 'filmId');
    }

    // protected function casts(): array
    // {
    //     return [
    //         'evaluation' => 'double'
           
    //     ];
    // }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Film extends Model
{
    /** @use HasFactory<\Database\Factories\FilmFactory> */
    use HasFactory, Notifiable;
    public $timestamps = false;
    protected $fillable = ['id', 'title', 'production', 'length', 'presentation', 'imdbLink'];

    protected function casts(): array
    {
        return ['presentation' => 'date'];
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'filmId');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'filmId');
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class, 'filmId');
    }
}
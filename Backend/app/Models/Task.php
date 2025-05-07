<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, Notifiable;
    public $timestamps = false;
    protected $fillable = ['id', 'filmId', 'personId', 'roleId'];

    // Needed for DatabaseTest
    public function film()
    {
        return $this->belongsTo(Film::class, 'filmId');
    }
    public function person()
    {
        return $this->belongsTo(Person::class, 'personId');
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

}
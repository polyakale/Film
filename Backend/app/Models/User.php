<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['id','positionId', 'name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token', 'email_verified_at', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'positionId' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Remove the position relationship (no Position model needed)
    // public function position()
    // {
    //     return $this->belongsTo(Position::class, 'positionId');
    // }

    public function favourites()
    {
        return $this->hasMany(Favourite::class, 'userId');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'positionId');
    }
}

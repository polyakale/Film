<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;
    protected $table = 'roles';
    protected $fillable = ['id', 'role'];
    public $timestamps = true;

    // Needed for DatabaseTest
    public function tasks()
    {
        return $this->hasMany(Task::class, 'roleId');
    }

}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
        'is_admin' => 'boolean', // To easily check if user is admin
    ];

    // Clubs where this user is the leader
    public function ledClubs()
    {
        return $this->hasMany(Club::class, 'leader_id');
    }

    // Clubs where this user is a member or applied to
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_user')
                    ->withPivot('status', 'joined_at')
                    ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
}
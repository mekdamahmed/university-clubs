<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use SoftDeletes; // Enables soft delete

    protected $fillable = ['name', 'description', 'leader_id'];

    // The leader of the club
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    // All members (including pending ones)
    public function members()
    {
        return $this->belongsToMany(User::class, 'club_user')
                    ->withPivot('status', 'joined_at', 'id')
                    ->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}
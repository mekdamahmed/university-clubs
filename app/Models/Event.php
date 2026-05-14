<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes; 

protected $fillable = ['club_id', 'title', 'description', 'event_date', 'location'];
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
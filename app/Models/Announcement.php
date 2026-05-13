<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['club_id', 'author_id', 'title', 'content'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
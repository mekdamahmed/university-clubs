<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    protected $fillable = ['club_id', 'assigned_to', 'title', 'description', 'is_completed', 'due_date'];
    
    // Add dynamic attribute to JSON response
    protected $appends = ['status_label'];

    public function club() { return $this->belongsTo(Club::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }

    // Dynamically calculate if task is Pending, Completed, or Failed
    public function getStatusLabelAttribute()
    {
        if ($this->is_completed) {
            return 'Completed';
        }
        if (Carbon::parse($this->due_date)->isPast()) {
            return 'Failed'; // Deadline passed
        }
        return 'Pending';
    }
}
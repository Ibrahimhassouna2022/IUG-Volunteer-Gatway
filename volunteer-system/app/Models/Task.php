<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'assigned_by',
        'assigned_to',
        'start_date',
        'end_date',
        'estimated_hours',
        'actual_hours',
        'volunteer_notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     */
    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHour::class);
    }
}

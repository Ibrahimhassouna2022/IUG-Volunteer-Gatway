<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'date',
        'hours',
        'notes',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
    ];

    /**
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

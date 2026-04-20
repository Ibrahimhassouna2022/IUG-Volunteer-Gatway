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
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * المشرف الذي أنشأ المهمة.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * المتطوع المسؤول عن تنفيذ المهمة.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * حركات ساعات التطوع المرتبطة بالمهمة.
     */
    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHour::class);
    }
}

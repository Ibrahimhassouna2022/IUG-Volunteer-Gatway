<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'supervisor_id',
        'is_active',
    ];

    /**
     * المشرف على الفريق.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * أعضاء الفريق (طلاب/متطوعون).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    /**
     * المهام المرتبطة بالفريق (اختياري إذا كانت المهام تتبع فرقاً).
     */
    public function tasks()
    {
        // إذا كان الجدول tasks يحتوي على team_id لاحقاً
        // return $this->hasMany(Task::class);
        
        // حالياً المهام فردية، لكن يمكن للمشرف رؤية مهام أعضاء فريقه
        return Task::whereIn('assigned_to', $this->members()->pluck('users.id'));
    }
}

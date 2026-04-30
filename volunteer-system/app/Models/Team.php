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
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    /**
     */
    public function tasks()
    {
        // return $this->hasMany(Task::class);
        
        return Task::whereIn('assigned_to', $this->members()->pluck('users.id'));
    }
}

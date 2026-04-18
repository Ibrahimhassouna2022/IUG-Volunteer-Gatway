<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * الحقول المسموح بتعبئتها
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'university_id',
        'phone',
        'role',
        'is_active',
        'joined_at',
    ];

    /**
     * الحقول المخفية
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * تحويلات الأنواع
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
    ];

    // ========================================
    // دوال التحقق من الصلاحيات
    // ========================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isVolunteer(): bool
    {
        return $this->role === 'volunteer';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

   
    // العلاقات مع الجداول الأخرى


    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHour::class);
    }
}
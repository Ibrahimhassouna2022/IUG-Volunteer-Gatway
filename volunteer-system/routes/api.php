<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Supervisor\ActivityApprovalController;
use App\Http\Controllers\Supervisor\SupervisorReportController;
use App\Http\Controllers\Supervisor\TaskAssignmentController;
use App\Http\Controllers\Supervisor\TeamController;

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSupervisorController;
use App\Http\Controllers\Admin\AdminSystemSettingsController;
use App\Http\Controllers\Admin\AdminReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// مسارات المشرف (Supervisor Routes)
Route::middleware(['auth:sanctum', 'role:supervisor'])->prefix('supervisor')->group(function () {
    
    // إدارة الفرق والمتطوعين
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

    // توزيع المهام
    Route::apiResource('tasks', TaskAssignmentController::class)->except(['show', 'update']);
    Route::put('tasks/{task}/status', [TaskAssignmentController::class, 'updateStatus']);

    // إدارة الحركات اليومية (الاعتمادات)
    Route::get('activities/pending', [ActivityApprovalController::class, 'pending']);
    Route::post('activities/{volunteerHour}/approve', [ActivityApprovalController::class, 'approve']);
    Route::put('activities/{volunteerHour}', [ActivityApprovalController::class, 'update']);

    // التقارير والإحصائيات
    Route::get('reports/team-stats', [SupervisorReportController::class, 'teamStats']);
    Route::get('reports/teams/{team}/efficiency', [SupervisorReportController::class, 'taskEfficiency']);
});

// مسارات مدير النظام (Admin Routes)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    
    // إدارة المستخدمين والمشرفين
    Route::apiResource('users', AdminUserController::class);
    Route::apiResource('supervisors', AdminSupervisorController::class);
    
    // إدارة إعدادات النظام
    Route::get('settings', [AdminSystemSettingsController::class, 'index']);
    Route::get('settings/{key}', [AdminSystemSettingsController::class, 'show']);
    Route::put('settings/bulk', [AdminSystemSettingsController::class, 'updateBulk']);
    Route::put('settings/{systemSetting}', [AdminSystemSettingsController::class, 'update']);
    
    // التقارير والإحصائيات الكلية
    Route::get('reports/dashboard', [AdminReportController::class, 'dashboardStats']);
    Route::get('reports/teams-performance', [AdminReportController::class, 'teamPerformanceReport']);
    Route::get('reports/monthly-hours', [AdminReportController::class, 'monthlyHoursChart']);
});

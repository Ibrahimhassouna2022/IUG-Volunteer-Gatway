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

Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'apiLogin']);
Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'apiRegister']);
Route::middleware('auth:sanctum')->post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'apiLogout']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'role:supervisor'])->prefix('supervisor')->group(function () {
    
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

    Route::apiResource('tasks', TaskAssignmentController::class)->except(['show', 'update']);
    Route::put('tasks/{task}/status', [TaskAssignmentController::class, 'updateStatus']);

    Route::get('activities/pending', [ActivityApprovalController::class, 'pending']);
    Route::post('activities/{volunteerHour}/approve', [ActivityApprovalController::class, 'approve']);
    Route::put('activities/{volunteerHour}', [ActivityApprovalController::class, 'update']);

    Route::get('reports/team-stats', [SupervisorReportController::class, 'teamStats']);
    Route::get('reports/teams/{team}/efficiency', [SupervisorReportController::class, 'taskEfficiency']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    
    Route::apiResource('users', AdminUserController::class);
    Route::apiResource('supervisors', AdminSupervisorController::class);
    
    Route::get('settings', [AdminSystemSettingsController::class, 'index']);
    Route::get('settings/{key}', [AdminSystemSettingsController::class, 'show']);
    Route::put('settings/bulk', [AdminSystemSettingsController::class, 'updateBulk']);
    Route::put('settings/{systemSetting}', [AdminSystemSettingsController::class, 'update']);
    
    Route::get('reports/dashboard', [AdminReportController::class, 'dashboardStats']);
    Route::get('reports/teams-performance', [AdminReportController::class, 'teamPerformanceReport']);
    Route::get('reports/monthly-hours', [AdminReportController::class, 'monthlyHoursChart']);
});


// ===============================
// ===============================
Route::middleware(['auth:sanctum', 'role:volunteer'])
    ->prefix('volunteer')->name('volunteer.')->group(function () {
        
        Route::get('/dashboard', [\App\Http\Controllers\Volunteer\VolunteerController::class, 'index'])->name('dashboard');
        
        Route::get('/tasks/{taskId}', [\App\Http\Controllers\Volunteer\VolunteerController::class, 'showTask'])->name('task.show');
        
        Route::post('/tasks/{taskId}/hours', [\App\Http\Controllers\Volunteer\VolunteerController::class, 'storeHours'])->name('task.hours.store');
        
        Route::post('/tasks/{taskId}/complete', [\App\Http\Controllers\Volunteer\VolunteerController::class, 'completeTask'])->name('task.complete');
        
        Route::get('/reports', [\App\Http\Controllers\Volunteer\VolunteerController::class, 'myReports'])->name('reports');
    });
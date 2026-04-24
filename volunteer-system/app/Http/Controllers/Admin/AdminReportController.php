<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use App\Models\Task;
use App\Models\VolunteerHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    /**
     * لوحة المؤشرات العامة للأدمن.
     */
    public function dashboardStats()
    {
        $stats = [
            'counts' => [
                'total_users' => User::count(),
                'total_volunteers' => User::where('role', 'volunteer')->count(),
                'total_supervisors' => User::where('role', 'supervisor')->count(),
                'total_teams' => Team::count(),
                'active_teams' => Team::where('is_active', true)->count(),
            ],
            'performance' => [
                'total_approved_hours' => VolunteerHour::whereNotNull('approved_by')->sum('hours'),
                'total_tasks' => Task::count(),
                'completed_tasks_percentage' => $this->getCompletedTasksPercentage(),
            ],
            'recent_activity' => $this->getRecentActivity(),
        ];

        return response()->json($stats);
    }

    /**
     * تقرير أداء الفرق للمقارنة.
     */
    public function teamPerformanceReport()
    {
        $teamsReport = Team::withCount('members')
            ->get()
            ->map(function ($team) {
                $memberIds = $team->members()->pluck('users.id');
                
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'members_count' => $team->members_count,
                    'total_hours' => VolunteerHour::whereIn('user_id', $memberIds)
                        ->whereNotNull('approved_by')
                        ->sum('hours'),
                    'tasks_count' => Task::whereIn('assigned_to', $memberIds)->count(),
                    'completed_tasks' => Task::whereIn('assigned_to', $memberIds)
                        ->where('status', 'completed')
                        ->count(),
                ];
            });

        return response()->json($teamsReport);
    }

    /**
     * إحصائيات الساعات التطوعية شهرياً.
     */
    public function monthlyHoursChart()
    {
        $data = VolunteerHour::whereNotNull('approved_by')
            ->select(
                DB::raw('SUM(hours) as total_hours'),
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month")
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json($data);
    }

    private function getCompletedTasksPercentage()
    {
        $total = Task::count();
        if ($total == 0) return 0;
        
        $completed = Task::where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }

    private function getRecentActivity()
    {
        return VolunteerHour::with('user:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'user_id', 'hours', 'date', 'approved_by']);
    }
}

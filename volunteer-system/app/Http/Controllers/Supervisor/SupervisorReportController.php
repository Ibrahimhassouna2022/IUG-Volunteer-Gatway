<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\VolunteerHour;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupervisorReportController extends Controller
{
    /**
     * إحصائيات عامة للفريق خلال فترة زمنية.
     */
    public function teamStats(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $team = Team::findOrFail($request->team_id);
        
        if ($team->supervisor_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بالوصول لبيانات هذا الفريق');
        }

        $memberIds = $team->members()->pluck('users.id');

        // إجمالي ساعات التطوع المعتمدة للفريق
        $totalHours = VolunteerHour::whereIn('user_id', $memberIds)
            ->whereBetween('date', [$request->from_date, $request->to_date])
            ->whereNotNull('approved_by')
            ->sum('hours');

        // حالة المهام للفريق
        $taskStats = Task::whereIn('assigned_to', $memberIds)
            ->whereBetween('created_at', [$request->from_date, $request->to_date])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // إحصائيات لكل متطوع في الفريق
        $memberStats = DB::table('users')
            ->join('team_user', 'users.id', '=', 'team_user.user_id')
            ->leftJoin('volunteer_hours', 'users.id', '=', 'volunteer_hours.user_id')
            ->where('team_user.team_id', $team->id)
            ->whereNotNull('volunteer_hours.approved_by')
            ->whereBetween('volunteer_hours.date', [$request->from_date, $request->to_date])
            ->select('users.name', DB::raw('SUM(volunteer_hours.hours) as total_member_hours'))
            ->groupBy('users.id', 'users.name')
            ->get();

        return response()->json([
            'team_name' => $team->name,
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date
            ],
            'total_approved_hours' => $totalHours,
            'tasks_summary' => $taskStats,
            'members_productivity' => $memberStats
        ]);
    }

    /**
     * تقرير المهام المنجزة مقابل المتوقعة.
     */
    public function taskEfficiency(Team $team)
    {
        if ($team->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $memberIds = $team->members()->pluck('users.id');

        $efficiency = Task::whereIn('assigned_to', $memberIds)
            ->where('status', 'completed')
            ->select(
                'title',
                'estimated_hours',
                'actual_hours',
                DB::raw('(actual_hours / estimated_hours) * 100 as efficiency_percentage')
            )
            ->where('estimated_hours', '>', 0)
            ->get();

        return response()->json($efficiency);
    }
}

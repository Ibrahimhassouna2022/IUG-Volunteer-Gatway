<?php
namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\VolunteerHour;

class VolunteerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $myTasks = Task::where('assigned_to', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalHours = VolunteerHour::where('user_id', $user->id)->sum('hours');
        
        $completedCount = Task::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->count();
        
        $data = [
            'user' => $user,
            'tasks' => $myTasks,
            'totalHours' => $totalHours,
            'completedCount' => $completedCount
        ];
        
        if (request()->wantsJson()) {
            return response()->json($data);
        }
        
        return view('volunteer.dashboard', $data);
    }
    
    public function showTask($taskId)
    {
        $task = Task::where('id', $taskId)
            ->where('assigned_to', Auth::id())
            ->with(['team.supervisor'])
            ->firstOrFail();
        
        $loggedHours = VolunteerHour::where('user_id', Auth::id())
            ->where('task_id', $taskId)
            ->get();
        
        $data = [
            'task' => $task,
            'loggedHours' => $loggedHours
        ];
        
        if (request()->wantsJson()) {
            return response()->json($data);
        }
        
        return view('volunteer.task-details', $data);
    }
    
    public function storeHours(Request $request, $taskId)
    {
        $request->validate([
            'hours' => 'required|numeric|min:0.5|max:24',
            'notes' => 'required|string|max:1000', 
            'date' => 'required|date|before_or_equal:today'
        ]);
        
        $task = Task::where('id', $taskId)
            ->where('assigned_to', Auth::id())
            ->firstOrFail();
        
        if ($task->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تسجيل ساعات عمل إضافية لمهمة مكتملة.'
            ], 422);
        }

        $volunteerHour = VolunteerHour::create([
            'user_id' => Auth::id(),
            'task_id' => $taskId,
            'date' => $request->date,
            'hours' => $request->hours,
            'notes' => $request->notes,
            'approved_by' => null 
        ]);
        
        if ($task->status === 'pending') {
            $task->update(['status' => 'in_progress']);
        }
        
        $message = 'تم تسجيل ساعات العمل بنجاح ونقل المهمة لحالة "قيد التنفيذ".';
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $volunteerHour
            ], 201);
        }
        
        return redirect()->route('volunteer.dashboard')->with('success', $message);
    }

    /**
     */
    public function completeTask(Request $request, $taskId)
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000'
        ]);

        $task = Task::where('id', $taskId)
            ->where('assigned_to', Auth::id())
            ->firstOrFail();

        if ($task->status === 'completed') {
            return response()->json([
                'message' => 'المهمة مكتملة بالفعل.'
            ], 400);
        }

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'volunteer_notes' => $request->notes ?? $task->volunteer_notes, 
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير حالة المهمة إلى "مكتملة" بنجاح.',
            'task' => $task
        ]);
    }
    
    public function myReports()
    {
        $userId = Auth::id();
        
        $totalHours = VolunteerHour::where('user_id', $userId)->sum('hours');
        
        $approvedHours = VolunteerHour::where('user_id', $userId)
            ->whereNotNull('approved_by')
            ->sum('hours');
        
        $hoursLog = VolunteerHour::where('user_id', $userId)
            ->with(['task'])
            ->orderBy('date', 'desc')
            ->get();
        
        $data = [
            'totalHours' => $totalHours,
            'approvedHours' => $approvedHours,
            'log' => $hoursLog
        ];
        
        if (request()->wantsJson()) {
            return response()->json($data);
        }
        
        return view('volunteer.reports', $data);
    }
}
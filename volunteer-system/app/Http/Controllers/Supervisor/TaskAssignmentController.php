<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskAssignmentController extends Controller
{
    /**
     * عرض المهام التي أنشأها المشرف.
     */
    public function index()
    {
        $tasks = Task::where('assigned_by', Auth::id())
                    ->with('assignee')
                    ->latest()
                    ->get();
                    
        return response()->json($tasks);
    }

    /**
     * إنشاء مهمة جديدة وتعيينها لمتطوع.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|integer',
        ]);

        $assignee = User::findOrFail($request->assigned_to);

        if (!$assignee->isVolunteer()) {
            return response()->json(['message' => 'يمكن تعيين المهام للمتطوعين فقط'], 422);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending',
            'assigned_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'estimated_hours' => $request->estimated_hours ?? 0,
        ]);

        return response()->json([
            'message' => 'تم إنشاء المهمة وتعيينها بنجاح',
            'task' => $task
        ], 201);
    }

    /**
     * تحديث حالة المهمة (نظرياً يقوم المتطوع بذلك، لكن المشرف يمكنه التدخل).
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeSupervisor($task);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update(['status' => $request->status]);

        return response()->json([
            'message' => 'تم تحديث حالة المهمة بنجاح',
            'task' => $task
        ]);
    }

    /**
     * حذف مهمة.
     */
    public function destroy(Task $task)
    {
        $this->authorizeSupervisor($task);
        $task->delete();

        return response()->json(['message' => 'تم حذف المهمة بنجاح']);
    }

    /**
     * التأكد من أن المستخدم الحالي هو الذي أنشأ هذه المهمة.
     */
    protected function authorizeSupervisor(Task $task)
    {
        if ($task->assigned_by !== Auth::id()) {
            abort(403, 'غير مصرح لك بالوصول لهذه المهمة');
        }
    }
}

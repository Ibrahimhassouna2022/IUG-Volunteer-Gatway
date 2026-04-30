<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     */
    public function index()
    {
        $teams = Auth::user()->managedTeams()->withCount('members')->get();
        return response()->json($teams);
    }

    /**
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team = Auth::user()->managedTeams()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'تم إنشاء الفريق بنجاح',
            'team' => $team
        ], 201);
    }

    /**
     */
    public function show(Team $team)
    {
        $this->authorizeSupervisor($team);

        $team->load('members');
        return response()->json($team);
    }

    /**
     */
    public function update(Request $request, Team $team)
    {
        $this->authorizeSupervisor($team);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $team->update($request->only(['name', 'description', 'is_active']));

        return response()->json([
            'message' => 'تم تحديث بيانات الفريق بنجاح',
            'team' => $team
        ]);
    }

    /**
     */
    public function destroy(Team $team)
    {
        $this->authorizeSupervisor($team);
        $team->delete();

        return response()->json(['message' => 'تم حذف الفريق بنجاح']);
    }

    /**
     */
    public function addMember(Request $request, Team $team)
    {
        $this->authorizeSupervisor($team);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$user->isVolunteer()) {
            return response()->json(['message' => 'المستخدم المختار ليس متطوعاً'], 422);
        }

        $team->members()->syncWithoutDetaching([$user->id]);

        return response()->json(['message' => 'تم إضافة المتطوع إلى الفريق بنجاح']);
    }

    /**
     */
    public function removeMember(Team $team, User $user)
    {
        $this->authorizeSupervisor($team);
        $team->members()->detach($user->id);

        return response()->json(['message' => 'تم إزالة المتطوع من الفريق بنجاح']);
    }

    /**
     */
    protected function authorizeSupervisor(Team $team)
    {
        if ($team->supervisor_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بالوصول لهذا الفريق');
        }
    }
}

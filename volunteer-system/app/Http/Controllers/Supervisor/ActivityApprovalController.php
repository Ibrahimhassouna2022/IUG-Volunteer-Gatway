<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\VolunteerHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityApprovalController extends Controller
{
    /**
     * عرض جميع حركات الساعات التي تنتظر الاعتماد للفرق التي يشرف عليها المشرف.
     */
    public function pending()
    {
        // جلب الحركات للمتطوعين الذين ينتمون لفرق يشرف عليها المشرف الحالي
        $teamMemberIds = Auth::user()->managedTeams()
            ->with('members')
            ->get()
            ->pluck('members')
            ->flatten()
            ->pluck('id')
            ->unique();

        $activities = VolunteerHour::whereIn('user_id', $teamMemberIds)
            ->whereNull('approved_by')
            ->with(['user', 'task'])
            ->latest()
            ->get();

        return response()->json($activities);
    }

    /**
     * اعتماد حركة ساعات معينة.
     */
    public function approve(VolunteerHour $volunteerHour)
    {
        $this->authorizeAccess($volunteerHour);

        $volunteerHour->update([
            'approved_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'تم اعتماد الحركة بنجاح',
            'activity' => $volunteerHour
        ]);
    }

    /**
     * رفض أو تعديل حركة (يمكن للمشرف تعديل الساعات قبل الاعتماد).
     */
    public function update(Request $request, VolunteerHour $volunteerHour)
    {
        $this->authorizeAccess($volunteerHour);

        $request->validate([
            'hours' => 'required|numeric|min:0.5',
            'notes' => 'nullable|string',
        ]);

        $volunteerHour->update([
            'hours' => $request->hours,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'تم تحديث الحركة بنجاح',
            'activity' => $volunteerHour
        ]);
    }

    /**
     * التحقق من أن المتطوع صاحب الحركة ينتمي لأحد فرق المشرف.
     */
    protected function authorizeAccess(VolunteerHour $volunteerHour)
    {
        $isMember = Auth::user()->managedTeams()
            ->whereHas('members', function($query) use ($volunteerHour) {
                $query->where('users.id', $volunteerHour->user_id);
            })->exists();

        if (!$isMember) {
            abort(403, 'غير مصرح لك بالوصول لحركات هذا المتطوع');
        }
    }
}

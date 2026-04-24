<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * عرض جميع المستخدمين مع إمكانية التصفية حسب الدور أو الحالة.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->latest()->paginate(20);
        return response()->json($users);
    }

    /**
     * إنشاء مستخدم جديد (من قبل الأدمن).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'university_id' => 'nullable|string|unique:users',
            'phone' => 'nullable|string',
            'role' => ['required', Rule::in(['admin', 'supervisor', 'volunteer'])],
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'university_id' => $request->university_id,
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->is_active ?? true,
            'joined_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم إنشاء المستخدم بنجاح',
            'user' => $user
        ], 201);
    }

    /**
     * عرض تفاصيل مستخدم معين.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * تحديث بيانات مستخدم.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'university_id' => ['sometimes', 'nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'phone' => 'sometimes|nullable|string',
            'role' => ['sometimes', Rule::in(['admin', 'supervisor', 'volunteer'])],
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($request->only([
            'name', 'email', 'university_id', 'phone', 'role', 'is_active'
        ]));

        if ($request->has('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'message' => 'تم تحديث بيانات المستخدم بنجاح',
            'user' => $user
        ]);
    }

    /**
     * حذف مستخدم.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'لا يمكنك حذف حسابك الشخصي'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'تم حذف المستخدم بنجاح']);
    }
}

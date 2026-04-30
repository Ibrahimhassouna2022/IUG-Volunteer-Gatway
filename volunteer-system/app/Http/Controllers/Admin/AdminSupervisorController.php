<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminSupervisorController extends Controller
{
    /**
     */
    public function index()
    {
        $supervisors = User::where('role', 'supervisor')
            ->withCount('managedTeams')
            ->latest()
            ->get();
            
        return response()->json($supervisors);
    }

    /**
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
        ]);

        $supervisor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'supervisor',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم إضافة المشرف بنجاح',
            'supervisor' => $supervisor
        ], 201);
    }

    /**
     */
    public function show(User $supervisor)
    {
        if (!$supervisor->isSupervisor()) {
            return response()->json(['message' => 'هذا المستخدم ليس مشرفاً'], 404);
        }

        $supervisor->load('managedTeams');
        return response()->json($supervisor);
    }

    /**
     */
    public function update(Request $request, User $supervisor)
    {
        if (!$supervisor->isSupervisor()) {
            return response()->json(['message' => 'هذا المستخدم ليس مشرفاً'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($supervisor->id)],
            'phone' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $supervisor->update($request->only(['name', 'email', 'phone', 'is_active']));

        if ($request->has('password')) {
            $supervisor->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'message' => 'تم تحديث بيانات المشرف بنجاح',
            'supervisor' => $supervisor
        ]);
    }

    /**
     */
    public function destroy(User $supervisor)
    {
        if (!$supervisor->isSupervisor()) {
            return response()->json(['message' => 'هذا المستخدم ليس مشرفاً'], 404);
        }

        $supervisor->delete();
        return response()->json(['message' => 'تم حذف حساب المشرف بنجاح']);
    }
}

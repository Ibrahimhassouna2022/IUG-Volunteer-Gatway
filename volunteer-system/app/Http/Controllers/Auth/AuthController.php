<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     */
    public function login(Request $request)
    {
        $request->validate([
            'selected_role' => 'required|in:volunteer,supervisor,admin',
        ], [
            'selected_role.required' => 'يجب اختيار نوع الحساب قبل الدخول',
            'selected_role.in' => 'نوع الحساب المحدد غير معروف',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            
            $request->session()->regenerate();
            
            $user = Auth::user();

            if ($user->role !== $request->selected_role) {
                Auth::logout();
                return back()->withErrors(['email' => 'نوع الحساب المختار لا يتطابق مع بيانات هذا المستخدم.']);
            }

            return match ($user->role) {
                'admin' => redirect()->intended('/admin/dashboard'),
                'supervisor' => redirect()->intended('/supervisor/dashboard'),
                default => redirect()->intended('/volunteer/dashboard'),
            };
        }

        return back()->withErrors(['email' => 'خطأ في اسم المستخدم أو كلمة المرور.']);
    }

    /**
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
            'message' => 'تم تسجيل الدخول بنجاح'
        ]);
    }

    /**
     */
    public function apiRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'university_id' => 'required|string|unique:users',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'university_id' => $request->university_id,
            'phone' => $request->phone,
            'role' => 'volunteer', 
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
            'message' => 'تم إنشاء الحساب بنجاح'
        ], 201);
    }

    /**
     */
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
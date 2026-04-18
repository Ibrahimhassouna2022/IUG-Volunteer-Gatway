<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * معالجة عملية تسجيل الدخول
     */
    public function login(Request $request)
    {
        // 1. التحقق من اختيار نوع الحساب (إجباري)
        $request->validate([
            'selected_role' => 'required|in:volunteer,supervisor,admin',
        ], [
            'selected_role.required' => 'يجب اختيار نوع الحساب قبل الدخول',
            'selected_role.in' => 'نوع الحساب المحدد غير معروف',
        ]);

        // 2. محاولة المصادقة (البريد + كلمة المرور)
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            
            // تجديد الجلسة
            $request->session()->regenerate();
            
            $user = Auth::user();

            // 3. التأكد إن الدور المختار يطابق الدور الفعلي
            if ($user->role !== $request->selected_role) {
                Auth::logout();
                return back()->withErrors(['email' => 'نوع الحساب المختار لا يتطابق مع بيانات هذا المستخدم.']);
            }

            // 4. التوجيه حسب الدور
            return match ($user->role) {
                'admin' => redirect()->intended('/admin/dashboard'),
                'supervisor' => redirect()->intended('/supervisor/dashboard'),
                default => redirect()->intended('/volunteer/dashboard'),
            };
        }

        // فشل المصادقة
        return back()->withErrors(['email' => 'خطأ في اسم المستخدم أو كلمة المرور.']);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
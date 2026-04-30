<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Auth\AuthController;


Route::get('/create-admin', function () {
    
    $user = User::firstOrNew(['email' => 'admin@iug.edu']);
    
    $user->name = 'مدير النظام';
    $user->password = Hash::make('password'); 
    $user->role = 'admin';
    $user->is_active = true;
    
    $user->save();
    
    return ' تم إنشاء المستخدم بنجاح! <br> البريد: admin@iug.edu <br> كلمة المرور: password <br><br> <a href="/login">اضغطي هنا للدخول</a>';
});

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');


Route::post('/login', function (Request $request) {
    $attempt = Auth::attempt(['email' => $request->email, 'password' => $request->password]);
    
    if ($attempt) {
        $request->session()->regenerate();
        return redirect()->intended('/welcome');
    }
    
    return back()->withErrors(['email' => 'خطأ في البيانات']);
});

Route::get('/welcome', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal; color:green"> تم الدخول بنجاح! أهلاً بكِ يا ' . Auth::user()->name . '</h1><br><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">تسجيل خروج</a></div>';
})->middleware('auth');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/admin/dashboard', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal"> لوحة تحكم المدير</h1><p style="text-align:center">أهلاً بك يا ' . Auth::user()->name . '</p><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">خروج</a></div>';
})->middleware('auth');

Route::get('/supervisor/dashboard', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal; color:#198754"> لوحة تحكم المشرف</h1><p style="text-align:center">أهلاً بك يا ' . Auth::user()->name . '</p><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">خروج</a></div>';
})->middleware('auth');

Route::get('/volunteer/dashboard', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal; color:#0d6efd"> صفحة المتطوع</h1><p style="text-align:center">أهلاً بك يا ' . Auth::user()->name . '</p><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">خروج</a></div>';
})->middleware('auth');

Route::post('/login', function (Request $request) {
    
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        
        $request->session()->regenerate();
        
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return redirect()->intended('/admin/dashboard');
        } elseif ($user->role === 'supervisor') {
            return redirect()->intended('/supervisor/dashboard');
        } else {
            return redirect()->intended('/volunteer/dashboard');
        }
    }
    
    return back()->withErrors(['email' => 'خطأ في اسم المستخدم أو كلمة المرور']);
});
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    return back()->with('status', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني بنجاح.');
})->name('password.email');



Route::get('/test-volunteer', function() {
    $user = \App\Models\User::where('role', 'volunteer')->first();
    if($user) {
        auth()->login($user);
        return redirect('/volunteer/dashboard');
    }
    return 'لا يوجد متطوع تجريبي';
});
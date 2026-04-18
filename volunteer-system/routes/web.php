<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Auth\AuthController;


//  Route مؤقت لإنشاء الأدمن (نشغله مرة واحدة بس!)
Route::get('/create-admin', function () {
    
    // نتأكد إذا المستخدم موجود عشان ما نعمل تكرار
    $user = User::firstOrNew(['email' => 'admin@iug.edu']);
    
    // نعبئ البيانات (حتى لو الجدول ناقص أعمدة، هاد الكود آمن)
    $user->name = 'مدير النظام';
    $user->password = Hash::make('password'); // تشفير كلمة المرور: password
    $user->role = 'admin';
    $user->is_active = true;
    
    $user->save();
    
    return ' تم إنشاء المستخدم بنجاح! <br> البريد: admin@iug.edu <br> كلمة المرور: password <br><br> <a href="/login">اضغطي هنا للدخول</a>';
});

//  الصفحة الرئيسية -> توجيه للدخول
Route::get('/', function () {
    return redirect('/login');
});

//  عرض صفحة الدخول
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');


//  معالجة الدخول (مع طباعة للخطأ عشان نشوف السبب لو فشلت)
Route::post('/login', function (Request $request) {
    // محاولة الدخول
    $attempt = Auth::attempt(['email' => $request->email, 'password' => $request->password]);
    
    if ($attempt) {
        $request->session()->regenerate();
        return redirect()->intended('/welcome');
    }
    
    // لو فشل، بنعرض رسالة الخطأ
    return back()->withErrors(['email' => 'خطأ في البيانات']);
});

//  صفحة الترحيب بعد النجاح
Route::get('/welcome', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal; color:green"> تم الدخول بنجاح! أهلاً بكِ يا ' . Auth::user()->name . '</h1><br><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">تسجيل خروج</a></div>';
})->middleware('auth');

// تسجيل الخروج 
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// لوحة المدير
Route::get('/admin/dashboard', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal"> لوحة تحكم المدير</h1><p style="text-align:center">أهلاً بك يا ' . Auth::user()->name . '</p><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">خروج</a></div>';
})->middleware('auth');

// لوحة المشرف
Route::get('/supervisor/dashboard', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal; color:#198754"> لوحة تحكم المشرف</h1><p style="text-align:center">أهلاً بك يا ' . Auth::user()->name . '</p><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">خروج</a></div>';
})->middleware('auth');

// لوحة المتطوع
Route::get('/volunteer/dashboard', function () {
    return '<h1 style="text-align:center; margin-top:100px; font-family:Tajawal; color:#0d6efd"> صفحة المتطوع</h1><p style="text-align:center">أهلاً بك يا ' . Auth::user()->name . '</p><div style="text-align:center"><a href="/logout" style="padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px">خروج</a></div>';
})->middleware('auth');

//  معالجة الدخول (مع توجيه حسب الدور)
Route::post('/login', function (Request $request) {
    
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        
        $request->session()->regenerate();
        
        $user = Auth::user();
        
        // توجيه حسب نوع المستخدم
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
//  عرض صفحة نسيت كلمة المرور
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

//  معالجة طلب استعادة كلمة المرور (محاكاة)
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    // هنا عادة بنرسل إيميل، بس للمحاكاة رح نظهر رسالة نجاح
    // في الحقيقة، يمكنك إضافة المنطق هنا
    return back()->with('status', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني بنجاح.');
})->name('password.email');
{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - نظام العمل التطوعي</title>
    
    <!-- Google Fonts: Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- رابط ملف الـ CSS باستخدام دالة asset في لارافيل --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <main class="login-card">

        <!-- Header Section -->
        <header class="login-header">
            <div class="login-title">
                <i class="fa-solid fa-shield"></i>
                <h2>تسجيل الدخول</h2>
            </div>
            <p>قم بإدخال اسم المستخدم وكلمة المرور</p>
        </header>

        <section class="login-body">

            {{-- عرض رسالة الخطأ من لارافيل --}}
            @if ($errors->any())
                <div class="alert-error" style="display: flex;" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- User Role Selection (نفس الكود الأصلي) -->
            <div class="form-group service-select-group">
                <label class="section-label">نوع الحساب</label>
                <div class="selected-service-box" id="service-box">

                    <div class="service-info">
                        <div class="service-icon">
                            <i class="fa-solid fa-graduation-cap" id="selected-icon"></i>
                        </div>
                        <div class="service-text">
                            <strong id="selected-title">الطالب / المتطوع</strong>
                            <span id="selected-desc">نظام إدارة العمل التطوعي</span>
                        </div>
                    </div>

                    <button type="button" class="btn-change" id="btn-change-role">
                        <i class="fa-solid fa-right-left"></i> تغيير
                    </button>

                    <menu class="role-dropdown" id="role-dropdown">
                        <li class="role-option active" data-role="student" data-title="الطالب / المتطوع"
                            data-desc="نظام إدارة العمل التطوعي" data-icon="fa-graduation-cap">
                            <div class="role-option-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                            <div class="role-option-text">الطالب / المتطوع</div>
                        </li>
                        <li class="role-option" data-role="supervisor" data-title="المشرف"
                            data-desc="لوحة تحكم المشرفين" data-icon="fa-user-tie">
                            <div class="role-option-icon"><i class="fa-solid fa-user-tie"></i></div>
                            <div class="role-option-text">المشرف</div>
                        </li>
                        <li class="role-option" data-role="admin" data-title="المدير (الآدمن)"
                            data-desc="لوحة تحكم إدارة النظام" data-icon="fa-user-shield">
                            <div class="role-option-icon"><i class="fa-solid fa-user-shield"></i></div>
                            <div class="role-option-text">المدير (الآدمن)</div>
                        </li>
                    </menu>
                </div>
            </div>

            {{-- فورم الدخول مع تعديلات لارافيل --}}
            <form action="{{ route('login') }}" method="POST" id="login-form">
                @csrf {{-- رمز الحماية الإلزامي في لارافيل --}}
                
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" 
                           name="email" 
                           id="username" 
                           class="form-control" 
                           placeholder="أدخل اسم المستخدم" 
                           value="{{ old('email') }}"
                           required>
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control" 
                           placeholder="أدخل كلمة المرور" 
                           required>
                </div>

                {{-- الروابط المحدثة --}}
                <nav class="forgot-password">
                    <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a> | 
                    <a href="#" onclick="alert('تواصل مع مدير النظام للحصول على كلمة المرور'); return false;">طلب كلمة المرور</a>
                </nav>

                <button type="submit" class="btn-submit">
                    دخول <i class="fa-solid fa-right-to-bracket fa-flip-horizontal" style="margin-right: 5px;"></i>
                </button>

                <!-- رسالة الخطأ الأصلية (مخفية لأن لارافيل يعرضها فوق) -->
                <div class="alert-error" id="error-message" role="alert" style="display: none;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>خطأ في اسم المستخدم أو كلمة المرور</span>
                </div>
            </form>
        </section>
    </main>

    {{-- رابط ملف الجافاسكريبت باستخدام دالة asset --}}
    <script src="{{ asset('js/main.js') }}"></script>
</body>

</html>
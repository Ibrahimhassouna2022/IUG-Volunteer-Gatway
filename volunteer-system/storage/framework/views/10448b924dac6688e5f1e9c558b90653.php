
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>نسيت كلمة المرور - نظام العمل التطوعي</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
</head>
<body>
    <main class="login-card">
        <header class="login-header">
            <div class="login-title">
                <i class="fa-solid fa-key"></i>
                <h2>استعادة كلمة المرور</h2>
            </div>
            <p>أدخل بريدك الإلكتروني لإرسال رابط الاستعادة</p>
        </header>

        <section class="login-body">
            
            
            <?php if(session('status')): ?>
                <div class="alert-error" style="display: flex; background-color: #d1fae5; color: #065f46; border-color: #a7f3d0;" role="alert">
                    <i class="fa-solid fa-check-circle"></i>
                    <span><?php echo e(session('status')); ?></span>
                </div>
                <div style="text-align:center; margin-top:15px;">
                    <a href="<?php echo e(route('login')); ?>" class="btn-submit" style="text-decoration:none; display:inline-block; width:auto; padding:10px 30px;">العودة لتسجيل الدخول</a>
                </div>
            <?php else: ?>

                <form method="POST" action="<?php echo e(route('password.email')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="أدخل بريدك الإلكتروني" value="<?php echo e(old('email')); ?>" required autofocus>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-sm"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <button type="submit" class="btn-submit">
                        إرسال رابط الاستعادة
                    </button>

                    <nav class="forgot-password">
                        <a href="<?php echo e(route('login')); ?>">العودة لتسجيل الدخول</a>
                    </nav>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html><?php /**PATH C:\Users\Asmaa\volunteer-system\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>
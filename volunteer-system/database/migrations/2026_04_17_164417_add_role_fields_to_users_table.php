<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // رقم الجامعة
            $table->string('university_id')->nullable()->unique()->after('email');
            
            // رقم الهاتف
            $table->string('phone')->nullable()->after('university_id');
            
            // الدور: admin, supervisor, volunteer
            $table->enum('role', ['admin', 'supervisor', 'volunteer'])
                  ->default('volunteer')
                  ->after('phone');
            
            // هل الحساب مفعل؟
            $table->boolean('is_active')->default(true)->after('role');
            
            // تاريخ الانضمام
            $table->timestamp('joined_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['university_id', 'phone', 'role', 'is_active', 'joined_at']);
        });
    }
};
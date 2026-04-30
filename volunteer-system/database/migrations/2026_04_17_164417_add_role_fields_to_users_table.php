<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('university_id')->nullable()->unique()->after('email');
            
            $table->string('phone')->nullable()->after('university_id');
            
            $table->enum('role', ['admin', 'supervisor', 'volunteer'])
                  ->default('volunteer')
                  ->after('phone');
            
            $table->boolean('is_active')->default(true)->after('role');
            
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
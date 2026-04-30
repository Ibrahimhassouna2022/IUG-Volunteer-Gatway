<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@iug.edu',
            'password' => Hash::make('password'), // كلمة المرور: password
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
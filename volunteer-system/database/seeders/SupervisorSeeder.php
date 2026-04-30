<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supervisor = User::create([
            'name' => 'المشرف محمد',
            'email' => 'supervisor@iug.edu',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $volunteers = [];
        for ($i = 1; $i <= 5; $i++) {
            $volunteers[] = User::create([
                'name' => "متطوع $i",
                'email' => "volunteer$i@iug.edu",
                'password' => Hash::make('password'),
                'role' => 'volunteer',
                'is_active' => true,
            ]);
        }

        $team = Team::create([
            'name' => 'فريق تطوير الباك ايند',
            'description' => 'فريق مسؤول عن مهام تطوير النظام الخلفي',
            'supervisor_id' => $supervisor->id,
            'is_active' => true,
        ]);

        foreach ($volunteers as $volunteer) {
            $team->members()->attach($volunteer->id);
        }
    }
}

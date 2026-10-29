<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorUserSeeder extends Seeder
{
    public function run(): void
    {
        // Đảm bảo có role doctor
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);

        // Tạo tài khoản doctor mặc định
        User::firstOrCreate(
            ['email' => 'doctor@example.com'],
            [
                'name' => 'Dr. Strange',
                'password' => Hash::make('doctor123'),
                'role_id' => $doctorRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}

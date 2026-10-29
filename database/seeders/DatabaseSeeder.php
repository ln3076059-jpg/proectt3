<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạo các roles trước
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'doctor']);
        Role::firstOrCreate(['name' => 'patient']);
        Role::firstOrCreate(['name' => 'leader']);

        // Chạy Seeder mẫu chuẩn cho Đồ án tốt nghiệp Phòng khám Thái Thịnh
        $this->call([
            RoleSeeder::class,
            ThaiThinhClinicDemoSeeder::class,
        ]);
    }
}

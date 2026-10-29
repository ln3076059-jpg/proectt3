<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'patient',
                'description' => 'Bệnh nhân',
            ],
            [
                'name' => 'doctor',
                'description' => 'Bác sĩ',
            ],
            [
                'name' => 'admin',
                'description' => 'Quản trị viên',
            ],
            [
                'name' => 'leader',
                'description' => 'Lãnh đạo',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}

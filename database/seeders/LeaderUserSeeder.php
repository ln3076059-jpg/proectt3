<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LeaderUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // tìm role leader
        $leaderRole = Role::where('name', 'leader')->first();

        // nếu chưa có thì tạo
        if (! $leaderRole) {
            $leaderRole = Role::create(['name' => 'leader']);
        }

        // tạo tài khoản leader
        User::create([
            'name' => 'Leader',
            'email' => 'leader@example.com',
            'password' => Hash::make('leader123'), // mật khẩu đăng nhập
            'role_id' => $leaderRole->id,
        ]);
    }
}

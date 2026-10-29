<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('services')->insert([
            ['name' => 'Khám tổng quát', 'description' => 'Khám lâm sàng', 'price' => 200000, 'department_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Xét nghiệm máu', 'description' => 'Xét nghiệm', 'price' => 150000, 'department_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

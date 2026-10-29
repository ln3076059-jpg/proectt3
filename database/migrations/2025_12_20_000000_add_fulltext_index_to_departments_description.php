<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kiểm tra xem FULLTEXT index đã tồn tại chưa
        $indexExists = DB::select(
            "SHOW INDEX FROM departments WHERE Key_name = 'fulltext_description'"
        );

        // Chỉ tạo index nếu chưa tồn tại
        if (empty($indexExists)) {
            // Thêm FULLTEXT index cho cột description trong bảng departments
            // Chỉ hoạt động với InnoDB (MySQL 5.6+) hoặc MyISAM
            DB::statement('ALTER TABLE departments ADD FULLTEXT INDEX fulltext_description (description)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa FULLTEXT index
        DB::statement('ALTER TABLE departments DROP INDEX fulltext_description');
    }
};

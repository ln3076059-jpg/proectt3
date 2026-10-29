<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lab_tests', function (Blueprint $table) {
            // 🔗 Thêm cột liên kết với bảng test_types
            if (! Schema::hasColumn('lab_tests', 'test_type_id')) {
                $table->unsignedBigInteger('test_type_id')->nullable()->after('department_id');
                $table->foreign('test_type_id')
                    ->references('id')
                    ->on('test_types')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_tests', function (Blueprint $table) {
            // 🧹 Xóa khóa ngoại và cột nếu rollback
            if (Schema::hasColumn('lab_tests', 'test_type_id')) {
                $table->dropForeign(['test_type_id']);
                $table->dropColumn('test_type_id');
            }
        });
    }
};

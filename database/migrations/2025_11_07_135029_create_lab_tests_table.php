<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medical_record_id'); // Liên kết với hồ sơ khám bệnh
            $table->unsignedBigInteger('department_id'); // Khoa phụ trách xét nghiệm
            $table->string('test_name'); // Tên xét nghiệm
            $table->string('image')->nullable(); // Ảnh chính
            $table->json('images')->nullable(); // Ảnh phụ
            $table->text('note')->nullable(); // Ghi chú
            $table->unsignedBigInteger('requested_by'); // Bác sĩ yêu cầu
            $table->unsignedBigInteger('uploaded_by')->nullable(); // Admin upload kết quả
            $table->enum('status', ['requested', 'completed'])->default('requested');
            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('medical_record_id')->references('id')->on('medical_records')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};

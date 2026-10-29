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
        if (!Schema::hasTable('medical_records')) {
            Schema::create('medical_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('appointment_id');
                $table->unsignedBigInteger('patient_id');

                // Nội dung hồ sơ
                $table->text('description')->nullable(); // mô tả bệnh / triệu chứng
                $table->text('diagnosis')->nullable(); // chẩn đoán
                $table->text('doctor_conclusion')->nullable(); // kết luận bác sĩ
                $table->json('prescription')->nullable(); // toa thuốc (mảng JSON)

                // Ảnh liên quan (kết quả xét nghiệm, chụp chiếu, ...)
                $table->string('image')->nullable(); // ảnh chính (tùy chọn)
                $table->json('images')->nullable(); // nhiều ảnh (mảng đường dẫn JSON)

                $table->timestamps();

                $table->foreign('appointment_id')
                    ->references('id')->on('appointments')
                    ->onDelete('cascade');

                $table->foreign('patient_id')
                    ->references('id')->on('patients')
                    ->onDelete('cascade');
            });
        } else {
            Schema::table('medical_records', function (Blueprint $table) {
                if (!Schema::hasColumn('medical_records', 'patient_id')) {
                    $table->unsignedBigInteger('patient_id')->nullable()->after('appointment_id');
                    $table->foreign('patient_id')
                        ->references('id')->on('patients')
                        ->onDelete('cascade');
                }
                if (!Schema::hasColumn('medical_records', 'description')) {
                    $table->text('description')->nullable()->after('patient_id');
                }
                if (!Schema::hasColumn('medical_records', 'doctor_conclusion')) {
                    $table->text('doctor_conclusion')->nullable()->after('diagnosis');
                }
                if (!Schema::hasColumn('medical_records', 'image')) {
                    $table->string('image')->nullable();
                }
                if (!Schema::hasColumn('medical_records', 'images')) {
                    $table->json('images')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down
    }
};

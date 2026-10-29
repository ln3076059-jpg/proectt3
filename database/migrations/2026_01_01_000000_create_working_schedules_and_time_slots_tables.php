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
        // 1. Bảng lịch làm việc của bác sĩ (Working Schedules)
        if (!Schema::hasTable('working_schedules')) {
            Schema::create('working_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
                $table->date('date');
                $table->tinyInteger('day_of_week')->nullable()->comment('0: CN, 1: T2, ..., 6: T7');
                $table->time('start_time')->default('07:30:00');
                $table->time('end_time')->default('17:00:00');
                $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
                $table->string('note', 500)->nullable();
                $table->timestamps();

                $table->unique(['doctor_id', 'date'], 'doc_date_unique');
                $table->index(['date', 'status']);
            });
        }

        // 2. Bảng khung giờ khám cụ thể (Time Slots)
        if (!Schema::hasTable('time_slots')) {
            Schema::create('time_slots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('working_schedule_id')->nullable()->constrained('working_schedules')->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
                $table->date('date');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedInteger('capacity')->default(1)->comment('Số lượng bệnh nhân tối đa');
                $table->unsignedInteger('booked_count')->default(0)->comment('Số lượng đã đặt');
                $table->enum('status', ['available', 'locked', 'cancelled'])->default('available');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['doctor_id', 'date', 'start_time'], 'doc_date_slot_unique');
                $table->index(['doctor_id', 'date', 'status'], 'doc_date_status_idx');
                $table->index(['date', 'status'], 'date_status_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('working_schedules');
    }
};

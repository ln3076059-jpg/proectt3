<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Backfill appointments.total & appointments.total_amount từ services.price nếu đang NULL
        if (Schema::hasTable('appointments') && Schema::hasTable('services')) {
            if (Schema::hasColumn('appointments', 'total') && Schema::hasColumn('appointments', 'total_amount')) {
                DB::statement("
                    UPDATE appointments a
                    INNER JOIN services s ON a.service_id = s.id
                    SET a.total = COALESCE(a.total, a.total_amount, s.price),
                        a.total_amount = COALESCE(a.total_amount, a.total, s.price)
                    WHERE a.total IS NULL OR a.total_amount IS NULL
                ");
            }
        }

        // 2. Backfill medical_records.patient_id từ appointments.patient_id nếu đang NULL
        if (Schema::hasTable('medical_records') && Schema::hasTable('appointments')) {
            if (Schema::hasColumn('medical_records', 'patient_id') && Schema::hasColumn('medical_records', 'appointment_id')) {
                DB::statement("
                    UPDATE medical_records mr
                    INNER JOIN appointments a ON mr.appointment_id = a.id
                    SET mr.patient_id = a.patient_id
                    WHERE mr.patient_id IS NULL AND a.patient_id IS NOT NULL
                ");
            }
        }

        // 3. Chuẩn hóa patients.gender ('Nam' -> 'male', 'Nữ' -> 'female', 'Khác' -> 'other') đồng bộ với users.gender
        if (Schema::hasTable('patients') && Schema::hasColumn('patients', 'gender')) {
            DB::table('patients')->whereIn('gender', ['Nam', 'nam'])->update(['gender' => 'male']);
            DB::table('patients')->whereIn('gender', ['Nữ', 'nữ'])->update(['gender' => 'female']);
            DB::table('patients')->whereIn('gender', ['Khác', 'khác'])->update(['gender' => 'other']);
        }

        // 4. Cho phép Bác sĩ có nhiều ca trực không trùng giờ trong cùng 1 ngày (Ca sáng + Ca chiều) trên bảng working_schedules
        if (Schema::hasTable('working_schedules')) {
            $indexes = collect(DB::select("SHOW INDEX FROM working_schedules"))->pluck('Key_name')->unique()->all();

            if (!in_array('working_schedules_doctor_id_idx', $indexes, true)) {
                Schema::table('working_schedules', function (Blueprint $table) {
                    $table->index('doctor_id', 'working_schedules_doctor_id_idx');
                });
            }

            if (in_array('doc_date_unique', $indexes, true)) {
                Schema::table('working_schedules', function (Blueprint $table) {
                    $table->dropUnique('doc_date_unique');
                    $table->index(['doctor_id', 'date'], 'working_schedules_doc_date_idx');
                });
            }
        }

        // 5. Bổ sung index cho prescription_items (prescription_id, medicine_id) để tối ưu truy vấn đơn thuốc
        if (Schema::hasTable('prescription_items')) {
            $pIndexes = collect(DB::select("SHOW INDEX FROM prescription_items"))->pluck('Key_name')->unique()->all();

            Schema::table('prescription_items', function (Blueprint $table) use ($pIndexes) {
                if (!in_array('prescription_items_prescription_id_idx', $pIndexes, true)) {
                    $table->index('prescription_id', 'prescription_items_prescription_id_idx');
                }
                if (!in_array('prescription_items_medicine_id_idx', $pIndexes, true)) {
                    $table->index('medicine_id', 'prescription_items_medicine_id_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('prescription_items')) {
            $pIndexes = collect(DB::select("SHOW INDEX FROM prescription_items"))->pluck('Key_name')->unique()->all();
            Schema::table('prescription_items', function (Blueprint $table) use ($pIndexes) {
                if (in_array('prescription_items_prescription_id_idx', $pIndexes, true)) {
                    $table->dropIndex('prescription_items_prescription_id_idx');
                }
                if (in_array('prescription_items_medicine_id_idx', $pIndexes, true)) {
                    $table->dropIndex('prescription_items_medicine_id_idx');
                }
            });
        }
    }
};

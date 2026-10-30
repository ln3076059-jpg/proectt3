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
        $db = env('DB_DATABASE', 'phongkham_thaithinh');

        // 1. Sửa giá trị mặc định của users.role_id từ 1 (admin) sang 3 (patient)
        DB::statement("ALTER TABLE users ALTER COLUMN role_id SET DEFAULT 3");

        // 2. Mở rộng enum payment_status trên bảng appointments để hỗ trợ đầy đủ: pending, success, failed, refunded
        DB::statement("ALTER TABLE appointments MODIFY COLUMN payment_status ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending'");

        // 3. Ràng buộc quan hệ 1-1 giữa appointment và medical_record (Unique appointment_id)
        $medRecIndexes = collect(DB::select("SHOW INDEX FROM medical_records"))->pluck('Key_name')->unique()->all();
        if (!in_array('uq_medical_records_appointment_id', $medRecIndexes)) {
            DB::statement("CREATE UNIQUE INDEX uq_medical_records_appointment_id ON medical_records (appointment_id)");
        }

        // 4. Bổ sung các chỉ mục hiệu năng quan trọng trên bảng appointments
        $apptIndexes = collect(DB::select("SHOW INDEX FROM appointments"))->pluck('Key_name')->unique()->all();
        if (!in_array('idx_appt_doc_date_status', $apptIndexes)) {
            DB::statement("CREATE INDEX idx_appt_doc_date_status ON appointments (doctor_id, appointment_date, status)");
        }
        if (!in_array('idx_appt_pat_status', $apptIndexes)) {
            DB::statement("CREATE INDEX idx_appt_pat_status ON appointments (patient_id, status)");
        }
        if (!in_array('idx_appt_trans_ref', $apptIndexes)) {
            DB::statement("CREATE INDEX idx_appt_trans_ref ON appointments (transaction_ref)");
        }
        if (!in_array('idx_appt_pay_status', $apptIndexes)) {
            DB::statement("CREATE INDEX idx_appt_pay_status ON appointments (payment_status)");
        }

        // 5. Bổ sung chỉ mục truy vấn tin nhắn thời gian thực trên bảng messages
        $msgIndexes = collect(DB::select("SHOW INDEX FROM messages"))->pluck('Key_name')->unique()->all();
        if (!in_array('idx_msg_receiver_read', $msgIndexes)) {
            DB::statement("CREATE INDEX idx_msg_receiver_read ON messages (receiver_id, is_read)");
        }
        if (!in_array('idx_msg_convo', $msgIndexes)) {
            DB::statement("CREATE INDEX idx_msg_convo ON messages (sender_id, receiver_id, created_at)");
        }

        // 6. Bổ sung chỉ mục và khóa ngoại toàn vẹn dữ liệu cho bảng prescription_items
        $piIndexes = collect(DB::select("SHOW INDEX FROM prescription_items"))->pluck('Key_name')->unique()->all();
        if (!in_array('idx_prescription_items_presc', $piIndexes)) {
            DB::statement("CREATE INDEX idx_prescription_items_presc ON prescription_items (prescription_id)");
        }
        if (!in_array('idx_prescription_items_med', $piIndexes)) {
            DB::statement("CREATE INDEX idx_prescription_items_med ON prescription_items (medicine_id)");
        }

        $fks = collect(DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'prescription_items' AND REFERENCED_TABLE_NAME IS NOT NULL
        "))->pluck('CONSTRAINT_NAME')->all();

        if (!in_array('fk_prescription_items_medical_record', $fks)) {
            DB::statement("ALTER TABLE prescription_items ADD CONSTRAINT fk_prescription_items_medical_record FOREIGN KEY (prescription_id) REFERENCES medical_records(id) ON DELETE CASCADE");
        }
        if (!in_array('fk_prescription_items_medicine', $fks)) {
            DB::statement("ALTER TABLE prescription_items ADD CONSTRAINT fk_prescription_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $db = env('DB_DATABASE', 'phongkham_thaithinh');

        // Gỡ khóa ngoại
        $fks = collect(DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'prescription_items' AND REFERENCED_TABLE_NAME IS NOT NULL
        "))->pluck('CONSTRAINT_NAME')->all();

        if (in_array('fk_prescription_items_medical_record', $fks)) {
            DB::statement("ALTER TABLE prescription_items DROP FOREIGN KEY fk_prescription_items_medical_record");
        }
        if (in_array('fk_prescription_items_medicine', $fks)) {
            DB::statement("ALTER TABLE prescription_items DROP FOREIGN KEY fk_prescription_items_medicine");
        }

        // Gỡ chỉ mục
        DB::statement("ALTER TABLE medical_records DROP INDEX IF EXISTS uq_medical_records_appointment_id");
        DB::statement("ALTER TABLE appointments DROP INDEX IF EXISTS idx_appt_doc_date_status");
        DB::statement("ALTER TABLE appointments DROP INDEX IF EXISTS idx_appt_pat_status");
        DB::statement("ALTER TABLE appointments DROP INDEX IF EXISTS idx_appt_trans_ref");
        DB::statement("ALTER TABLE appointments DROP INDEX IF EXISTS idx_appt_pay_status");
        DB::statement("ALTER TABLE messages DROP INDEX IF EXISTS idx_msg_receiver_read");
        DB::statement("ALTER TABLE messages DROP INDEX IF EXISTS idx_msg_convo");
        DB::statement("ALTER TABLE prescription_items DROP INDEX IF EXISTS idx_prescription_items_presc");
        DB::statement("ALTER TABLE prescription_items DROP INDEX IF EXISTS idx_prescription_items_med");

        // Hoàn tác enum và role_id default
        DB::statement("ALTER TABLE appointments MODIFY COLUMN payment_status ENUM('success', 'failed') NOT NULL DEFAULT 'failed'");
        DB::statement("ALTER TABLE users ALTER COLUMN role_id SET DEFAULT 1");
    }
};

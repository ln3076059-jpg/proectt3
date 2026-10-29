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
        // 1. Bổ sung các trường chuyên sâu vào bảng appointments
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'time_slot_id')) {
                $table->foreignId('time_slot_id')->nullable()->after('service_id')->constrained('time_slots')->nullOnDelete();
            }
            if (!Schema::hasColumn('appointments', 'appointment_code')) {
                $table->string('appointment_code', 32)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('appointments', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->index()->after('appointment_code');
            }
            if (!Schema::hasColumn('appointments', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('note');
            }
            if (!Schema::hasColumn('appointments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('appointments', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancelled_at');
            }
            if (!Schema::hasColumn('appointments', 'refund_status')) {
                $table->enum('refund_status', ['none', 'pending', 'refunded', 'rejected'])->default('none')->after('payment_status');
            }
            if (!Schema::hasColumn('appointments', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->nullable()->after('refund_status');
            }
            if (!Schema::hasColumn('appointments', 'refund_note')) {
                $table->text('refund_note')->nullable()->after('refund_amount');
            }
        });

        // 2. Tạo bảng lịch sử trạng thái lịch hẹn (Audit trail)
        if (!Schema::hasTable('appointment_status_histories')) {
            Schema::create('appointment_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
                $table->string('from_status', 32)->nullable();
                $table->string('to_status', 32);
                $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action_role', 32)->nullable()->comment('admin, doctor, patient, system');
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['appointment_id', 'created_at'], 'appt_hist_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_status_histories');

        Schema::table('appointments', function (Blueprint $table) {
            $cols = [
                'time_slot_id',
                'appointment_code',
                'idempotency_key',
                'cancellation_reason',
                'cancelled_at',
                'cancelled_by',
                'refund_status',
                'refund_amount',
                'refund_note',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

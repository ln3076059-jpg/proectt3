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
        if (Schema::hasTable('doctors')) {
            Schema::table('doctors', function (Blueprint $table) {
                if (!Schema::hasColumn('doctors', 'room_number')) {
                    $table->string('room_number', 100)->nullable()->after('license_number');
                }
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (!Schema::hasColumn('appointments', 'payment_reference')) {
                    $table->string('payment_reference')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('appointments', 'transaction_ref')) {
                    $table->string('transaction_ref')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('appointments', 'transaction_id')) {
                    $table->string('transaction_id')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('appointments', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('appointments', 'total_amount')) {
                    $table->decimal('total_amount', 12, 2)->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('appointments', 'total')) {
                    $table->decimal('total', 12, 2)->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('appointments', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('payment_status');
                }
            });
        }

        if (Schema::hasTable('lab_tests')) {
            try {
                DB::statement("ALTER TABLE `lab_tests` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'requested'");
            } catch (\Throwable $e) {
                // Fallback if SQLite or non-MySQL driver
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'room_number')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('room_number');
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                $cols = ['payment_reference', 'transaction_ref', 'payment_method', 'total_amount', 'total'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('appointments', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

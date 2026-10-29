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
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'idempotency_key')) {
            // Drop existing non-unique index if present
            try {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->dropIndex(['idempotency_key']);
                });
            } catch (\Throwable $e) {
                // Ignore if index name differs or not present
            }

            // Ensure unique index
            Schema::table('appointments', function (Blueprint $table) {
                $table->unique('idempotency_key', 'appointments_idempotency_key_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'idempotency_key')) {
            try {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->dropUnique('appointments_idempotency_key_unique');
                });
            } catch (\Throwable $e) {
                // Ignore
            }

            Schema::table('appointments', function (Blueprint $table) {
                $table->index('idempotency_key');
            });
        }
    }
};

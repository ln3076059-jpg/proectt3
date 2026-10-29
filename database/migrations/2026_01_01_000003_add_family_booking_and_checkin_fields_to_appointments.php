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
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'booking_for')) {
                $table->string('booking_for', 20)->default('self')->after('patient_id')->comment('self, relative');
            }
            if (!Schema::hasColumn('appointments', 'relative_name')) {
                $table->string('relative_name', 255)->nullable()->after('booking_for');
            }
            if (!Schema::hasColumn('appointments', 'relative_dob')) {
                $table->date('relative_dob')->nullable()->after('relative_name');
            }
            if (!Schema::hasColumn('appointments', 'relative_gender')) {
                $table->string('relative_gender', 10)->nullable()->after('relative_dob');
            }
            if (!Schema::hasColumn('appointments', 'relative_relationship')) {
                $table->string('relative_relationship', 50)->nullable()->after('relative_gender')->comment('Con nhỏ, Bố/Mẹ, Vợ/Chồng, Khác');
            }
            if (!Schema::hasColumn('appointments', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $cols = [
                'booking_for',
                'relative_name',
                'relative_dob',
                'relative_gender',
                'relative_relationship',
                'checked_in_at',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

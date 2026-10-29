<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumns('users', ['phone', 'insurance_number', 'birthdate', 'gender', 'address'])) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable();
                $table->string('insurance_number')->nullable();
                $table->date('birthdate')->nullable();
                $table->string('gender')->nullable();
                $table->text('address')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'insurance_number',
                'birthdate',
                'gender',
                'address',
            ]);
        });
    }
};

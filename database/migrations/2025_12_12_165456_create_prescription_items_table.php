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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();

            // Liên kết đến đơn thuốc (prescriptions)
            $table->unsignedBigInteger('prescription_id');

            // Liên kết đến thuốc trong kho (medicines)
            $table->unsignedBigInteger('medicine_id');

            // Thông tin kê đơn chi tiết
            $table->string('dosage')->nullable();         // liều dùng, ví dụ: 1 viên
            $table->string('frequency')->nullable();      // số lần trong ngày, ví dụ: 2 lần/ngày
            $table->string('duration')->nullable();       // thời gian dùng, ví dụ: 5 ngày
            $table->integer('quantity')->default(0);      // tổng số lượng cấp
            $table->string('unit')->nullable();           // đơn vị (viên, ống, gói...)
            $table->text('usage')->nullable();            // hướng dẫn sử dụng chi tiết
            $table->text('note')->nullable();             // ghi chú thêm

            $table->timestamps();

            // Khóa ngoại (có thể bật nếu đã có bảng tương ứng)
            // $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade');
            // $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};

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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản
            $table->string('name');                     // Tên thuốc
            $table->string('generic_name')->nullable(); // Hoạt chất (Paracetamol)
            $table->string('brand_name')->nullable();   // Tên biệt dược (Panadol)
            $table->string('slug')->unique();

            // Phân loại
            $table->string('category')->nullable();     // Giảm đau, kháng sinh, tim mạch...
            $table->string('dosage_form')->nullable();  // dạng thuốc: viên, siro, tiêm...
            $table->string('strength')->nullable();     // hàm lượng: 500mg, 250mg/5ml
            $table->string('unit')->nullable();         // đơn vị tính: viên, chai, hộp

            // Giá + tồn kho
            $table->decimal('price', 12, 2)->nullable();
            $table->integer('stock')->default(0);       // số lượng tồn kho
            $table->integer('min_stock')->default(10);  // cảnh báo hết hàng

            // Chỉ định & thông tin lâm sàng
            $table->text('indications')->nullable();       // Chỉ định
            $table->text('contraindications')->nullable(); // Chống chỉ định
            $table->text('side_effects')->nullable();      // Tác dụng phụ
            $table->text('interactions')->nullable();      // Tương tác thuốc

            // Cách dùng
            $table->text('dosage')->nullable();    // liều lượng
            $table->text('usage')->nullable();     // hướng dẫn sử dụng
            $table->text('note')->nullable();      // ghi chú thêm

            // Quản lý
            $table->string('manufacturer')->nullable();  // nhà sản xuất
            $table->string('origin')->nullable();        // xuất xứ
            $table->string('barcode')->nullable();       // mã vạch
            $table->boolean('is_prescription')->default(false); // thuốc kê đơn?

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};

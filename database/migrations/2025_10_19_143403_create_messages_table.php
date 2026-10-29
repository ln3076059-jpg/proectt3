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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');   // Người gửi
            $table->unsignedBigInteger('receiver_id'); // Người nhận
            $table->text('message');                  // Nội dung tin nhắn
            $table->boolean('is_read')->default(false); // Đã đọc chưa
            $table->timestamps();

            // Khóa ngoại (quan hệ với bảng users)
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

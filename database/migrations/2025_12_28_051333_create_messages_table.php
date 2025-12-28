<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('MessageID'); // Primary Key
            $table->unsignedBigInteger('SenderID');   // Người gửi
            $table->unsignedBigInteger('ReceiverID'); // Người nhận
            $table->text('Content');                  // Nội dung
            $table->dateTime('SendTime');             // Thời gian gửi
            $table->boolean('IsRead')->default(false); // Đã xem chưa
            $table->timestamps(); // Created_at, Updated_at

            // Khóa ngoại (Liên kết với bảng users)
            $table->foreign('SenderID')->references('UserID')->on('users')->onDelete('cascade');
            $table->foreign('ReceiverID')->references('UserID')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

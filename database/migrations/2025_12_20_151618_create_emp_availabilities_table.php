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
    Schema::create('emp_availabilities', function (Blueprint $table) {
        $table->id('EaID');
        $table->foreignId('UserID')->constrained('users', 'UserID')->onDelete('cascade');
        $table->foreignId('WeekID')->constrained('weeks', 'WeekID')->onDelete('cascade');
        $table->string('DayOfWeek');
        $table->time('AvailableFrom');
        $table->time('AvailableTo');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_availabilities');
    }
};

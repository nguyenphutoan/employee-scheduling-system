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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id('ShiftID');
            $table->foreignId('WeekID')->constrained('weeks', 'WeekID')->onDelete('cascade');
            $table->string('DayOfWeek'); // Mon, Tue...
            $table->string('ShiftType'); // MOR, EVE
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};

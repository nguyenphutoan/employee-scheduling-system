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
        Schema::create('work_assignments', function (Blueprint $table) {
            $table->id('WaID');
            $table->foreignId('ShiftID')->constrained('shifts', 'ShiftID')->onDelete('cascade');
            $table->foreignId('UserID')->constrained('users', 'UserID')->onDelete('cascade');
            $table->foreignId('PositionID')->constrained('positions', 'PositionID');
            $table->time('StartTime');
            $table->time('EndTime');
            $table->string('Status')->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_assignments');
    }
};

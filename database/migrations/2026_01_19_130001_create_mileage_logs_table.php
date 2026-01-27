<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mileage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Staff who logged
            $table->integer('mileage_reading'); // Mileage at time of logging
            $table->dateTime('recorded_at'); // When this was recorded
            $table->text('notes')->nullable(); // Optional notes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mileage_logs');
    }
};

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
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Links to User
        $table->string('site_name')->nullable(); // e.g. "HQ", "Site A"
        $table->decimal('latitude', 10, 8); // GPS Lat
        $table->decimal('longitude', 11, 8); // GPS Long
        $table->string('status')->default('pending'); // verified, pending
        $table->timestamp('clock_in_time'); // The exact time
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

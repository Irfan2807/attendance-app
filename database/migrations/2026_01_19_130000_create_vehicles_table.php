<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('numberplate')->unique(); // Vehicle ID
            $table->string('name'); // Vehicle name/model
            $table->integer('current_mileage')->default(0); // Current odometer reading in KM
            $table->integer('next_service_mileage')->default(0); // Next service due at this KM
            $table->boolean('is_active')->default(true); // Active/inactive vehicle
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

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
        Schema::table('mileage_logs', function (Blueprint $table) {
            $table->integer('start_mileage')->nullable()->after('user_id');
            $table->integer('end_mileage')->nullable()->after('start_mileage');
            $table->integer('distance_km')->nullable()->after('end_mileage');
            $table->string('destination')->nullable()->after('distance_km');
            $table->string('purpose')->nullable()->after('destination');
            $table->foreignId('site_id')->nullable()->after('purpose')->constrained('sites')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mileage_logs', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn([
                'start_mileage',
                'end_mileage',
                'distance_km',
                'destination',
                'purpose',
                'site_id',
            ]);
        });
    }
};

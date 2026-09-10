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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('annual_leave_quota', 4, 1)->default(14.0)->after('incomplete_clock_out_count');
            $table->decimal('medical_leave_quota', 4, 1)->default(14.0)->after('annual_leave_quota');
            $table->decimal('hospitalization_quota', 4, 1)->default(60.0)->after('medical_leave_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'annual_leave_quota',
                'medical_leave_quota',
                'hospitalization_quota',
            ]);
        });
    }
};

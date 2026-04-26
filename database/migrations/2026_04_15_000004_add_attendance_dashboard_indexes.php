<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'clock_out_time', 'clock_in_time'], 'att_user_out_in_idx');
            $table->index(['status', 'clock_in_time'], 'att_status_in_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('att_user_out_in_idx');
            $table->dropIndex('att_status_in_idx');
        });
    }
};
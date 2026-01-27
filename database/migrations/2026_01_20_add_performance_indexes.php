<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add performance indexes
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Speed up common queries
            $table->index('user_id');
            $table->index('status');
            $table->index(['clock_in_time', 'user_id']);
            $table->index('approved_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('phone');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->index('ip_address');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['clock_in_time', 'user_id']);
            $table->dropIndex(['approved_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['phone']);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['is_active']);
        });
    }
};

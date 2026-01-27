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
            // Rename email column to phone
            $table->renameColumn('email', 'phone');
            
            // Drop email_verified_at since we're using phone now
            $table->dropColumn('email_verified_at');
            
            // Add phone_verified_at if needed for future SMS verification
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
        });

        // Also update password_reset_tokens table
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->renameColumn('email', 'phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename phone back to email
            $table->renameColumn('phone', 'email');
            
            // Drop phone_verified_at
            $table->dropColumn('phone_verified_at');
            
            // Restore email_verified_at
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // Revert password_reset_tokens table
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->renameColumn('phone', 'email');
        });
    }
};

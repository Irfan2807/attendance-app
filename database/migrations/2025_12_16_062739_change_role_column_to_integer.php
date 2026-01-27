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
            // Change 'role' from string to integer
            // Default to 3 (Staff)
            // 1=Admin, 2=Manager, 3=Staff
            $table->integer('role')->default(3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to string if we rollback
            $table->string('role')->default('employee')->change();
        });
    }
};

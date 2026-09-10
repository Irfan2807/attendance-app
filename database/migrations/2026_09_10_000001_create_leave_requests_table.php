<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('leave_type', 32);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 4, 1)->default(1.0);
            $table->text('reason')->nullable();
            $table->string('attachment_path', 512)->nullable();
            $table->string('status', 32)->default('pending');
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};


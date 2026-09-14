<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->string('day_name')->nullable();
            $table->json('state_codes')->nullable();
            $table->boolean('is_nationwide')->default(false);
            $table->boolean('is_subject_to_change')->default(false);
            $table->unsignedSmallInteger('year')->index();
            $table->timestamps();

            $table->unique(['date', 'name']);
            $table->index(['year', 'date']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('public_holidays');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('road_tax_expiry')->nullable()->after('notes')->index();
            $table->decimal('road_tax_amount', 10, 2)->nullable()->after('road_tax_expiry');
            $table->string('road_tax_document')->nullable()->after('road_tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['road_tax_expiry']);
            $table->dropColumn(['road_tax_expiry', 'road_tax_amount', 'road_tax_document']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a sale_type column to sale_items so each line item records
     * whether it was sold at the retail or wholesale price.
     * Existing rows default to 'retail' for backward compatibility.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('sale_type', 20)->default('retail')->after('total_price')
                  ->comment('retail or wholesale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('sale_type');
        });
    }
};

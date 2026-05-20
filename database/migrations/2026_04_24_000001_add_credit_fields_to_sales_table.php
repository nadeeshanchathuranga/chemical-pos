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
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('status', ['Open', 'Closed'])->default('Closed')->after('payment_method');
            $table->boolean('is_credit')->default(false)->after('status');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_cost');
            $table->date('closing_date')->nullable()->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_credit', 'paid_amount', 'closing_date']);
        });
    }
};

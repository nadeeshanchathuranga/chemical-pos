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
            if (! Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['Open', 'Closed'])->default('Closed')->after('payment_method');
            }
            if (! Schema::hasColumn('sales', 'is_credit')) {
                $table->boolean('is_credit')->default(false)->after('status');
            }
            if (! Schema::hasColumn('sales', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('total_cost');
            }
            if (! Schema::hasColumn('sales', 'closing_date')) {
                $table->date('closing_date')->nullable()->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['status', 'is_credit', 'paid_amount', 'closing_date'] as $column) {
            if (Schema::hasColumn('sales', $column)) {
                Schema::table('sales', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};

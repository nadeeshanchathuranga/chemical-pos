<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'balance_due')) {
                $table->decimal('balance_due', 15, 2)->default(0)->after('paid_amount');
            }
        });

        DB::statement('UPDATE sales SET balance_due = GREATEST(total_amount - paid_amount, 0)');
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'balance_due')) {
                $table->dropColumn('balance_due');
            }
        });
    }
};

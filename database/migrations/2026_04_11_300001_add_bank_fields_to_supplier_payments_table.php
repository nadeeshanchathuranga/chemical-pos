<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreignId('bank_account_id')
                  ->nullable()
                  ->after('note')
                  ->constrained('bank_accounts')
                  ->nullOnDelete();
            $table->foreignId('bank_transaction_id')
                  ->nullable()
                  ->after('bank_account_id')
                  ->constrained('bank_transactions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropConstrainedForeignId('bank_transaction_id');
        });
    }
};

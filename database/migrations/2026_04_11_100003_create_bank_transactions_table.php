<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->enum('type', ['Credit', 'Debit']);         // Credit = money in, Debit = money out
            $table->decimal('amount', 15, 2);
            $table->string('reference_type')->nullable();      // "Sale", "Expense", "Manual", "SupplierPayment", "Transfer"
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Received', 'Issued']);       // Received = from customer, Issued = to supplier
            $table->string('cheque_number');
            $table->string('bank_name');                         // Bank the cheque is drawn on
            $table->string('branch')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('cheque_date');                         // Date written on cheque
            $table->date('due_date')->nullable();                 // Deposit/clearing date (PDC)
            $table->string('payee_payer');                       // Customer (Received) / Supplier (Issued)
            $table->enum('status', ['Pending', 'Cleared', 'Bounced', 'Cancelled'])->default('Pending');
            // Linked bank account (set when cleared)
            $table->foreignId('bank_account_id')
                  ->nullable()
                  ->constrained('bank_accounts')
                  ->nullOnDelete();
            // The bank transaction created when cleared
            $table->foreignId('bank_transaction_id')
                  ->nullable()
                  ->constrained('bank_transactions')
                  ->nullOnDelete();
            // Optional link to supplier (for Issued cheques)
            $table->foreignId('supplier_id')
                  ->nullable()
                  ->constrained('suppliers')
                  ->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};

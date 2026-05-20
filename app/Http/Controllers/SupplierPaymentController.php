<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Cheque;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SupplierPaymentController extends Controller
{
    public function store(Request $request, Supplier $supplier)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date',
            'payment_method'   => 'required|in:Cash,Bank Transfer,Online,Cheque',
            'note'             => 'nullable|string|max:500',
            // Bank Transfer / Online
            'bank_account_id'  => 'nullable|exists:bank_accounts,id',
            // Cheque-specific
            'cheque_number'    => 'required_if:payment_method,Cheque|nullable|string|max:100',
            'cheque_bank_name' => 'required_if:payment_method,Cheque|nullable|string|max:191',
            'cheque_branch'    => 'nullable|string|max:191',
            'cheque_date'      => 'required_if:payment_method,Cheque|nullable|date',
            'due_date'         => 'nullable|date',
        ]);

        DB::transaction(function () use ($supplier, $validated) {
            $bankTransactionId = null;

            // Auto-debit bank for Bank Transfer / Online
            if (in_array($validated['payment_method'], ['Bank Transfer', 'Online'])
                && !empty($validated['bank_account_id'])) {
                $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);
                $bankTx = BankTransaction::create([
                    'bank_account_id'  => $bankAccount->id,
                    'type'             => 'Debit',
                    'amount'           => $validated['amount'],
                    'description'      => "Supplier payment — {$supplier->name} ({$validated['payment_method']})",
                    'reference_type'   => 'SupplierPayment',
                    'transaction_date' => $validated['payment_date'],
                ]);
                $bankAccount->recalculateBalance();
                $bankTransactionId = $bankTx->id;
            }

            // Save the supplier payment record
            $supplier->supplierPayments()->create([
                'amount'              => $validated['amount'],
                'payment_date'        => $validated['payment_date'],
                'payment_method'      => $validated['payment_method'],
                'note'                => $validated['note'] ?? null,
                'bank_account_id'     => $validated['bank_account_id'] ?? null,
                'bank_transaction_id' => $bankTransactionId,
            ]);

            // Cheque → create Issued cheque record (Pending; bank debited only when cheque clears)
            if ($validated['payment_method'] === 'Cheque') {
                Cheque::create([
                    'type'          => 'Issued',
                    'cheque_number' => $validated['cheque_number'],
                    'bank_name'     => $validated['cheque_bank_name'],
                    'branch'        => $validated['cheque_branch'] ?? null,
                    'amount'        => $validated['amount'],
                    'cheque_date'   => $validated['cheque_date'],
                    'due_date'      => $validated['due_date'] ?? null,
                    'payee_payer'   => $supplier->name,
                    'status'        => 'Pending',
                    'supplier_id'   => $supplier->id,
                    'note'          => $validated['note'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('suppliers.show', $supplier->id)
            ->banner('Payment recorded successfully.');
    }

    public function destroy(SupplierPayment $supplierPayment)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $supplierId = $supplierPayment->supplier_id;

        DB::transaction(function () use ($supplierPayment) {
            // Reverse the bank transaction if one was auto-created
            if ($supplierPayment->bank_transaction_id) {
                $bankTx = BankTransaction::find($supplierPayment->bank_transaction_id);
                if ($bankTx) {
                    $bankAccount = $bankTx->bankAccount;
                    $bankTx->delete();
                    if ($bankAccount) {
                        $bankAccount->recalculateBalance();
                    }
                }
            }
            $supplierPayment->delete();
        });

        return redirect()
            ->route('suppliers.show', $supplierId)
            ->banner('Payment deleted and bank entry reversed.');
    }
}

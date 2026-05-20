<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Cheque;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $query = Cheque::with(['bankAccount', 'supplier'])->orderBy('due_date', 'asc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('cheque_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('cheque_date', '<=', $request->date_to);
        }

        $cheques = $query->get();

        // KPI summary
        $kpis = [
            'pending_received'  => $cheques->where('type', 'Received')->where('status', 'Pending')->sum('amount'),
            'pending_issued'    => $cheques->where('type', 'Issued')->where('status', 'Pending')->sum('amount'),
            'cleared_received'  => $cheques->where('type', 'Received')->where('status', 'Cleared')->sum('amount'),
            'cleared_issued'    => $cheques->where('type', 'Issued')->where('status', 'Cleared')->sum('amount'),
            'bounced'           => $cheques->where('status', 'Bounced')->sum('amount'),
            'count_pending'     => $cheques->where('status', 'Pending')->count(),
            'count_overdue'     => $cheques->where('status', 'Pending')
                                           ->filter(fn($c) => $c->due_date && $c->due_date->isPast())
                                           ->count(),
        ];

        return Inertia::render('Cheques/Index', [
            'cheques'      => $cheques,
            'kpis'         => $kpis,
            'bankAccounts' => BankAccount::orderBy('name')->get(['id', 'name', 'bank_name', 'current_balance']),
            'suppliers'    => Supplier::orderBy('name')->get(['id', 'name']),
            'filters'      => $request->only(['type', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'type'          => 'required|in:Received,Issued',
            'cheque_number' => 'required|string|max:100',
            'bank_name'     => 'required|string|max:191',
            'branch'        => 'nullable|string|max:191',
            'amount'        => 'required|numeric|min:0.01',
            'cheque_date'   => 'required|date',
            'due_date'      => 'nullable|date',
            'payee_payer'   => 'required|string|max:191',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'note'          => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'Pending';
        Cheque::create($validated);

        return back()->banner('Cheque added successfully.');
    }

    /**
     * Update cheque status.  When marking Cleared, a bank transaction is created.
     */
    public function updateStatus(Request $request, Cheque $cheque)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status'         => 'required|in:Pending,Cleared,Bounced,Cancelled',
            'bank_account_id'=> 'required_if:status,Cleared|nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($cheque, $validated) {
            if ($validated['status'] === 'Cleared') {
                $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);

                // Received cheque → bank Credit; Issued cheque → bank Debit
                $txType = $cheque->type === 'Received' ? 'Credit' : 'Debit';

                $bankTx = BankTransaction::create([
                    'bank_account_id'  => $bankAccount->id,
                    'type'             => $txType,
                    'amount'           => $cheque->amount,
                    'description'      => "Cheque cleared — #{$cheque->cheque_number} ({$cheque->type}) {$cheque->payee_payer}",
                    'reference_type'   => 'Cheque',
                    'transaction_date' => now()->toDateString(),
                ]);

                $bankAccount->recalculateBalance();

                $cheque->update([
                    'status'              => 'Cleared',
                    'bank_account_id'     => $bankAccount->id,
                    'bank_transaction_id' => $bankTx->id,
                ]);
            } else {
                // Bounced / Cancelled — reverse bank transaction if one existed
                if ($cheque->bank_transaction_id) {
                    $bankTx = BankTransaction::find($cheque->bank_transaction_id);
                    if ($bankTx) {
                        $bankAccount = $bankTx->bankAccount;
                        $bankTx->delete();
                        if ($bankAccount) {
                            $bankAccount->recalculateBalance();
                        }
                    }
                }
                $cheque->update([
                    'status'              => $validated['status'],
                    'bank_account_id'     => null,
                    'bank_transaction_id' => null,
                ]);
            }
        });

        return back()->banner('Cheque status updated.');
    }

    public function destroy(Cheque $cheque)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        DB::transaction(function () use ($cheque) {
            if ($cheque->bank_transaction_id) {
                $bankTx = BankTransaction::find($cheque->bank_transaction_id);
                if ($bankTx) {
                    $bankAccount = $bankTx->bankAccount;
                    $bankTx->delete();
                    if ($bankAccount) {
                        $bankAccount->recalculateBalance();
                    }
                }
            }
            $cheque->delete();
        });

        return back()->banner('Cheque deleted.');
    }
}

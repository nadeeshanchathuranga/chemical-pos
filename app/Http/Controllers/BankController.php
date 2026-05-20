<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BankController extends Controller
{
    // ── Accounting Hub (P&L + Cash Flow + Bank Overview) ──────────────────
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $startRaw = $request->input('start_date');
        $endRaw   = $request->input('end_date');

        $from = $startRaw ? Carbon::parse($startRaw)->startOfDay() : null;
        $to   = $endRaw   ? Carbon::parse($endRaw)->endOfDay()     : null;

        $applyWindow = function ($query, $col = 'created_at') use ($from, $to) {
            if ($from) $query->where($col, '>=', $from);
            if ($to)   $query->where($col, '<=', $to);
        };

        // ---- Revenue ----
        $salesQ = Sale::query();
        $applyWindow($salesQ, 'created_at');
        $sales        = $salesQ->get();
        $totalRevenue = (float) $sales->sum('total_amount');
        $totalCOGS    = (float) $sales->sum('total_cost');
        $grossProfit  = $totalRevenue - $totalCOGS;

        // Revenue by payment method
        $revenueByMethod = $sales->groupBy('payment_method')
            ->map(fn($g) => round((float) $g->sum('total_amount'), 2))
            ->toArray();

        // ---- Expenses ----
        $expQ = Expense::query();
        $applyWindow($expQ, 'date');
        $expenses         = $expQ->get();
        $totalExpenses    = (float) $expenses->sum('amount');
        $expenseByCategory = $expenses->groupBy('category')
            ->map(fn($g) => round((float) $g->sum('amount'), 2))
            ->toArray();

        // ---- Supplier Payments ----
        $spQ = SupplierPayment::query();
        $applyWindow($spQ, 'payment_date');
        $totalSupplierPaid = (float) $spQ->sum('amount');

        // ---- Net Profit ----
        $netProfit = $grossProfit - $totalExpenses;

        // ---- Bank accounts overview ----
        $bankAccounts = BankAccount::withCount('transactions')
            ->orderBy('name')
            ->get();

        $totalBankBalance = (float) $bankAccounts->sum('current_balance');

        // ---- Monthly trend (last 6 months) ----
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        });

        $monthlySales = Sale::selectRaw("DATE_FORMAT(created_at,'%Y-%m') as month, SUM(total_amount) as revenue, SUM(total_cost) as cogs")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        $monthlyExpenses = Expense::selectRaw("DATE_FORMAT(date,'%Y-%m') as month, SUM(amount) as total")
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyTrend = $months->map(fn($m) => [
            'month'    => $m,
            'revenue'  => round((float) ($monthlySales[$m]   ?? 0), 2),
            'expenses' => round((float) ($monthlyExpenses[$m] ?? 0), 2),
        ])->values()->toArray();

        return Inertia::render('Accounting/Index', [
            // P&L
            'totalRevenue'       => round($totalRevenue, 2),
            'totalCOGS'          => round($totalCOGS, 2),
            'grossProfit'        => round($grossProfit, 2),
            'totalExpenses'      => round($totalExpenses, 2),
            'netProfit'          => round($netProfit, 2),
            'totalSupplierPaid'  => round($totalSupplierPaid, 2),
            // Breakdowns
            'revenueByMethod'    => $revenueByMethod,
            'expenseByCategory'  => $expenseByCategory,
            // Bank
            'bankAccounts'       => $bankAccounts,
            'totalBankBalance'   => round($totalBankBalance, 2),
            // Trend
            'monthlyTrend'       => $monthlyTrend,
            // Filter state
            'startDate'          => $startRaw,
            'endDate'            => $endRaw,
        ]);
    }

    // ── Bank Account CRUD ──────────────────────────────────────────────────
    public function storeAccount(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'bank_name'       => 'nullable|string|max:191',
            'account_number'  => 'nullable|string|max:100',
            'opening_balance' => 'required|numeric|min:0',
            'note'            => 'nullable|string|max:500',
        ]);

        $validated['current_balance'] = $validated['opening_balance'];
        BankAccount::create($validated);

        return redirect()->route('accounting.index')->banner('Bank account created successfully.');
    }

    public function updateAccount(Request $request, BankAccount $bankAccount)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'bank_name'      => 'nullable|string|max:191',
            'account_number' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        $bankAccount->update($validated);

        return redirect()->route('accounting.index')->banner('Bank account updated successfully.');
    }

    public function destroyAccount(BankAccount $bankAccount)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $bankAccount->delete();

        return redirect()->route('accounting.index')->banner('Bank account deleted.');
    }

    // ── Transactions (deposit / withdraw / manual) ─────────────────────────
    public function storeTransaction(Request $request, BankAccount $bankAccount)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'type'             => 'required|in:Credit,Debit',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
            'reference_type'   => 'nullable|string|max:100',
        ]);

        $validated['bank_account_id'] = $bankAccount->id;

        DB::transaction(function () use ($bankAccount, $validated) {
            BankTransaction::create($validated);
            $bankAccount->recalculateBalance();
        });

        return redirect()->route('banking.show', $bankAccount->id)->banner('Transaction recorded.');
    }

    public function destroyTransaction(BankTransaction $bankTransaction)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $account = $bankTransaction->bankAccount;

        DB::transaction(function () use ($bankTransaction, $account) {
            $bankTransaction->delete();
            $account->recalculateBalance();
        });

        return redirect()->route('banking.show', $account->id)->banner('Transaction deleted.');
    }

    // ── Bank Account Detail Page ───────────────────────────────────────────
    public function showAccount(BankAccount $bankAccount)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $bankAccount->load(['transactions' => fn($q) => $q->orderBy('transaction_date', 'desc')]);

        $totalCredits = (float) $bankAccount->transactions->where('type', 'Credit')->sum('amount');
        $totalDebits  = (float) $bankAccount->transactions->where('type', 'Debit')->sum('amount');

        return Inertia::render('Banking/Show', [
            'bankAccount'  => $bankAccount,
            'totalCredits' => round($totalCredits, 2),
            'totalDebits'  => round($totalDebits, 2),
        ]);
    }
}

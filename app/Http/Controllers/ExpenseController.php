<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public const CATEGORIES = [
        'General',
        'Rent',
        'Utilities',
        'Salaries',
        'Transport',
        'Maintenance',
        'Marketing',
        'Office Supplies',
        'Bank Charges',
        'Other',
    ];

    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $startRaw = $request->input('start_date');
        $endRaw   = $request->input('end_date');
        $category = $request->input('category');

        $query = Expense::with('user')->orderBy('date', 'desc');

        if ($startRaw) {
            $query->where('date', '>=', Carbon::parse($startRaw)->startOfDay());
        }
        if ($endRaw) {
            $query->where('date', '<=', Carbon::parse($endRaw)->endOfDay());
        }
        if ($category && $category !== 'All') {
            $query->where('category', $category);
        }

        $expenses = $query->get();

        $totalExpenses     = (float) $expenses->sum('amount');
        $expenseByCategory = $expenses->groupBy('category')
            ->map(fn($g) => round((float) $g->sum('amount'), 2))
            ->toArray();

        // Sales for same window (for P&L at a glance)
        $salesQuery = Sale::query();
        if ($startRaw) $salesQuery->where('created_at', '>=', Carbon::parse($startRaw)->startOfDay());
        if ($endRaw)   $salesQuery->where('created_at', '<=', Carbon::parse($endRaw)->endOfDay());
        $sales = $salesQuery->get();

        $totalRevenue = (float) $sales->sum('total_amount');
        $totalCOGS    = (float) $sales->sum('total_cost');
        $grossProfit  = $totalRevenue - $totalCOGS;
        $netProfit    = $grossProfit - $totalExpenses;

        return Inertia::render('Expenses/Index', [
            'expenses'          => $expenses,
            'totalExpenses'     => round($totalExpenses, 2),
            'expenseByCategory' => $expenseByCategory,
            'categories'        => self::CATEGORIES,
            'startDate'         => $startRaw,
            'endDate'           => $endRaw,
            'selectedCategory'  => $category,
            'totalRevenue'      => round($totalRevenue, 2),
            'totalCOGS'         => round($totalCOGS, 2),
            'grossProfit'       => round($grossProfit, 2),
            'netProfit'         => round($netProfit, 2),
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'category'    => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = Auth::id();

        Expense::create($validated);

        return redirect()->route('expenses.index')->banner('Expense recorded successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'category'    => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:1000',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->banner('Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $expense->delete();

        return redirect()->route('expenses.index')->banner('Expense deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $startDateRaw = $request->input('start_date');
        $endDateRaw = $request->input('end_date');

        $from = $startDateRaw ?: null;
        $to = $endDateRaw ?: null;

        $applyCreatedWindow = function ($q) use ($from, $to) {
            if ($from && $to) {
                $q->whereDate('created_at', '>=', $from)
                  ->whereDate('created_at', '<=', $to);
            } elseif ($from) {
                $q->whereDate('created_at', '>=', $from);
            } elseif ($to) {
                $q->whereDate('created_at', '<=', $to);
            }
        };

        $salesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer'])
            ->where('status', '!=', 'Open');

        $pendingCreditQuery = Sale::where('is_credit', true)
            ->where('status', 'Open');

        if ($from || $to) {
            $applyCreatedWindow($salesQuery);
            $applyCreatedWindow($pendingCreditQuery);
        }

        $sales = $salesQuery->orderBy('created_at', 'desc')->get();

        $totalCreditBillOutstanding = (float) $pendingCreditQuery->sum('balance_due');
        $totalAdvancePayments = (float) $pendingCreditQuery->sum('paid_amount');

        $customDiscountToLkr = function ($sale) {
            $gross = (float) ($sale->total_amount ?? 0);
            $val = (float) ($sale->custom_discount ?? 0);
            $type = $sale->custom_discount_type ?? 'fixed';
            return $type === 'percent' ? ($gross * $val / 100.0) : $val;
        };

        $categorySales = [];
        $retailGross = 0;
        $wholesaleGross = 0;

        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                $categoryName = $item->product->category->name ?? 'No Category';
                $categorySales[$categoryName] = ($categorySales[$categoryName] ?? 0) + (float) $item->total_price;

                if ($item->sale_type === 'wholesale') {
                    $wholesaleGross += (float) $item->total_price;
                } else {
                    $retailGross += (float) $item->total_price;
                }
            }
        }

        $paymentMethodTotals = $sales->groupBy('payment_method')->map(
            fn($g) => (float) $g->sum('total_amount')
        )->toArray();

        $employeeSalesSummary = [];
        foreach ($sales as $sale) {
            if (!$sale->employee) {
                continue;
            }
            $name = $sale->employee->name;
            $employeeSalesSummary[$name] ??= [
                'Employee Name' => $name,
                'Total Sales Amount' => 0,
            ];
            $gross = (float) ($sale->total_amount ?? 0);
            $prodDisc = (float) ($sale->discount ?? 0);
            $customDisc = $customDiscountToLkr($sale);
            $employeeSalesSummary[$name]['Total Sales Amount'] += ($gross - $prodDisc - $customDisc);
        }

        $totalSaleAmount = (float) $sales->sum('total_amount');
        $totalCost = (float) $sales->sum('total_cost');
        $totalProductDiscountLkr = (float) $sales->sum('discount');
        $totalCustomDiscountLkr = (float) $sales->reduce(fn($c, $s) => $c + $customDiscountToLkr($s), 0.0);
        $netProfit = $totalSaleAmount - $totalCost - ($totalProductDiscountLkr + $totalCustomDiscountLkr);
        $totalTransactions = $sales->count();
        $averageTransactionValue = $totalTransactions > 0 ? ($totalSaleAmount / $totalTransactions) : 0;
        $totalCustomer = (clone $salesQuery)->distinct('customer_id')->count('customer_id');

        return Inertia::render('Reports/Index', [
            'sales' => $sales,
            'totalSaleAmount' => round($totalSaleAmount, 2),
            'totalDiscountLkr' => round($totalProductDiscountLkr, 2),
            'totalCustomDiscountLkr' => round($totalCustomDiscountLkr, 2),
            'netProfit' => round($netProfit, 2),
            'totalTransactions' => $totalTransactions,
            'averageTransactionValue' => round($averageTransactionValue, 2),
            'totalCustomer' => $totalCustomer,
            'startDate' => $startDateRaw,
            'endDate' => $endDateRaw,
            'categorySales' => $categorySales,
            'employeeSalesSummary' => $employeeSalesSummary,
            'paymentMethodTotals' => $paymentMethodTotals,
            'retailGross' => round($retailGross, 2),
            'wholesaleGross' => round($wholesaleGross, 2),
            'totalCreditBillOutstanding' => round($totalCreditBillOutstanding, 2),
            'totalAdvancePayments' => round($totalAdvancePayments, 2),
        ]);
    }

    // stockReport removed — stock management is handled in StockTransaction module

    public function searchByCode(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json([
                'products' => [],
                'totalQuantity' => 0,
                'remainingQuantity' => 0,
            ]);
        }

        $products = Product::where('code', $code)
            ->select([
                'batch_no',
                'total_quantity',
                'stock_quantity',
                'expire_date',
                'purchase_date',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalQuantity = $products->sum('total_quantity');
        $remainingQuantity = $products->sum('stock_quantity');

        return response()->json([
            'products' => $products,
            'totalQuantity' => $totalQuantity,
            'remainingQuantity' => $remainingQuantity,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Report $report)
    {
        //
    }

    public function edit(Report $report)
    {
        //
    }

    public function update(Request $request, Report $report)
    {
        //
    }

    public function destroy(Report $report)
    {
        //
    }
}

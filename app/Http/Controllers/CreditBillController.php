<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CreditBillController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $search = $request->input('search');
        $customerName = $request->input('customer_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Sale::with('customer')
            ->where('is_credit', true)
            ->whereIn('status', ['Open', 'Closed'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('order_id', 'like', "%{$search}%")
                      ->orWhere('total_amount', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                      });
            });
        }

        if ($customerName) {
            $query->whereHas('customer', function ($query) use ($customerName) {
                $query->where('name', 'like', "%{$customerName}%");
            });
        }

        if ($startDate || $endDate) {
            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }
        }

            $creditSales = $query->paginate(10);
            $creditSales->appends($request->only('search', 'customer_name', 'start_date', 'end_date'));

            return Inertia::render('CreditBills/Index', [
            'creditSales' => $creditSales,
            'filters' => [
                'search' => $search,
                'customer_name' => $customerName,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}

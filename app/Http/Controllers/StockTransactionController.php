<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class StockTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index(Request $request)
     {
         $startDate       = $request->input('start_date');
         $endDate         = $request->input('end_date');
         $productSearch   = $request->input('product_search');
         $categoryId      = $request->input('category_id');
         $transactionType = $request->input('transaction_type');

         $query = StockTransaction::with('product.supplier', 'product.category');

         if ($startDate && $endDate) {
             $query->whereBetween('transaction_date', [$startDate, $endDate]);
         } elseif ($startDate) {
             $query->whereDate('transaction_date', '>=', $startDate);
         } elseif ($endDate) {
             $query->whereDate('transaction_date', '<=', $endDate);
         }

         if ($productSearch) {
             $query->whereHas('product', function ($q) use ($productSearch) {
                 $q->where('name', 'like', "%{$productSearch}%")
                   ->orWhere('code', 'like', "%{$productSearch}%")
                   ->orWhere('barcode', 'like', "%{$productSearch}%");
             });
         }

         if ($categoryId) {
             $query->whereHas('product', function ($q) use ($categoryId) {
                 $q->where('category_id', $categoryId);
             });
         }

         if ($transactionType) {
             $query->where('transaction_type', $transactionType);
         }

         $allStockTransactions = $query->orderBy('created_at', 'desc')->get();
         $categories = Category::orderBy('name')->get(['id', 'name']);

         return Inertia::render('StockTransaction/Index', [
            'allStockTransactions'  => $allStockTransactions,
            'totalStockTransactions'=> $allStockTransactions->count(),
            'categories'            => $categories,
            'startDate'             => $startDate,
            'endDate'               => $endDate,
            'productSearch'         => $productSearch,
            'categoryId'            => $categoryId ? (int) $categoryId : null,
            'transactionType'       => $transactionType,
        ]);
     }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(StockTransaction $stockTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockTransaction $stockTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */







     public function update(Request $request)
     {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }




         $Stockreason = StockTransaction::find($request->stock_id);

         if ($Stockreason != null)
         {
             $Stockreason->reason = $request->input('reason');



             $Stockreason->save();
             return back()
                 ->with('success', 'reason updated successfully !');
         }
         else
         {
             return back()
                 ->with('error', 'Could not find the reason');
         }
     }











    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockTransaction $stockTransaction)
    {
        //
    }
}

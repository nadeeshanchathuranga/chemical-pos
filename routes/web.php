<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReturnItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CompanyInfoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\SizeController;

use App\Http\Controllers\QuotationController;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\ManualPosController;
use App\Http\Controllers\CreditBillController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });
Route::get('/dashboard', function () {
    return redirect()->route('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        //
        if (Gate::allows('hasRole', ['Cashier'])) {
            return redirect()->route('pos.index');
        }

        $pendingCreditBills = \App\Models\Sale::query()
            ->where('is_credit', true)
            ->where('status', 'Open');

        $advancePaymentsTotal = (float) $pendingCreditBills->sum('paid_amount');
        $totalCreditAmount = (float) $pendingCreditBills->sum('balance_due');

        return Inertia::render('Dashboard', [
            'advancePaymentsTotal' => round($advancePaymentsTotal, 2),
            'totalCreditAmount' => round($totalCreditAmount, 2),
        ]);

    })->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::post('/suppliers/{supplier}/payments', [SupplierPaymentController::class, 'store'])->name('supplier.payments.store');
    Route::delete('/supplier-payments/{supplierPayment}', [SupplierPaymentController::class, 'destroy'])->name('supplier.payments.destroy');
    Route::post('products/{product}', [ProductController::class, 'update']);
    Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjustStock');
    Route::post('products-variant', [ProductController::class, 'productVariantStore'])->name('productVariant');

    Route::post('products-size', [ProductController::class, 'sizeStore'])->name('productSize');


    // Route::resource('company-info', CompanyInfoController::class)->name('companyInfo.index');
    Route::get('/company-info', [CompanyInfoController::class, 'index'])->name('companyInfo.index');
    Route::post('/company-info/{companyInfo}', [CompanyInfoController::class, 'update'])->name('companyInfo.update');


    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'getProduct'])->name('pos.getProduct');
    Route::post('/get-coupon', [PosController::class, 'getCoupon'])->name('pos.getCoupon');
    Route::get('/pos/returns/orders', [PosController::class, 'getReturnOrders'])->name('pos.return.orders');
    Route::get('/pos/returns/orders/{sale}/items', [PosController::class, 'getReturnOrderItems'])->name('pos.return.items');
    Route::post('/pos/returns/submit', [PosController::class, 'submitReturn'])->name('pos.return.submit');
    Route::post('/pos/submit', [PosController::class, 'submit'])->name('pos.checkout');
    Route::get('/credit-bills', [CreditBillController::class, 'index'])->name('creditBills.index');
    Route::resource('payment', PaymentController::class);
    Route::resource('reports', ReportController::class);
    Route::get('/batch-management/search', [ReportController::class, 'searchByCode']);
    Route::resource('customers', CustomerController::class);
    Route::resource('colors', ColorController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('sizes', SizeController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('transactionHistory', TransactionHistoryController::class );
    Route::post('/transactions/delete', [TransactionHistoryController::class, 'destroy'])->name('transactions.delete');
    Route::resource('stock-transition', StockTransactionController::class);
    Route::resource('manualpos', ManualPosController::class);



    Route::resource('/quotation', QuotationController::class);
    Route::post('/api/save-quotation', [QuotationController::class, 'saveQuotationPdf']);



 Route::get('/add_promotion', [ProductController::class, 'addPromotion']);
    Route::post('/submit_promotion', [ProductController::class, 'submitPromotion']);
    Route::get('/products/{id}/promotion-items', [ProductController::class, 'getPromotionItems']);


    // Route::get('/stock-transition', [PosController::class, 'index'])->name('pos.index');
    // Route::post('/stock-transition', [PosController::class, 'getProduct'])->name('pos.getProduct');
  Route::post('/api/products2', [ProductController::class, 'fetchProducts2']);

    Route::resource('return-bill', ReturnItemController::class);

    // ── Accounting ──────────────────────────────────────────────────────────
    Route::get('/accounting', [BankController::class, 'index'])->name('accounting.index');

    // Expenses
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);

    // Bank accounts
    Route::post('/bank-accounts', [BankController::class, 'storeAccount'])->name('bank-accounts.store');
    Route::put('/bank-accounts/{bankAccount}', [BankController::class, 'updateAccount'])->name('bank-accounts.update');
    Route::delete('/bank-accounts/{bankAccount}', [BankController::class, 'destroyAccount'])->name('bank-accounts.destroy');

    // Bank account detail + transactions
    Route::get('/banking/{bankAccount}', [BankController::class, 'showAccount'])->name('banking.show');
    Route::post('/banking/{bankAccount}/transactions', [BankController::class, 'storeTransaction'])->name('banking.transactions.store');
    Route::delete('/banking/transactions/{bankTransaction}', [BankController::class, 'destroyTransaction'])->name('banking.transactions.destroy');




    // ── Cheque management ───────────────────────────────────────────────────
    Route::get('/cheques', [ChequeController::class, 'index'])->name('cheques.index');
    Route::post('/cheques', [ChequeController::class, 'store'])->name('cheques.store');
    Route::patch('/cheques/{cheque}/status', [ChequeController::class, 'updateStatus'])->name('cheques.updateStatus');
    Route::delete('/cheques/{cheque}', [ChequeController::class, 'destroy'])->name('cheques.destroy');

    Route::post('/api/products', [ProductController::class, 'fetchProducts']);
    Route::post('/api/sale/items', [ReturnItemController::class, 'fetchSaleItems'])->name('sale.items');


});

Route::get('/barcode/{id}', [CategoryController::class, 'showBarcode']);

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CashierClosingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\CashierSessionController;
use App\Http\Controllers\PriceRuleController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED AREA
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */
    Route::prefix('master')->group(function () {
        // Price Rules
        Route::get('/harga', [PriceRuleController::class, 'index'])->name('price-rules.index');
        Route::post('/harga', [PriceRuleController::class, 'store'])->name('price-rules.store');
        Route::put('/harga/{id}', [PriceRuleController::class, 'update'])->name('price-rules.update');
        Route::delete('/harga/{id}', [PriceRuleController::class, 'destroy'])->name('price-rules.destroy');

        // Other master routes (if any) can go here
    });

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    Route::resource('products', ProductController::class);

    /*
    |--------------------------------------------------------------------------
    | Stocks
    |--------------------------------------------------------------------------
    */
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('/stocks/create', [StockController::class, 'create'])->name('stocks.create');
    Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
    Route::post('/stocks/transfer', [StockController::class, 'transfer'])->name('stocks.transfer');

    /*
    |--------------------------------------------------------------------------
    | POS
    |--------------------------------------------------------------------------
    */
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/scan', [PosController::class, 'scan'])->name('pos.scan');
    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
    Route::post('/pos/add-item', [PosController::class, 'addItem'])->name('pos.addItem');
    Route::post('/pos/update-qty', [PosController::class, 'updateQty'])->name('pos.updateQty');
    Route::post('/pos/update-discount', [PosController::class, 'updateDiscount'])->name('pos.updateDiscount');
    Route::post('/pos/pay', [PosController::class, 'pay'])->name('pos.pay');
    Route::post('/pos/override-stock', [PosController::class, 'overrideStock'])->name('pos.overrideStock');
    Route::post('/pos/cancel', [PosController::class, 'cancel'])->name('pos.cancel');


    /*
    |--------------------------------------------------------------------------
    | Cashier Sessions
    |--------------------------------------------------------------------------
    */
    Route::get('/session/open', [CashierSessionController::class, 'openForm'])->name('cashier.open.form');
    Route::post('/session/open', [CashierSessionController::class, 'open'])->name('cashier.open');
    Route::post('/session/close', [CashierSessionController::class, 'close'])->name('cashier.close');

    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */

Route::prefix('transactions')->group(function () {

    // LIST
    Route::get('/', [TransactionController::class, 'index'])
        ->name('transactions.index');

    // EDIT
    Route::get('/{id}/edit', [TransactionController::class, 'edit'])
        ->name('transactions.edit');

    // UPDATE
    Route::put('/{id}', [TransactionController::class, 'update'])
        ->name('transactions.update');

    // REQUEST EDIT (KASIR)
    Route::post('/{id}/request-edit', [TransactionController::class, 'requestEdit'])
        ->name('transactions.request-edit');

    // APPROVE (OWNER)
    Route::put('/{id}/approve', [TransactionController::class, 'approve'])
        ->name('transactions.approve');

    // CANCEL / HAPUS PENDING
    Route::delete('/{id}', [TransactionController::class, 'destroy'])
        ->name('transactions.destroy');

    // STRUK
    Route::get('/{id}/struk', [TransactionController::class, 'struk'])
        ->name('transactions.struk');
});

    /*
    |--------------------------------------------------------------------------
    | Members
    |--------------------------------------------------------------------------
    */
    Route::resource('members', MemberController::class);
    Route::post('/members/redeem', [MemberController::class, 'redeem'])->name('members.redeem');

    /*
    |--------------------------------------------------------------------------
    | Purchase Orders
    |--------------------------------------------------------------------------
    */
    Route::prefix('po')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('create', [PurchaseOrderController::class, 'create'])->name('po.create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('{id}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
        Route::put('{id}', [PurchaseOrderController::class, 'update'])->name('po.update');
        Route::delete('{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');

        Route::post('{id}/item', [PurchaseOrderController::class, 'addItem'])->name('po.addItem');
        Route::put('item/{id}', [PurchaseOrderController::class, 'updateItem'])->name('po.updateItem');
        Route::delete('item/{id}', [PurchaseOrderController::class, 'deleteItem'])->name('po.deleteItem');
        Route::post('{id}/approve', [PurchaseOrderController::class, 'approve'])->name('po.approve');
        Route::post('{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('po.cancel');
        Route::post('{id}/receive', [PurchaseOrderController::class, 'receive'])->name('po.receive');
    });

    /*
    |--------------------------------------------------------------------------
    | Returns
    |--------------------------------------------------------------------------
    */
    Route::prefix('returns')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('returns.index');
        Route::post('/', [ReturnController::class, 'store'])->name('returns.store');
        Route::post('{id}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
        Route::post('{id}/reject', [ReturnController::class, 'reject'])->name('returns.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/sales/{id}', [ReportController::class, 'salesDetail'])->name('reports.sales.detail');
    Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/sales-csv', [ReportController::class, 'salesCsv'])->name('reports.sales.csv');

    /*
    |--------------------------------------------------------------------------
    | Approval
    |--------------------------------------------------------------------------
    */
    Route::post('/approval/stock', [ApprovalController::class, 'stockOverride'])->name('approval.stock');

    /*
    |--------------------------------------------------------------------------
    | Cashier Closing
    |--------------------------------------------------------------------------
    */
    Route::get('/closing', [CashierClosingController::class, 'close'])->middleware('role:kasir')->name('closing');
});

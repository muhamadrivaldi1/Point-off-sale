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

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Stocks
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::post('/stocks/transfer', [StockController::class, 'transfer'])->name('stocks.transfer');
    Route::get('/stocks/create', [StockController::class, 'create'])->name('stocks.create');
    Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');

    // POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/scan', [PosController::class, 'scan'])->name('pos.scan');
    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
    Route::post('/pos/add-item', [PosController::class, 'addItem'])->name('pos.addItem');
    Route::post('/pos/update-qty', [PosController::class, 'updateQty'])->name('pos.updateQty');
    Route::post('/pos/pay', [PosController::class, 'pay'])->name('pos.pay');

    Route::get('/session/open', [CashierSessionController::class, 'openForm'])->name('cashier.open.form');
    Route::post('/session/open', [CashierSessionController::class, 'open'])->name('cashier.open');
    Route::post('/session/close', [CashierSessionController::class, 'close'])->name('cashier.close');
    
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{id}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::put('/transactions/{id}/approve', [TransactionController::class, 'approveEdit'])->name('transactions.approve');

    Route::post('/transactions/{id}/request-edit',[TransactionController::class, 'requestEdit'])->name('transactions.request');
    Route::post('/transactions/{id}/approve-edit',[TransactionController::class, 'approveEdit'])->name('transactions.approve');
    Route::post('/transactions/{id}/request-edit', [TransactionController::class, 'requestEdit'])->name('transactions.request-edit');

    Route::get('/transactions/{id}/struk',[TransactionController::class, 'struk'])->name('transactions.struk');

    // Members
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{id}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{id}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{id}', [MemberController::class, 'destroy'])->name('members.destroy');
    Route::post('/members/redeem', [MemberController::class, 'redeem']);

    // Purchase Orders
    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::delete('/po/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::get('/po/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/{id}', [PurchaseOrderController::class, 'update'])->name('po.update');
    Route::post('/po/{id}/item', [PurchaseOrderController::class, 'addItem'])->name('po.addItem');
    Route::put('/po/item/{id}', [PurchaseOrderController::class, 'updateItem'])->name('po.updateItem');
    Route::delete('/po/item/{id}', [PurchaseOrderController::class, 'deleteItem'])->name('po.deleteItem');
    Route::post('/po/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('po.approve');
    Route::post('/po/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('po.cancel');
    Route::post('/po/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('po.receive');

    // Returns
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::post('/returns/{id}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
    Route::post('/returns/{id}/reject', [ReturnController::class, 'reject'])->name('returns.reject');

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/sales/{id}', [ReportController::class, 'salesDetail'])->name('reports.sales.detail');
    Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/sales-csv', [ReportController::class, 'salesCsv'])->name('reports.sales.csv');

    // Approval
    Route::post('/approval/stock', [ApprovalController::class, 'stockOverride'])->name('approval.stock');

    // Cashier Closing
    Route::get('/closing', [CashierClosingController::class, 'close'])->middleware('role:kasir');
});

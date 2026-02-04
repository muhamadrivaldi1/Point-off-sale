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

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED AREA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::post('/stocks/transfer', [StockController::class, 'transfer'])->name('stocks.transfer');

    Route::get('/pos', [PosController::class, 'index'])
        ->name('pos');
    Route::post('/pos/scan', [PosController::class, 'scan'])
        ->name('pos.scan');
    Route::get('/pos/search', [PosController::class, 'search'])
        ->name('pos.search');
    Route::post('/pos/add-item', [PosController::class, 'addItem'])
        ->name('pos.addItem');
    Route::post('/pos/update-qty', [PosController::class, 'updateQty'])
        ->name('pos.updateQty');
    Route::post('/pos/pay', [PosController::class, 'pay'])
        ->name('pos.pay');


    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');

    Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])
        ->name('transactions.edit');

    Route::put('/transactions/{id}', [TransactionController::class, 'update'])
        ->name('transactions.update');

    Route::post(
        '/transactions/{id}/request-edit',
        [TransactionController::class, 'requestEdit']
    )->name('transactions.request');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{id}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{id}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{id}', [MemberController::class, 'destroy'])->name('members.destroy');
    Route::post('/members/redeem', [MemberController::class, 'redeem']);

    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/po/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');

    Route::post('/po/{id}/item', [PurchaseOrderController::class, 'addItem']);
    Route::put('/po/item/{id}', [PurchaseOrderController::class, 'updateItem']);
    Route::delete('/po/item/{id}', [PurchaseOrderController::class, 'deleteItem']);

    Route::post('/po/{id}/approve', [PurchaseOrderController::class, 'approve']);
    Route::post('/po/{id}/receive', [PurchaseOrderController::class, 'receive']);

    Route::get('/closing', [CashierClosingController::class, 'close'])
        ->middleware('role:kasir');

    Route::get('/reports/sales', [ReportController::class, 'sales'])
        ->middleware('role:owner,supervisor');

    Route::get('/reports/stock', [ReportController::class, 'stock'])
        ->middleware('role:owner,supervisor');

    Route::post('/approval/stock', [ApprovalController::class, 'stockOverride'])
        ->middleware('role:owner,supervisor');
});

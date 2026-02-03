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

    /* DASHBOARD */
    Route::get('/dashboard', [DashboardController::class,'index'])
        ->name('dashboard');

    /* PRODUCT & STOCK */
    Route::resource('products', ProductController::class);
    Route::resource('stocks', StockController::class);

    /* ===============================
       POS (🔥 JANTUNG SISTEM)
    =============================== */
    Route::get('/pos', [PosController::class,'index'])->name('pos');

    Route::post('/pos/start', [PosController::class,'start']);
    Route::post('/pos/scan', [PosController::class,'scan']);
    Route::post('/pos/add-item', [PosController::class,'addItem']);
    Route::post('/pos/verify', [PosController::class,'verify']);
    Route::post('/pos/pay', [PosController::class,'pay']);

    /* TRANSAKSI */
    Route::resource('transactions', TransactionController::class)
        ->only(['index','show']);

    /* MEMBER */
    Route::resource('members', MemberController::class);
    Route::post('/members/redeem', [MemberController::class,'redeem']);

    /* ===============================
       PURCHASE ORDER
    =============================== */
    Route::get('/po', [PurchaseOrderController::class,'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class,'create']);
    Route::post('/po/store', [PurchaseOrderController::class,'store']);
    Route::get('/po/{id}/edit', [PurchaseOrderController::class,'edit'])->name('po.edit');

    Route::post('/po/{id}/item', [PurchaseOrderController::class,'addItem']);
    Route::put('/po/item/{id}', [PurchaseOrderController::class,'updateItem']);
    Route::delete('/po/item/{id}', [PurchaseOrderController::class,'deleteItem']);

    Route::post('/po/{id}/approve', [PurchaseOrderController::class,'approve']);
    Route::post('/po/{id}/receive', [PurchaseOrderController::class,'receive']);

    /* ===============================
       CLOSING KASIR
    =============================== */
    Route::get('/closing', [CashierClosingController::class,'close'])
        ->middleware('role:kasir');

    /* ===============================
       REPORT
    =============================== */
    Route::get('/reports/sales', [ReportController::class,'sales'])
        ->middleware('role:owner,supervisor');

    Route::get('/reports/stock', [ReportController::class,'stock'])
        ->middleware('role:owner,supervisor');

    /* ===============================
       APPROVAL
    =============================== */
    Route::post('/approval/stock', [ApprovalController::class,'stockOverride'])
        ->middleware('role:owner,supervisor');
});

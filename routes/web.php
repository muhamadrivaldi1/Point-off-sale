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
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\UserManagementController;

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */
    Route::prefix('master')->group(function () {
        Route::get('/harga', [PriceRuleController::class, 'index'])->name('price-rules.index');
        Route::post('/harga', [PriceRuleController::class, 'store'])->name('price-rules.store');
        Route::put('/harga/{id}', [PriceRuleController::class, 'update'])->name('price-rules.update');
        Route::delete('/harga/{id}', [PriceRuleController::class, 'destroy'])->name('price-rules.destroy');

        // Other master routes (if any) can go here
    });

    // kredit
    Route::middleware(['auth'])->prefix('pos/kredit')->name('pos.kredit.')->group(function () {

        // Halaman detail kredit
        Route::get('{trx_id}', [PosController::class, 'showKredit'])->name('show');

        // Simpan catatan kredit
        Route::post('notes', [PosController::class, 'saveKreditNotes'])->name('notes');

        // Lunasi penuh
        Route::post('lunasi', [PosController::class, 'lunasiKredit'])->name('lunasi');

        // Bayar sebagian
        Route::post('partial', [PosController::class, 'partialPayKredit'])->name('partial');
    });

    Route::middleware(['auth'])->prefix('pos/kredit')->name('pos.kredit.')->group(function () {

        // daftar kredit
        Route::get('/', [PosController::class, 'kreditIndex'])->name('index');

        // detail kredit
        Route::get('{trx_id}', [PosController::class, 'showKredit'])->name('show');

        // bayar cicilan
        Route::post('partial', [PosController::class, 'partialPayKredit'])->name('partial');

        // print struk kredit
        Route::get('print/{trx_id}', [PosController::class, 'printKredit'])->name('print');
    });


    Route::middleware(['auth'])->group(function () {

        Route::get('/users',            [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create',     [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users',           [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit',  [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}',       [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}',    [UserManagementController::class, 'destroy'])->name('users.delete');
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

    Route::get('/stocks/{id}/edit', [StockController::class, 'edit'])->name('stocks.edit');
    Route::put('/stocks/{id}', [StockController::class, 'update'])->name('stocks.update');
    Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->name('stocks.destroy');

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
    Route::post('/pos/update-unit', [PosController::class, 'updateUnit'])->name('pos.updateUnit');
    Route::post('/pos/update-qty-manual', [PosController::class, 'updateQtyManual'])->name('pos.updateQtyManual');
    Route::post('/pos/update-qty', [PosController::class, 'updateQty'])->name('pos.updateQty');
    Route::post('/pos/update-discount', [PosController::class, 'updateDiscount'])->name('pos.updateDiscount');
    Route::post('/pos/pay', [PosController::class, 'pay'])->name('pos.pay');
    Route::post('/pos/override-stock', [PosController::class, 'overrideStock'])->name('pos.overrideStock');
    Route::post('/pos/remove-item', [PosController::class, 'removeItem']);
    Route::post('/pos/cancel', [PosController::class, 'cancel'])->name('pos.cancel');
    Route::post('/pos/override-owner', [PosController::class, 'overrideOwner']);
    Route::post('/pos/set-member', [PosController::class, 'setMember']);
    Route::post('/pos/set-discount', [PosController::class, 'setDiscount']);
    Route::post('/pos/remove-item', [PosController::class, 'removeItem']);
    Route::get('/pos/search-member', [PosController::class, 'searchMember']);
    Route::get('/pos/get-member', [PosController::class, 'getMember']);
    Route::post('/pos/cleanup-empty', [PosController::class, 'cleanupEmptyPending']);
    Route::post('/pos/reopen-transaction', [PosController::class, 'reopenTransaction']);


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

        Route::get('/', [TransactionController::class, 'index'])
            ->name('transactions.index');
        Route::get('/{id}/edit', [TransactionController::class, 'edit'])
            ->name('transactions.edit');
        Route::put('/{id}', [TransactionController::class, 'update'])
            ->name('transactions.update');
        Route::post('/{id}/request-edit', [TransactionController::class, 'requestEdit'])
            ->name('transactions.request-edit');
        Route::put('/{id}/approve', [TransactionController::class, 'approve'])
            ->name('transactions.approve');
        Route::delete('/{id}', [TransactionController::class, 'destroy'])
            ->name('transactions.destroy');
        Route::get('/{id}/struk', [TransactionController::class, 'struk'])
            ->name('transactions.struk');
        Route::get('/pos/kredit/print/{id}', [PosController::class, 'printKredit'])
            ->name('print.kredit');
    });

    /*
    |--------------------------------------------------------------------------
    | Members
    |--------------------------------------------------------------------------
    */
    Route::resource('members', MemberController::class);
    Route::post('/members/redeem', [MemberController::class, 'redeem'])->name('members.redeem');
    Route::get('/members/{id}/barcode', [MemberController::class, 'printBarcode'])->name('members.printBarcode');

    /*
    |--------------------------------------------------------------------------
    | Purchase Orders
    |--------------------------------------------------------------------------
    */
    Route::prefix('po')->group(function () {
        Route::get('/',            [PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('create',       [PurchaseOrderController::class, 'create'])->name('po.create');
        Route::get('{id}',         [PurchaseOrderController::class, 'show'])->name('po.show');
        Route::get('{id}/edit',    [PurchaseOrderController::class, 'edit'])->name('po.edit');
        Route::post('/',           [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::put('{id}',         [PurchaseOrderController::class, 'update'])->name('po.update');
        Route::delete('{id}',      [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
        Route::post('{id}/update-header', [PurchaseOrderController::class, 'updateHeader'])->name('po.updateHeader');
        Route::post('{id}/item',   [PurchaseOrderController::class, 'addItem'])->name('po.addItem');
        Route::put('item/{id}',    [PurchaseOrderController::class, 'updateItem'])->name('po.updateItem');
        Route::delete('item/{id}', [PurchaseOrderController::class, 'deleteItem'])->name('po.deleteItem');
        Route::post('{id}/approve', [PurchaseOrderController::class, 'approve'])->name('po.approve');
        Route::post('{id}/cancel',  [PurchaseOrderController::class, 'cancel'])->name('po.cancel');
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

    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
        // Penjualan
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/detail/{id}', [ReportController::class, 'salesDetail'])->name('sales.detail');
        Route::get('/sales/csv', [ReportController::class, 'salesCsv'])->name('sales.csv');

        // Stok & Penerimaan
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/penerimaan', [ReportController::class, 'penerimaan'])->name('penerimaan'); // Pastikan baris ini ada

        // Piutang & Hutang
        Route::get('/piutang', [ReportController::class, 'piutang'])->name('piutang');
        Route::get('/hutang', [ReportController::class, 'hutang'])->name('hutang');

        // Akuntansi
        Route::get('/journal', [ReportController::class, 'journal'])->name('journal');
        Route::get('/laba-rugi', [ReportController::class, 'labaRugi'])->name('laba_rugi');
    });

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

    Route::get('/cashier/sessions', [CashierSessionController::class, 'index'])->name('cashier.sessions');
    Route::get('/closing', [CashierClosingController::class, 'close'])->middleware('role:kasir')->name('closing');

    Route::prefix('warehouses')->group(function () {

        Route::get('/', [WarehouseController::class, 'index'])
            ->name('warehouses.index');

        Route::post('/store', [WarehouseController::class, 'store'])
            ->name('warehouses.store');

        Route::post('/set-active/{id}', [WarehouseController::class, 'setActive'])
            ->name('warehouses.setActive');
    });

    Route::get('/struk/setting', [App\Http\Controllers\StrukSettingController::class, 'index'])->name('struk.setting');
    Route::post('/struk/setting', [App\Http\Controllers\StrukSettingController::class, 'update'])->name('struk.setting.update');

    Route::post('/cashier/opening-balance/update', [CashierSessionController::class, 'updateOpeningBalance'])->name('cashier.updateOpeningBalance'); // bisa tambah middleware role jika perlu
});

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/hutang', [ReportController::class, 'hutang'])->name('hutang');
    Route::get('/hutang/{id}', [ReportController::class, 'hutangDetail'])->name('hutang.detail');
    Route::get('hutang/pay/{id}', [ReportController::class, 'hutangPay'])->name('hutang.pay');
});

Route::get('reports/journal', [ReportController::class, 'journal'])->name('reports.journal');

Route::get('reports/laba-rugi', [ReportController::class, 'labaRugi'])->name('reports.laba-rugi');
Route::get('/reports/laba-rugi/export', [ReportController::class, 'exportLabaRugi'])->name('reports.laba-rugi.export');
// Route::get('/reports/laba-rugi/export', [ReportController::class, 'exportLabaRugi']);

Route::get('/reports/neraca', [ReportController::class, 'neraca'])->name('reports.neraca');
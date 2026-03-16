<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Stock;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\CashierSession;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /* ══════════════════════════════════════════════
           1. PENJUALAN (Hanya Type: Income)
           - Otomatis Reset Setiap Hari jam 00:00
        ══════════════════════════════════════════════ */
        $salesQuery = Transaction::where('type', 'income')
            ->whereIn('status', ['paid', 'kredit']);

        // Data Hari Ini (RESET HARIAN)
        $todaySales = (clone $salesQuery)->whereDate('created_at', today())->sum('total');
        $todayTransactions = (clone $salesQuery)->whereDate('created_at', today())->count();

        // Data Bulan Ini (Untuk Banner Laba)
        $monthSales = (clone $salesQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $monthTransactions = (clone $salesQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        /* ══════════════════════════════════════════════
           2. PEMBELIAN
           - Menghitung PO harian & bulanan
        ══════════════════════════════════════════════ */
        $pembelianQuery = PurchaseOrder::whereIn('status', ['approved', 'received', 'Received', 'paid']);

        // Hari Ini (RESET HARIAN)
        $todayPembelian = (clone $pembelianQuery)->whereDate('tanggal', today())->sum('total');

        // Bulan Ini
        $monthPembelian = (clone $pembelianQuery)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('total');

        $monthPembelianCount = (clone $pembelianQuery)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        /* ══════════════════════════════════════════════
           3. HUTANG SUPPLIER (Akumulasi - Tidak Reset Harian)
        ══════════════════════════════════════════════ */
        $hutangQuery = PurchaseOrder::whereNotIn('status', ['paid', 'received', 'Received', 'cancelled', 'canceled']);

        $hutangSupplier = (clone $hutangQuery)->sum('total');
        $hutangSupplierCount = (clone $hutangQuery)->count();

        $hutangJatuhTempo = (clone $hutangQuery)
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<=', now())
            ->count();

        /* ══════════════════════════════════════════════
           4. PIUTANG PELANGGAN (Akumulasi - Tidak Reset Harian)
        ══════════════════════════════════════════════ */
        $kreditTrx = Transaction::where('type', 'income')
            ->where('status', 'kredit')
            ->withSum('cicilan as total_terbayar', 'amount')
            ->get();

        $totalPiutang = $kreditTrx->sum(fn($t) => max($t->total - ($t->total_terbayar ?? 0), 0));
        $piutangCount = $kreditTrx->filter(fn($t) => ($t->total - ($t->total_terbayar ?? 0)) > 0)->count();

        $piutangBulanIni = Transaction::where('type', 'income')
            ->where('status', 'kredit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        /* ══════════════════════════════════════════════
           5. PENGELUARAN (Expense)
           - Otomatis Reset Setiap Hari
        ══════════════════════════════════════════════ */
        $expenseQuery = Transaction::where('type', 'expense');

        // Hari Ini (RESET HARIAN)
        $todayExpense = (clone $expenseQuery)->whereDate('created_at', today())->sum('total');
        
        // Bulan Ini
        $monthExpense = (clone $expenseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $monthExpenseCount = (clone $expenseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // List pengeluaran terbaru (Limit 10 transaksi terakhir)
        $recentExpenses = Transaction::where('type', 'expense')
            ->select('id', 'trx_number', 'total as amount', 'created_at as date', 'description')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($exp) {
                return (object)[
                    'id' => $exp->id,
                    'name' => $exp->description ?? $exp->trx_number,
                    'amount' => $exp->amount,
                    'date' => $exp->date
                ];
            });

        /* ══════════════════════════════════════════════
           6. STOK & PRODUK TERLARIS (Reset Harian)
        ══════════════════════════════════════════════ */
        $lowStockProducts = ProductUnit::with('product')
            ->whereHas('stock', fn($q) => $q->where('qty', '<=', 5))
            ->limit(5)->get();

        // Produk Terlaris HANYA HARI INI
        $bestProducts = TransactionItem::with('unit.product')
            ->whereHas('transaction', fn($q) => 
                $q->where('type', 'income')
                  ->whereIn('status', ['paid', 'kredit'])
                  ->whereDate('created_at', today()) // Filter reset harian
            )
            ->select('product_unit_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_unit_id')
            ->orderByDesc('total_qty')->limit(5)->get();

        /* ══════════════════════════════════════════════
           7. TRANSAKSI TERBARU (Hanya Income Hari Ini)
        ══════════════════════════════════════════════ */
        $recentTransactions = Transaction::with('member')
            ->where('type', 'income')
            ->whereDate('created_at', today()) // Menampilkan transaksi hari ini saja
            ->latest()->limit(8)->get();

        /* ══════════════════════════════════════════════
           8. ESTIMASI LABA BERSIH & SESI
        ══════════════════════════════════════════════ */
        $labaKotorEstimasi = $monthSales - $monthExpense;

        $openSession = false;
        if ($user->role === 'kasir') {
            $openSession = CashierSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->exists();
        }

        return view('dashboard.index', compact(
            'todaySales', 'todayTransactions', 'monthSales', 'monthTransactions',
            'monthPembelian', 'monthPembelianCount', 'todayPembelian',
            'hutangSupplier', 'hutangSupplierCount', 'hutangJatuhTempo',
            'totalPiutang', 'piutangCount', 'piutangBulanIni',
            'todayExpense', 'monthExpense', 'monthExpenseCount', 'recentExpenses',
            'lowStockProducts', 'bestProducts',
            'recentTransactions', 'labaKotorEstimasi', 'openSession'
        ));
    }
}
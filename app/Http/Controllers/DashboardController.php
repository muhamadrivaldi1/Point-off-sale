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
        ══════════════════════════════════════════════ */
        $salesQuery = Transaction::where('type', 'income')
            ->whereIn('status', ['paid', 'kredit']);

        $todaySales = (clone $salesQuery)->whereDate('created_at', today())->sum('total');
        $todayTransactions = (clone $salesQuery)->whereDate('created_at', today())->count();

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
        ══════════════════════════════════════════════ */
        $pembelianQuery = PurchaseOrder::whereIn('status', ['approved', 'received', 'Received', 'paid']);

        $monthPembelian = (clone $pembelianQuery)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('total');

        $monthPembelianCount = (clone $pembelianQuery)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        $todayPembelian = (clone $pembelianQuery)->whereDate('tanggal', today())->sum('total');

        /* ══════════════════════════════════════════════
           3. HUTANG SUPPLIER
        ══════════════════════════════════════════════ */
        $hutangQuery = PurchaseOrder::whereNotIn('status', ['paid', 'received', 'Received', 'cancelled', 'canceled']);

        $hutangSupplier = (clone $hutangQuery)->sum('total');
        $hutangSupplierCount = (clone $hutangQuery)->count();

        $hutangJatuhTempo = (clone $hutangQuery)
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereMonth('tanggal_jatuh_tempo', now()->month)
            ->whereYear('tanggal_jatuh_tempo', now()->year)
            ->count();

        /* ══════════════════════════════════════════════
           4. PIUTANG PELANGGAN
        ══════════════════════════════════════════════ */
        $kreditTrx = Transaction::where('type', 'income')
            ->where('status', 'kredit')
            ->withSum('cicilan as total_terbayar', 'amount')
            ->get();

        $totalPiutang = $kreditTrx->sum(fn($t) => max($t->total - ($t->total_terbayar ?? 0), 0));
        $piutangCount = $kreditTrx->filter(fn($t) => ($t->total - ($t->total_terbayar ?? 0)) > 0)->count();

        $piutangJatuhTempo = Transaction::where('type', 'income')
            ->where('status', 'kredit')
            ->whereNotNull('due_date')
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->whereRaw('(total - COALESCE((SELECT SUM(amount) FROM kredit_payments WHERE transaction_id = transactions.id), 0)) > 0')
            ->count();

        $piutangBulanIni = Transaction::where('type', 'income')
            ->where('status', 'kredit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        /* ══════════════════════════════════════════════
           5. PENGELUARAN (Ambil dari Type: Expense)
           Mapping dari TRX-MANUAL & Akun 2-1002
        ══════════════════════════════════════════════ */
        $expenseQuery = Transaction::where('type', 'expense')
        ->where('trx_number', 'LIKE', 'TRX-MANUAL%');

        $todayExpense = (clone $expenseQuery)->whereDate('created_at', today())->sum('total');
        
        $monthExpense = (clone $expenseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $monthExpenseCount = (clone $expenseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // List pengeluaran untuk kolom tengah
        $recentExpenses = Transaction::where('type', 'expense')
            ->select('id', 'trx_number', 'total as amount', 'created_at as date', 'description')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($exp) {
                return (object)[
                    'id' => $exp->id,
                    'name' => $exp->description ?? $exp->trx_number, // Utamakan deskripsi (ex: Bayar Listrik)
                    'amount' => $exp->amount,
                    'date' => $exp->date
                ];
            });

        /* ══════════════════════════════════════════════
           6. STOK & PRODUK TERLARIS
        ══════════════════════════════════════════════ */
        $lowStock = Stock::where('qty', '<=', 5)->where('qty', '>', 0)->count();

        $lowStockProducts = ProductUnit::with('product')
            ->whereHas('stock', fn($q) => $q->where('qty', '<=', 5)->where('qty', '>', 0))
            ->limit(5)->get();

        $bestProducts = TransactionItem::with('unit.product')
            ->whereHas('transaction', fn($q) => 
                $q->where('type', 'income')->whereIn('status', ['paid', 'kredit'])->whereDate('created_at', today())
            )
            ->select('product_unit_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_unit_id')
            ->orderByDesc('total_qty')->limit(5)->get();

        /* ══════════════════════════════════════════════
           7. TRANSAKSI TERBARU (Hanya Income)
        ══════════════════════════════════════════════ */
        $recentTransactions = Transaction::with('member')
            ->where('type', 'income')
            ->whereIn('status', ['paid', 'kredit'])
            ->latest()->limit(8)->get();

        /* ══════════════════════════════════════════════
           8. ESTIMASI LABA BERSIH & SESI
        ══════════════════════════════════════════════ */
        $labaKotorEstimasi = $monthSales - $monthExpense;

        $openSession = false;
        if ($user->role === 'kasir') {
            $openSession = CashierSession::where('user_id', $user->id)->where('status', 'open')->exists();
        }

        return view('dashboard.index', compact(
            'todaySales', 'todayTransactions', 'monthSales', 'monthTransactions',
            'monthPembelian', 'monthPembelianCount', 'todayPembelian',
            'hutangSupplier', 'hutangSupplierCount', 'hutangJatuhTempo',
            'totalPiutang', 'piutangCount', 'piutangJatuhTempo', 'piutangBulanIni',
            'todayExpense', 'monthExpense', 'monthExpenseCount', 'recentExpenses',
            'lowStock', 'lowStockProducts', 'bestProducts',
            'recentTransactions', 'labaKotorEstimasi', 'openSession'
        ));
    }
}
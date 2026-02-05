<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Stock;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // ===============================
        // RINGKASAN
        // ===============================
        $todaySales = Transaction::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total');

        $todayTransactions = Transaction::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->count();

        $monthSales = Transaction::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where('status', 'paid')
            ->sum('total');

        $lowStock = Stock::where('location', 'toko')
            ->where('qty', '<=', 5)
            ->count();

        // ===============================
        // PRODUK TERLARIS HARI INI
        // ===============================
        $bestProducts = TransactionItem::with('unit.product')
            ->whereDate('created_at', $today)
            ->get()
            ->groupBy('product_unit_id')
            ->map(function ($items) {
                return (object) [
                    'unit' => $items->first()->unit,
                    'total_qty' => $items->sum('qty'),
                ];
            })
            ->sortByDesc('total_qty')
            ->take(5);

        // ===============================
        // STOK RENDAH
        // ===============================
        $lowStockProducts = ProductUnit::with('product', 'stock')
            ->whereHas('stock', function ($q) {
                $q->where('location', 'toko')
                  ->where('qty', '<=', 5);
            })
            ->get();

        // ===============================
        // DATA REVENUE GRAFIK 6 BULAN TERAKHIR
        // ===============================
        $revenueLabels = [];
        $revenueData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $label = $month->format('M'); // Jan, Feb, Mar
            $total = Transaction::where('status', 'paid')
                        ->whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->sum('total');

            $revenueLabels[] = $label;
            $revenueData[] = $total;
        }

        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'monthSales',
            'lowStock',
            'bestProducts',
            'lowStockProducts',
            'revenueLabels',
            'revenueData'
        ));
    }
}

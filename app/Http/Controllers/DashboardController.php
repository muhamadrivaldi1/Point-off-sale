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

        // ===============================
        // RINGKASAN
        // ===============================
        $todaySales = Transaction::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total');

        $todayTransactions = Transaction::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->count();

        $monthSales = Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'paid')
            ->sum('total');

        $lowStock = Stock::where('location', 'toko')
            ->where('qty', '<=', 5)
            ->count();

        // ===============================
        // PRODUK TERLARIS HARI INI
        // ===============================
        // Menggunakan Eloquent agar bisa akses relasi di Blade
        $bestProducts = TransactionItem::with('unit.product')
            ->whereDate('created_at', $today)
            ->get()
            ->groupBy('product_unit_id')
            ->map(function ($items) {
                return (object) [
                    'unit' => $items->first()->unit,        // relasi ProductUnit
                    'total_qty' => $items->sum('qty'),     // total qty terjual
                ];
            })
            ->sortByDesc('total_qty')
            ->take(5);

        $lowStockProducts = ProductUnit::with('product')
            ->whereHas('stock', function ($q) {
                $q->where('location', 'toko')
                    ->where('qty', '<=', 5);
            })
            ->get();

        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'monthSales',
            'lowStock',
            'bestProducts',
            'lowStockProducts'
        ));
    }
}

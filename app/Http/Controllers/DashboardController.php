<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Stock;
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
        $bestProducts = DB::table('transaction_items')
            ->join('product_units', 'transaction_items.product_unit_id', '=', 'product_units.id')
            ->join('products', 'product_units.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.qty) as total_qty')
            )
            ->whereDate('transaction_items.created_at', $today)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'monthSales',
            'lowStock',
            'bestProducts'
        ));
    }
}

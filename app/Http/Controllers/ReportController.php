<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Stock;

class ReportController extends Controller
{
    /* ===============================
       LAPORAN PENJUALAN
    =============================== */
    public function sales(Request $r)
    {
        // default tanggal: dari awal bulan sampai hari ini
        $from = $r->from ?? now()->startOfMonth()->toDateString();
        $to   = $r->to   ?? now()->toDateString();

        $data = Transaction::where('status', 'paid')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->paginate(10)          // pagination 10 per halaman
            ->withQueryString();    // biar filter tanggal tetap di URL saat paginate

        return view('reports.sales', compact('data', 'from', 'to'));
    }

    /* ===============================
       EXPORT PENJUALAN KE CSV
    =============================== */
    public function salesCsv(Request $r)
    {
        $from = $r->from ?? now()->startOfMonth()->toDateString();
        $to   = $r->to   ?? now()->toDateString();

        $transactions = Transaction::where('status', 'paid')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'sales_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['Tanggal', 'Invoice', 'Total'];

        $callback = function () use ($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($transactions as $trx) {
                fputcsv($file, [
                    $trx->created_at->format('d/m/Y H:i'),
                    $trx->invoice,
                    $trx->total
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ===============================
       DETAIL PENJUALAN
    =============================== */
    public function salesDetail($id)
    {
        $trx = Transaction::with('items.unit.product')
            ->where('id', $id)
            ->where('status', 'paid')
            ->firstOrFail();

        return view('reports.sales_detail', compact('trx'));
    }

    /* ===============================
       LAPORAN STOK
    =============================== */
    public function stock()
    {
        $data = Stock::with('unit.product')->get();

        return view('reports.stock', compact('data'));
    }
}

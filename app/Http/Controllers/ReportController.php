<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Stock;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /* ===============================
       LAPORAN PENJUALAN
    =============================== */
    public function sales(Request $r)
    {
        // Fallback jika tanggal tidak diisi
        $from = $r->input('from', now()->startOfMonth()->toDateString());
        $to   = $r->input('to', now()->toDateString());

        $data = Transaction::with(['user', 'member'])
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalOmzet = Transaction::where('status', 'paid')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('total');

        return view('reports.sales', compact('data', 'from', 'to', 'totalOmzet'));
    }

    /* ===============================
       EXPORT PENJUALAN KE CSV (Excel-Friendly)
    =============================== */
    public function salesCsv(Request $r)
{
    $from = $r->input('from', now()->startOfMonth()->toDateString());
    $to   = $r->input('to', now()->toDateString());

    $transactions = Transaction::with(['user', 'member', 'items.unit.product'])
        ->where('status', 'paid')
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to)
        ->orderBy('created_at', 'desc')
        ->get();

    $filename = 'Laporan_Penjualan_' . now()->format('d-M-Y_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    $callback = function () use ($transactions, $from, $to) {
        $file = fopen('php://output', 'w');

        // UTF-8 BOM
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // ✅ HEADER LAPORAN (PERBAIKAN FORMAT)
        fputcsv($file, ['LAPORAN PENJUALAN'], ';');
        fputcsv($file, ['Periode', date('d/m/Y', strtotime($from)) . ' s/d ' . date('d/m/Y', strtotime($to))], ';');
        fputcsv($file, ['Dicetak', date('d/m/Y H:i:s')], ';');
        fputcsv($file, ['Total Transaksi', $transactions->count() . ' transaksi'], ';');
        fputcsv($file, [], ';');

        // ✅ HEADER TABEL
        fputcsv($file, [
            'No',
            'Tanggal',
            'Jam',
            'Invoice',
            'Kasir',
            'Member',
            'Subtotal',
            'Diskon',
            'Total',
            'Bayar',
            'Kembalian',
            'Jumlah Item'
        ], ';');

        // ✅ DATA TRANSAKSI (PERBAIKAN)
        $no = 1;
        $grandTotal = 0;
        $totalDiskon = 0;
        $totalBayar = 0;

        foreach ($transactions as $trx) {
            $itemCount = $trx->items->sum('qty');
            $subtotal = $trx->items->sum(function($item) {
                return ($item->price - ($item->discount ?? 0)) * $item->qty;
            });

            // ✅ FORMAT TANGGAL YANG BENAR
            $tanggal = $trx->created_at ? $trx->created_at->format('d/m/Y') : '-';
            $jam = $trx->created_at ? $trx->created_at->format('H:i:s') : '-';

            fputcsv($file, [
                $no++,
                $tanggal,  // ✅ Format d/m/Y
                $jam,      // ✅ Format H:i:s
                $trx->trx_number ?? '-',
                $trx->user->name ?? '-',
                $trx->member->name ?? 'Non-Member',
                $subtotal,
                $trx->discount ?? 0,
                $trx->total,
                $trx->paid,
                $trx->change,
                $itemCount
            ], ';');

            $grandTotal += $trx->total;
            $totalDiskon += ($trx->discount ?? 0);
            $totalBayar += $trx->paid;
        }

        // ✅ TOTAL
        fputcsv($file, [], ';');
        fputcsv($file, [
            '',
            '',
            '',
            '',
            '',
            'TOTAL',
            '',
            $totalDiskon,
            $grandTotal,
            $totalBayar,
            '',
            ''
        ], ';');

        fputcsv($file, [], ';');
        fputcsv($file, [], ';');

        // ✅ DETAIL ITEM TERJUAL
        fputcsv($file, ['DETAIL PRODUK TERJUAL'], ';');
        fputcsv($file, [], ';');
        fputcsv($file, [
            'Tanggal',
            'Invoice',
            'Nama Produk',
            'Barcode',
            'Satuan',
            'Qty',
            'Harga Satuan',
            'Diskon Item',
            'Subtotal',
            'Lokasi'
        ], ';');

        foreach ($transactions as $trx) {
            $tanggal = $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-';
            
            foreach ($trx->items as $item) {
                fputcsv($file, [
                    $tanggal,
                    $trx->trx_number,
                    $item->unit->product->name ?? '-',
                    $item->unit->barcode ?? '-',
                    $item->unit->unit_name ?? '-',
                    $item->qty,
                    $item->price,
                    $item->discount ?? 0,
                    ($item->price - ($item->discount ?? 0)) * $item->qty,
                    strtoupper($item->location ?? '-')
                ], ';');
            }
        }

        fputcsv($file, [], ';');
        fputcsv($file, [], ';');

        // ✅ REKAP PRODUK TERLARIS
        fputcsv($file, ['REKAP PRODUK TERLARIS'], ';');
        fputcsv($file, [], ';');
        fputcsv($file, [
            'No',
            'Nama Produk',
            'Total Qty',
            'Total Omzet'
        ], ';');

        $productStats = [];
        foreach ($transactions as $trx) {
            foreach ($trx->items as $item) {
                $productName = $item->unit->product->name ?? '-';
                if (!isset($productStats[$productName])) {
                    $productStats[$productName] = [
                        'qty' => 0,
                        'omzet' => 0
                    ];
                }
                $productStats[$productName]['qty'] += $item->qty;
                $productStats[$productName]['omzet'] += ($item->price - ($item->discount ?? 0)) * $item->qty;
            }
        }

        // Sort by qty descending
        uasort($productStats, function($a, $b) {
            return $b['qty'] - $a['qty'];
        });
        
        $no = 1;
        foreach ($productStats as $productName => $stats) {
            fputcsv($file, [
                $no++,
                $productName,
                $stats['qty'],
                $stats['omzet']
            ], ';');
        }

        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
}

    /* ===============================
       DETAIL PENJUALAN
    =============================== */
    public function salesDetail($id)
    {
        $trx = Transaction::with(['items.unit.product', 'user', 'member'])
            ->where('id', $id)
            ->where('status', 'paid')
            ->firstOrFail();

        return view('reports.sales_detail', compact('trx'));
    }

    /* ===============================
       LAPORAN STOK
    =============================== */
    public function stock(Request $request)
    {
        $query = Stock::with('unit.product');

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('q')) {
            $query->whereHas('unit.product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            });
        }

        $data = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('reports.stock', compact('data'));
    }

    /* ===============================
       EXPORT STOK KE CSV
    =============================== */
    public function stockCsv(Request $request)
{
    $query = Stock::with('unit.product');

    if ($request->filled('location')) {
        $query->where('location', $request->location);
    }

    if ($request->filled('q')) {
        $query->whereHas('unit.product', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->q . '%');
        });
    }

    $stocks = $query->orderBy('id', 'desc')->get();

    $filename = 'Laporan_Stok_' . now()->format('d-M-Y_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function () use ($stocks) {
        $file = fopen('php://output', 'w');

        // UTF-8 BOM
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // ✅ PERBAIKAN FORMAT TANGGAL
        fputcsv($file, ['LAPORAN STOK BARANG'], ';');
        fputcsv($file, ['Dicetak', date('d/m/Y H:i:s')], ';'); // ✅ DIPERBAIKI
        fputcsv($file, [], ';');

        fputcsv($file, [
            'No',
            'Nama Produk',
            'Barcode',
            'Satuan',
            'Lokasi',
            'Stok',
            'Harga',
            'Nilai Stok'
        ], ';');

        $no = 1;
        $totalNilai = 0;

        foreach ($stocks as $stock) {
            $nilaiStok = ($stock->qty ?? 0) * ($stock->unit->price ?? 0);
            $totalNilai += $nilaiStok;

            fputcsv($file, [
                $no++,
                $stock->unit->product->name ?? '-',
                $stock->unit->barcode ?? '-',
                $stock->unit->unit_name ?? '-',
                strtoupper($stock->location ?? '-'),
                $stock->qty ?? 0,
                $stock->unit->price ?? 0,
                $nilaiStok
            ], ';');
        }

        fputcsv($file, [], ';');
        fputcsv($file, [
            '',
            '',
            '',
            '',
            '',
            'TOTAL NILAI STOK',
            '',
            $totalNilai
        ], ';');

        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
    }

}
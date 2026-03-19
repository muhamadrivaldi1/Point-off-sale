<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Member;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\KreditPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /* ===============================
       LAPORAN PENJUALAN
    =============================== */
public function sales(Request $r)
{
    // 1. Ambil data untuk dropdown filter
    $users = \App\Models\User::orderBy('name')->get();
    $members = \App\Models\Member::orderBy('name')->get();

    // 2. Tangkap Input Filter
    $from = $r->input('from', now()->startOfMonth()->toDateString());
    $to   = $r->input('to', now()->toDateString());
    $invoice = $r->input('invoice');
    $kasir_id = $r->input('kasir_id');
    $member_id = $r->input('member_id');
    $product    = $r->input('product');
    $supplier   = $r->input('supplier');

    // 3. Bangun Query
    $query = Transaction::with(['user', 'member', 'items.unit.product.supplier'])
        ->whereIn('status', ['paid', 'kredit'])
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to);

    // Terapkan filter yang sama dengan CSV
    if ($invoice) {
        $query->where('trx_number', 'like', '%' . $invoice . '%');
    }
    if ($kasir_id) {
        $query->where('user_id', $kasir_id);
    }
    if ($member_id) {
        $query->where('member_id', $member_id);
    }
    if ($product) {
        $query->whereHas('items.unit.product', function ($q) use ($product) {
            $q->where('name', 'like', '%' . $product . '%');
        });
    }
    if ($supplier) {
    $query->whereHas('items.unit.product.supplier', function ($q) use ($supplier) {
        $q->where('nama_supplier', 'like', '%' . $supplier . '%');
    });
}

    // 4. Eksekusi Data (Gunakan clone agar sum total tidak terpengaruh pagination)
    $totalOmzet = (clone $query)->sum('total');
    $data = $query->orderBy('created_at', 'desc')
                  ->paginate(15)
                  ->withQueryString();

    return view('reports.sales', compact('data', 'from', 'to', 'totalOmzet', 'users', 'members'));
}

/* ===============================
    EXPORT PENJUALAN KE CSV
=============================== */
public function salesCsv(Request $r)
{
    $from       = $r->input('from', now()->startOfMonth()->toDateString());
    $to         = $r->input('to', now()->toDateString());
    $invoice    = $r->input('invoice');
    $kasir_id   = $r->input('kasir_id');
    $member_id  = $r->input('member_id');
    $product    = $r->input('product');   // 🔥 TAMBAHAN
    $supplier   = $r->input('supplier');  // 🔥 TAMBAHAN

    $query = Transaction::with(['user', 'member', 'items.unit.product.supplier'])
        ->whereIn('status', ['paid', 'kredit'])
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to);

    if ($invoice) {
        $query->where('trx_number', 'like', '%' . $invoice . '%');
    }

    if ($kasir_id) {
        $query->where('user_id', $kasir_id);
    }

    if ($member_id) {
        $query->where('member_id', $member_id);
    }

    if ($product) {
        $query->whereHas('items.unit.product', function ($q) use ($product) {
            $q->where('name', 'like', '%' . $product . '%');
        });
    }

    if ($supplier) {
        $query->whereHas('items.unit.product.supplier', function ($q) use ($supplier) {
            $q->where('nama_supplier', 'like', '%' . $supplier . '%');
        });
    }

    // EKSEKUSI
    $transactions = $query->orderBy('created_at', 'desc')->get();

    // FILE
    $filename = 'Laporan_Penjualan_' . now()->format('d-M-Y_His') . '.csv';
    $headers  = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function () use ($transactions, $from, $to) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // HEADER
        fputcsv($file, ['LAPORAN PENJUALAN'], ';');
        fputcsv($file, ['Periode', date('d/m/Y', strtotime($from)) . ' s/d ' . date('d/m/Y', strtotime($to))], ';');
        fputcsv($file, ['Total Transaksi', $transactions->count()], ';');
        fputcsv($file, [], ';');

        fputcsv($file, [
            'No',
            'Tanggal',
            'Jam',
            'Invoice',
            'Kasir',
            'Member',
            'Barang',
            'Supplier',
            'Subtotal',
            'Diskon',
            'Total',
            'Bayar',
            'Kembalian',
            'Status',
            'Jml Item'
        ], ';');

        $no = 1;
        $grandTotal = 0;
        $totalDiskon = 0;

        foreach ($transactions as $trx) {

            $itemCount = $trx->items->sum('qty');
            $subtotal  = $trx->items->sum(fn($i) => $i->price * $i->qty);

            $barang = $trx->items->map(function ($i) {
                return $i->unit->product->name . ' x' . $i->qty;
            })->implode(', ');

            $supplier = $trx->items->map(function ($i) {
                return optional($i->unit->product->supplier)->nama_supplier;
            })->filter()->unique()->implode(', ');

            fputcsv($file, [
                $no++,
                $trx->created_at?->format('d/m/Y') ?? '-',
                $trx->created_at?->format('H:i:s') ?? '-',
                $trx->trx_number ?? '-',
                $trx->user->name ?? '-',
                $trx->member->name ?? 'Umum',
                $barang,
                $supplier ?: '-',
                $subtotal,
                $trx->discount ?? 0,
                $trx->total,
                $trx->paid,
                $trx->change,
                strtoupper($trx->status),
                $itemCount,
            ], ';');

            $grandTotal  += $trx->total;
            $totalDiskon += ($trx->discount ?? 0);
        }

        fputcsv($file, [], ';');
        fputcsv($file, ['', '', '', '', '', 'TOTAL', '', '', '', $totalDiskon, $grandTotal, '', '', '', ''], ';');

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

    /* ===============================
       DETAIL PENJUALAN
    =============================== */
    public function salesDetail($id)
    {
        $trx = Transaction::with(['items.unit.product', 'user', 'member'])->findOrFail($id);
        return view('reports.sales_detail', compact('trx'));
    }

    /* ===============================
       EXPORT STOK KE CSV
    =============================== */
    public function stockCsv(Request $request)
{
    // 1. Ambil Parameter Filter yang sama dengan halaman Mutasi
    $from = $request->input('from', now()->startOfMonth()->toDateString());
    $to   = $request->input('to', now()->toDateString());
    $status = $request->input('status');
    $search = $request->input('search');
    $supplier_id = $request->input('supplier_id');

    // 2. Query ke tabel StockMutation (sesuaikan nama modelnya, misal StockMutation atau Stock)
    // Di sini saya asumsikan tabel mutasi kamu namanya StockMutation
    $query = \App\Models\StockMutation::with(['unit.product.supplier'])
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to);

    // 3. Terapkan Filter yang aktif (Sama dengan tampilan di web)
    if ($status) {
        $query->where('status', $status);
    }
    if ($search) {
        $query->whereHas('unit.product', fn($q) => $q->where('name', 'like', "%{$search}%"));
    }
    if ($supplier_id) {
        $query->whereHas('unit.product', fn($q) => $q->where('supplier_id', $supplier_id));
    }

    $mutations = $query->orderBy('created_at', 'desc')->get();

    // 4. Pengaturan Header File
    $filename = 'History_Mutasi_Stok_' . now()->format('d-M-Y_His') . '.csv';
    $headers  = [
        'Content-Type' => 'text/csv; charset=UTF-8', 
        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ];

    $callback = function () use ($mutations, $from, $to) {
        $file = fopen('php://output', 'w');
        // Tambahkan BOM agar karakter khusus terbaca di Excel
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Header Laporan
        fputcsv($file, ['LAPORAN HISTORY MUTASI STOK'], ';');
        fputcsv($file, ['Periode', $from . ' s/d ' . $to], ';');
        fputcsv($file, ['Dicetak', date('d/m/Y H:i:s')], ';');
        fputcsv($file, [], ';');

        // Header Kolom (Disesuaikan dengan permintaan kamu sebelumnya)
        fputcsv($file, ['No', 'Waktu', 'Nama Produk', 'Supplier', 'Status', 'Masuk', 'Keluar', 'Saldo Akhir', 'Referensi'], ';');

        $no = 1;
        foreach ($mutations as $row) {
            fputcsv($file, [
                $no++,
                $row->created_at->format('d/m/Y H:i'),
                $row->unit->product->name ?? '-',
                $row->unit->product->supplier->nama_supplier ?? '-',
                strtoupper($row->status),
                $row->type == 'in' ? $row->qty : 0, // Kolom Masuk
                $row->type == 'out' ? $row->qty : 0, // Kolom Keluar
                $row->stock_after,
                $row->reference ?? '-'
            ], ';');
        }
        
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

    /* ===============================
       LAPORAN PENERIMAAN
    =============================== */
public function penerimaan(Request $request)
{
    $from = $request->input('from', now()->startOfMonth()->toDateString());
    $to   = $request->input('to', now()->toDateString());

    $data = $this->getPenerimaanData($request)
                 ->paginate(15)
                 ->withQueryString();

    return view('reports.penerimaan', compact('from', 'to', 'data'));
}

public function penerimaanExport(Request $request)
{
    $from     = $request->input('from', now()->startOfMonth()->toDateString());
    $to       = $request->input('to', now()->toDateString());
    $keyword  = $request->input('product');
    $results  = $this->getPenerimaanData($request)->get();
    $fileName = "Laporan_Penerimaan_" . date('Ymd_His') . ".csv";

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0",
    ];

    $callback = function () use ($results, $from, $to, $keyword) {
        $file = fopen('php://output', 'w');

        // BOM agar Excel baca karakter UTF-8 dengan benar
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header laporan
        fputcsv($file, ['LAPORAN PENERIMAAN BARANG (PURCHASE ORDER)'], ';');
        fputcsv($file, ['Periode:', $from . ' s/d ' . $to], ';');
        fputcsv($file, ['Waktu Cetak:', date('d/m/Y H:i:s')], ';');
        fputcsv($file, [], ';');

        // Header kolom — 9 kolom
        fputcsv($file, [
            'NO',
            'NOMOR PO',
            'TANGGAL',
            'SUPPLIER',
            'BARANG',
            'QTY',
            'METODE PEMBAYARAN',
            'STATUS',
            'TOTAL NOMINAL',
        ], ';');

        $no         = 1;
        $grandTotal = 0;

        foreach ($results as $row) {
            $total       = (float) $row->total;
            $grandTotal += $total;

            // Filter item sesuai keyword (sama seperti di blade)
            $filteredItems = $keyword
                ? $row->items->filter(function ($item) use ($keyword) {
                    $name = $item->productUnit->product->name ?? '';
                    return stripos($name, $keyword) !== false;
                  })
                : $row->items;

            // (int) cast: 1.00 → 1, 10.00 → 10
            $barang = $filteredItems->map(function ($item) {
                $name = optional($item->productUnit->product)->name ?? '-';
                return $name . ' x' . (int) $item->qty;
            })->implode(', ');

            $qty = (int) $filteredItems->sum('qty');

            fputcsv($file, [
                $no++,
                $row->po_number,
                \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y'),
                optional($row->supplier)->nama_supplier ?? '-',
                $barang,
                $qty,
                strtoupper($row->jenis_pembayaran),
                strtoupper($row->status),
                $total,
            ], ';');
        }

        // Grand total: 9 kolom, label kolom ke-8, nilai kolom ke-9
        fputcsv($file, [], ';');
        fputcsv($file, ['', '', '', '', '', '', '', 'GRAND TOTAL', $grandTotal], ';');

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

private function getPenerimaanData(Request $request)
{
    $from    = $request->input('from', now()->startOfMonth()->toDateString());
    $to      = $request->input('to', now()->toDateString());
    $product = $request->input('product');

    $query = \App\Models\PurchaseOrder::with([
        'supplier',
        'items.productUnit.product',
    ])->whereBetween('tanggal', [$from, $to]);

    if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
        $query->where('supplier_id', $request->supplier_id);
    }

    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    if ($product) {
        $query->whereHas('items.productUnit.product', function ($q) use ($product) {
            $q->where('name', 'like', '%' . $product . '%');
        });
    }

    return $query->orderBy('tanggal', 'desc');
}

    /* ===============================
       LAPORAN MUTASI STOK
    =============================== */
    public function stock(Request $request)
{
    $from = $request->input('from', now()->startOfMonth()->toDateString());
    $to   = $request->input('to', now()->toDateString());
    $status = $request->input('status'); // Sesuai name="status" di Blade
    $search = $request->input('search');
    $supplier_id = $request->input('supplier_id');

    // Gunakan query builder agar bisa dipakai ulang untuk Export
    $query = StockMutation::with(['unit.product.supplier'])
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to);

    // Filter berdasarkan Status Mutasi
    if ($status) {
        $query->where('status', $status);
    }

    // Filter berdasarkan Pencarian Nama Produk / SKU
    if ($search) {
        $query->whereHas('unit.product', function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    }

    // Filter berdasarkan Supplier
    if ($supplier_id) {
        $query->whereHas('unit.product', function($q) use ($supplier_id) {
            $q->where('supplier_id', $supplier_id);
        });
    }

    $data = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
    
    // Ambil data supplier untuk dropdown filter
    $suppliers = \App\Models\Supplier::all(); 

    return view('reports.stock', compact('data', 'from', 'to', 'suppliers'));
}



    /* ===============================
       LAPORAN PIUTANG
    =============================== */
    public function piutang(Request $request)
{
    // Mengambil parameter untuk dikirim kembali ke View (agar input filter tetap terisi)
    $from   = $request->input('from', now()->startOfMonth()->toDateString());
    $to     = $request->input('to', now()->toDateString());
    $status = $request->input('status');
    $search = $request->input('search');

    // Gunakan query yang sama dengan export
    $query = $this->getPiutangQuery($request);

    // Hitung total sisa piutang dari data yang sudah difilter
    $totalSisaPiutang = (clone $query)->get()->sum(function ($item) {
        return $item->total - ($item->total_terbayar ?? 0);
    });

    $data = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
    
    return view('reports.piutang', compact('data', 'from', 'to', 'totalSisaPiutang', 'status', 'search'));
}

public function piutangExport(Request $request)
{
    $from   = $request->input('from', now()->startOfMonth()->toDateString());
    $to     = $request->input('to', now()->toDateString());
    $search = $request->input('search', 'Semua Pelanggan');

    // Ambil data berdasarkan filter aktif di layar
    $query = $this->getPiutangQuery($request);
    
    $fileName = "Laporan_Piutang_" . date('Ymd_His') . ".csv";
    
    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function () use ($query, $from, $to, $search) {
        $file = fopen('php://output', 'w');
        
        // Tambahkan BOM agar Excel membaca UTF-8 (mencegah karakter aneh)
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // --- HEADER LAPORAN (STYLE PROFESSIONAL) ---
        fputcsv($file, ['LAPORAN PIUTANG PELANGGAN'], ';');
        fputcsv($file, ['Periode:', $from . ' s/d ' . $to], ';');
        fputcsv($file, ['Pencarian:', $search], ';');
        fputcsv($file, ['Waktu Cetak:', date('d/m/Y H:i:s')], ';');
        fputcsv($file, [], ';'); // Baris Kosong

        // HEADER KOLOM
        fputcsv($file, [
            'TANGGAL', 
            'NO. INVOICE', 
            'PELANGGAN', 
            'TOTAL TRX', 
            'TOTAL DIBAYAR', 
            'SISA PIUTANG', 
            'STATUS'
        ], ';');

        $grandTotalPiutang = 0;

        $query->chunk(500, function ($rows) use ($file, &$grandTotalPiutang) {
            foreach ($rows as $row) {
                $dibayar = (float)($row->total_terbayar ?? 0);
                $sisa    = (float)($row->total - $dibayar);
                $grandTotalPiutang += $sisa;

                // Logika Status Label
                if ($dibayar >= $row->total && $row->total > 0) {
                    $statusLabel = 'LUNAS';
                } elseif ($dibayar > 0) {
                    $statusLabel = 'CICILAN';
                } else {
                    $statusLabel = 'BELUM BAYAR';
                }

                fputcsv($file, [
                    $row->created_at->format('d/m/Y H:i'),
                    $row->trx_number,
                    $row->member->name ?? 'Pelanggan Umum',
                    (float)$row->total,
                    $dibayar,
                    $sisa,
                    $statusLabel
                ], ';');
            }
        });

        // --- FOOTER (TOTAL AKHIR) ---
        fputcsv($file, [], ';');
        fputcsv($file, ['', '', '', '', 'TOTAL SISA PIUTANG', $grandTotalPiutang, ''], ';');

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

private function getPiutangQuery(Request $request)
{
    $from   = $request->input('from', now()->startOfMonth()->toDateString());
    $to     = $request->input('to', now()->toDateString());
    $status = $request->input('status');
    $search = $request->input('search');

    // Query dasar: Pastikan relasi 'cicilan' sesuai dengan nama relasi di Model Transaction
    $query = Transaction::with(['member'])
        ->withSum('cicilan as total_terbayar', 'amount')
        ->where('status', 'kredit')
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to);

    // Filter Berdasarkan Nama Pelanggan atau No Invoice
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('trx_number', 'like', "%{$search}%")
              ->orWhereHas('member', fn($m) => $m->where('name', 'like', "%{$search}%"));
        });
    }

    // Filter Berdasarkan Status Pembayaran
    if ($status === 'belum_bayar') {
        $query->has('cicilan', '=', 0);
    } elseif ($status === 'cicilan') {
        // Menggunakan havingSum agar lebih akurat dibanding subquery manual
        $query->havingRaw('total_terbayar > 0 AND total_terbayar < total');
    } elseif ($status === 'lunas') {
        $query->havingRaw('total_terbayar >= total');
    }

    return $query;
}

    /* ===============================
       LAPORAN HUTANG SUPPLIER
       ← Hanya pakai kolom `total`, bukan total_amount
    =============================== */
public function hutang(Request $request)
{
    $from = $request->get('from', now()->startOfMonth()->toDateString());
    $to   = $request->get('to', now()->toDateString());
    $search = $request->get('search');
    $status = $request->get('status');

    $query       = $this->getHutangQuery($request);
    $totalHutang = (clone $query)->sum('total'); 

    $data = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();
    
    return view('reports.hutang', compact('data', 'from', 'to', 'totalHutang', 'search', 'status'));
}

public function hutangExport(Request $request)
{
    $from   = $request->get('from', now()->startOfMonth()->toDateString());
    $to     = $request->get('to', now()->toDateString());
    $search = $request->get('search', 'Semua Supplier');

    $query    = $this->getHutangQuery($request);
    $fileName = "Laporan_Hutang_Supplier_" . date('Ymd_His') . ".csv";
    
    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function () use ($query, $from, $to, $search) {
        $file = fopen('php://output', 'w');
        
        // Tambahkan BOM agar Excel membaca UTF-8 (mencegah karakter berantakan)
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // --- HEADER LAPORAN ---
        fputcsv($file, ['LAPORAN HUTANG KE SUPPLIER (UNPAID PO)'], ';');
        fputcsv($file, ['Periode:', $from . ' s/d ' . $to], ';');
        fputcsv($file, ['Pencarian:', $search], ';');
        fputcsv($file, ['Waktu Cetak:', date('d/m/Y H:i:s')], ';');
        fputcsv($file, [], ';'); // Baris Kosong

        // HEADER KOLOM
        fputcsv($file, [
            'TANGGAL PO', 
            'NO. PO', 
            'SUPPLIER', 
            'TOTAL HUTANG', 
            'STATUS'
        ], ';');

        $grandTotalHutang = 0;

        $query->chunk(500, function ($rows) use ($file, &$grandTotalHutang) {
            foreach ($rows as $row) {
                $totalRow = (float)$row->total;
                $grandTotalHutang += $totalRow;

                fputcsv($file, [
                    $row->tanggal ? $row->tanggal->format('d/m/Y') : '-',
                    $row->po_number,
                    $row->supplier->nama_supplier ?? 'Supplier Umum',
                    $totalRow,
                    strtoupper($row->status ?? 'DRAFT')
                ], ';');
            }
        });

        // --- FOOTER (TOTAL AKHIR) ---
        fputcsv($file, [], ';');
        fputcsv($file, ['', '', 'TOTAL KESELURUHAN HUTANG', $grandTotalHutang, ''], ';');

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

private function getHutangQuery(Request $request)
{
    $from   = $request->get('from', now()->startOfMonth()->toDateString());
    $to     = $request->get('to', now()->toDateString());
    $search = $request->get('search');
    $status = $request->get('status');

    // Filter dasar: Ambil yang belum lunas (menghindari paid, received, cancelled)
    $query = PurchaseOrder::with('supplier')
        ->whereNotIn('status', ['paid', 'received', 'Received', 'cancelled', 'canceled']);

    if ($from) $query->whereDate('tanggal', '>=', $from);
    if ($to)   $query->whereDate('tanggal', '<=', $to);

    // Filter Pencarian (No PO atau Nama Supplier)
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('po_number', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn($s) => $s->where('nama_supplier', 'like', "%{$search}%"));
        });
    }

    // Filter Status Spesifik (jika dipilih)
    if ($status) {
        $query->where('status', $status);
    }

    return $query;
}

    /* ===============================
       JURNAL
    =============================== */
    public function journal(Request $request)
    {
        $from     = $request->get('from', date('Y-m-01'));
        $to       = $request->get('to', date('Y-m-d'));
        $search   = $request->get('search');
        $accounts = Account::orderBy('code', 'asc')->get();

        $query = Transaction::with(['user', 'member', 'account'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $totalDebit  = (clone $query)->sum('total');
        $totalKredit = $totalDebit;
        $data        = $query->latest()->paginate(15)->withQueryString();

        return view('reports.journal', compact('data', 'from', 'to', 'totalDebit', 'totalKredit', 'accounts'));
    }

    /* ===============================
        SIMPAN TRANSAKSI MANUAL
    =============================== */
    public function store(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:income,expense',
            'account_id'  => 'required|exists:accounts,id',
            'total'       => 'required|numeric|min:0',
            'date'        => 'required|date',
            'description' => 'nullable|string',
        ]);

        $transaction = new Transaction();
        $transaction->trx_number     = 'TRX-MANUAL-' . strtoupper(uniqid());
        $transaction->user_id        = Auth::id();
        $transaction->account_id     = $request->account_id;
        $transaction->total          = $request->total;
        $transaction->paid           = $request->total;
        $transaction->accepted       = $request->total;
        $transaction->change         = 0;
        $transaction->description    = $request->description;
        $transaction->type           = $request->type;
        $transaction->status         = 'paid';
        $transaction->payment_method = 'cash';
        $transaction->created_at     = $request->date . ' ' . date('H:i:s');
        $transaction->updated_at     = now();
        $transaction->save();

        return redirect()->back()->with('success', 'Transaksi berhasil disimpan ke Jurnal!');
    }

    /* ===============================
        EXPORT JURNAL (CSV) - REVISED
    =============================== */
public function journalExport(Request $request)
{
    $from     = $request->get('from', date('Y-m-01'));
    $to       = $request->get('to', date('Y-m-d'));
    $search   = $request->get('search'); // Ambil input pencarian
    $fileName = "Jurnal_Umum_" . date('Ymd_His') . ".csv";

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    // PERBAIKAN: Tambahkan $search ke dalam 'use' agar bisa diakses di dalam closure
    $callback = function () use ($from, $to, $search) {
        $file = fopen('php://output', 'w');
        
        // BOM untuk UTF-8 Excel agar karakter tidak berantakan
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header Laporan
        fputcsv($file, ['LAPORAN JURNAL UMUM'], ';');
        fputcsv($file, ['Periode:', $from . ' s/d ' . $to], ';');
        if ($search) {
            fputcsv($file, ['Pencarian:', $search], ';');
        }
        fputcsv($file, ['Waktu Cetak:', date('d/m/Y H:i:s')], ';');
        fputcsv($file, [], ';'); 

        // Header Kolom
        fputcsv($file, ['TANGGAL', 'NO. TRX', 'AKUN & KETERANGAN', 'REF', 'DEBIT', 'KREDIT'], ';');

        // PERBAIKAN: Terapkan query filter yang sama dengan fungsi journal()
        $query = Transaction::with(['account', 'member', 'user'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  // Opsional: Jika ingin mencari berdasarkan nama akun juga
                  ->orWhereHas('account', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('created_at', 'asc')->get();

        $totalDebitAll = 0;
        $totalKreditAll = 0;

        foreach ($transactions as $row) {
            $isIncome = $row->type == 'income' || $row->type == null;
            
            // Nama & Kode Akun
            $akunNama = $row->account->name ?? ($isIncome ? 'Pendapatan Penjualan' : 'Beban/Lainnya');
            $akunKode = $row->account->code ?? ($isIncome ? '4100' : '5100');
            
            // Logika Keterangan (Agar sama dengan gambar 2 yang Anda inginkan)
            $memberInfo = $row->member->name ?? 'Pelanggan Umum';
            $desc = $row->description ? " (" . $row->description . ")" : " (Penjualan ke $memberInfo)";

            if ($isIncome) {
                // Baris 1: Kas (Debit)
                fputcsv($file, [$row->created_at->format('d/m/Y'), $row->trx_number, 'Kas dan Bank', '1100', $row->total, 0], ';');
                // Baris 2: Akun Lawan (Kredit)
                fputcsv($file, ['', '', '  ' . $akunNama . $desc, $akunKode, 0, $row->total], ';');
            } else {
                // Baris 1: Akun Lawan (Debit)
                fputcsv($file, [$row->created_at->format('d/m/Y'), $row->trx_number, $akunNama . $desc, $akunKode, $row->total, 0], ';');
                // Baris 2: Kas (Kredit)
                fputcsv($file, ['', '', '  Kas dan Bank', '1100', 0, $row->total], ';');
            }
            
            $totalDebitAll += $row->total;
            $totalKreditAll += $row->total;
        }

        fputcsv($file, [], ';');
        fputcsv($file, ['', '', 'TOTAL AKHIR', '', $totalDebitAll, $totalKreditAll], ';');

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

/* ===============================
   HELPER: Harga beli terakhir
=============================== */
private function getHargaBeli(): array
{
    // ✅ Ambil harga beli + ongkir per unit dari PO terakhir
    $latestItemIds = DB::table('purchase_order_items as poi')
        ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
        ->whereIn('po.status', ['received', 'approved', 'Received', 'Approved'])
        ->select(DB::raw('MAX(poi.id) as max_id'))
        ->groupBy('poi.product_unit_id')
        ->pluck('max_id');

    $items = DB::table('purchase_order_items')
        ->whereIn('id', $latestItemIds)
        ->select('product_unit_id', 'price', 'ongkir', 'qty')
        ->get();

    $result = [];
    foreach ($items as $item) {
        // ✅ HPP per unit = harga beli + (ongkir ÷ qty)
        $ongkirPerUnit = ($item->qty > 0) ? ((float)($item->ongkir ?? 0) / (float)$item->qty) : 0;
        $result[$item->product_unit_id] = (float)$item->price + $ongkirPerUnit;
    }

    return $result;
}

/* ===============================
   LAPORAN LABA / RUGI
=============================== */
public function labaRugi(Request $request)
{
    $from   = $request->input('from', now()->startOfMonth()->toDateString());
    $to     = $request->input('to', now()->toDateString());
    $search = $request->input('search');

    $transactions = Transaction::whereIn('status', ['paid', 'kredit'])
        ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
        ->with(['items.unit.product'])
        ->get();

    // ✅ Ambil harga beli dari PO
    $hargaBeli       = $this->getHargaBeli();
    $totalPenjualan  = 0;
    $totalModal      = 0;
    $penjualanProduk = [];
    $bulanData       = [];

    foreach ($transactions as $trx) {
        $bulan = $trx->created_at->format('Y-m');
        if (!isset($bulanData[$bulan])) {
            $bulanData[$bulan] = ['penjualan' => 0, 'modal' => 0, 'laba' => 0];
        }

        foreach ($trx->items as $item) {
            $namaProduk = $item->unit->product->name ?? 'Produk Tidak Diketahui';

            if ($search && !str_contains(strtolower($namaProduk), strtolower($search))) {
                continue;
            }

            $hargaJual = ($item->price - ($item->discount ?? 0)) * $item->qty;

            // ✅ FIX: fallback ke price item sendiri jika tidak ada di PO
            $hargaBeliSatuan = $hargaBeli[$item->product_unit_id] ?? $item->price ?? 0;
            $hpp             = $hargaBeliSatuan * $item->qty;

            $totalPenjualan += $hargaJual;
            $totalModal     += $hpp;

            $bulanData[$bulan]['penjualan'] += $hargaJual;
            $bulanData[$bulan]['modal']     += $hpp;

            if (!isset($penjualanProduk[$namaProduk])) {
                $penjualanProduk[$namaProduk] = [
                    'name'  => $namaProduk,
                    'qty'   => 0,
                    'omzet' => 0,
                    'hpp'   => 0,
                    'laba'  => 0,
                ];
            }
            $penjualanProduk[$namaProduk]['qty']   += $item->qty;
            $penjualanProduk[$namaProduk]['omzet'] += $hargaJual;
            $penjualanProduk[$namaProduk]['hpp']   += $hpp;
            $penjualanProduk[$namaProduk]['laba']  += ($hargaJual - $hpp);
        }
    }

    foreach ($bulanData as $b => $v) {
        $bulanData[$b]['laba'] = $v['penjualan'] - $v['modal'];
    }
    ksort($bulanData);

    $labaKotor = $totalPenjualan - $totalModal;

    // ✅ FIX: Wrap expenses dalam try-catch jika tabel belum ada
    try {
        $totalBiayaOperasional = DB::table('expenses')
            ->whereBetween('date', [$from, $to])
            ->sum('amount') ?? 0;
    } catch (\Exception $e) {
        $totalBiayaOperasional = 0;
    }

    $labaBersih = $labaKotor - $totalBiayaOperasional;

    usort($penjualanProduk, fn($a, $b) => $b['laba'] <=> $a['laba']);

    $marginPersen    = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0;
    $jumlahTrx       = $transactions->count();
    $jumlahTrxPaid   = $transactions->where('status', 'paid')->count();
    $jumlahTrxKredit = $transactions->where('status', 'kredit')->count();

    // ✅ FIX: Coba relasi payments, fallback ke accepted jika tidak ada
    try {
        $totalPiutang = Transaction::where('status', 'kredit')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->with('payments')
            ->get()
            ->sum(fn($t) => max($t->total - $t->payments->sum('amount'), 0));
    } catch (\Exception $e) {
        $totalPiutang = Transaction::where('status', 'kredit')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->get()
            ->sum(fn($t) => max($t->total - ($t->accepted ?? 0), 0));
    }

    return view('reports.laba_rugi', compact(
        'from', 'to', 'search',
        'totalPenjualan', 'totalModal', 'labaKotor',
        'totalBiayaOperasional', 'labaBersih', 'marginPersen',
        'penjualanProduk', 'bulanData', 'jumlahTrx',
        'jumlahTrxPaid', 'jumlahTrxKredit', 'totalPiutang'
    ));
}

    /* ===============================
       LAPORAN NERACA
       ← Ganti semua total_amount → total
    =============================== */
    public function neraca(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());

        $totalPengeluaran = DB::table('expenses')->whereDate('date', '<=', $tanggal)->sum('amount') ?? 0;

        // 1. KAS
        $kas = Transaction::where('status', 'paid')->whereDate('created_at', '<=', $tanggal)->sum('paid')
            + KreditPayment::whereDate('paid_at', '<=', $tanggal)->sum('amount')
            - PurchaseOrder::where('status', 'paid')->whereDate('tanggal', '<=', $tanggal)->sum('total') // ← total
            - $totalPengeluaran;

        // 2. PIUTANG
        $kreditTrx = Transaction::where('status', 'kredit')->whereDate('created_at', '<=', $tanggal)->with('payments')->get();
        $piutang   = $kreditTrx->sum(fn($t) => max($t->total - $t->payments->sum('amount'), 0));

        // 3. STOK
        $hargaBeliArr = $this->getHargaBeli();
        $stocks       = Stock::with('unit.product')->get();
        $nilaiStok    = 0;
        $detailStok   = [];

        foreach ($stocks as $stock) {
            if (($stock->qty ?? 0) <= 0) continue;
            $beli   = $hargaBeliArr[$stock->product_unit_id] ?? ($stock->unit->cost ?? 0);
            $nilai  = $stock->qty * $beli;
            $nilaiStok += $nilai;
            $nama = $stock->unit->product->name ?? '-';
            if (!isset($detailStok[$nama])) $detailStok[$nama] = ['qty' => 0, 'nilai' => 0];
            $detailStok[$nama]['qty']   += $stock->qty;
            $detailStok[$nama]['nilai'] += $nilai;
        }

        $totalAsetLancar = max($kas, 0) + $piutang + $nilaiStok;
        $asetTetap       = 0;
        $totalAset       = $totalAsetLancar + $asetTetap;

        // 4. KEWAJIBAN — pakai total (bukan total_amount)
        $hutangSupplier = PurchaseOrder::whereNotIn('status', ['paid', 'received', 'Received', 'cancelled', 'canceled'])
            ->whereDate('tanggal', '<=', $tanggal)
            ->sum('total'); // ← total

        $totalKewajiban = $hutangSupplier;

        // 5. MODAL
        $allTrx    = Transaction::whereIn('status', ['paid', 'kredit'])->whereDate('created_at', '<=', $tanggal)->with('items')->get();
        $omzetTotal = 0;
        $hppTotal = 0;
        foreach ($allTrx as $trx) {
            foreach ($trx->items as $item) {
                $omzetTotal += ($item->price - ($item->discount ?? 0)) * $item->qty;
                $hppTotal   += ($hargaBeliArr[$item->product_unit_id] ?? ($item->unit->cost ?? 0)) * $item->qty;
            }
        }

        $labaDitahan = ($omzetTotal - $hppTotal) - $totalPengeluaran;
        $modal       = $totalAset - $totalKewajiban;

        $ringkasan = [
            'aset' => [
                'lancar' => ['kas' => max($kas, 0), 'piutang' => $piutang, 'stok' => $nilaiStok, 'total' => $totalAsetLancar],
                'tetap'  => $asetTetap,
                'total'  => $totalAset,
            ],
            'kewajiban' => ['hutang_supplier' => $hutangSupplier, 'total' => $totalKewajiban],
            'modal'     => ['laba_ditahan' => $labaDitahan, 'total' => $modal],
        ];

        return view('reports.neraca', compact('tanggal', 'ringkasan', 'detailStok', 'kreditTrx', 'totalAset', 'totalKewajiban', 'modal'));
    }

    /* ===============================
       HELPER: getLabaRugiData (untuk export)
    =============================== */
    private function getLabaRugiData($from, $to)
    {
        $transactions    = Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->with(['items.unit.product'])->get();
        $hargaBeliArr    = $this->getHargaBeli();
        $totalPenjualan  = 0;
        $totalModal = 0;
        $penjualanProduk = [];
        $bulanData = [];

        foreach ($transactions as $trx) {
            $bulan = $trx->created_at->format('Y-m');
            if (!isset($bulanData[$bulan])) $bulanData[$bulan] = ['penjualan' => 0, 'modal' => 0, 'laba' => 0];
            foreach ($trx->items as $item) {
                $omzet = ($item->price - ($item->discount ?? 0)) * $item->qty;
                $beli  = $hargaBeliArr[$item->product_unit_id] ?? ($item->unit->cost ?? 0);
                $hpp   = $beli * $item->qty;
                $totalPenjualan += $omzet;
                $totalModal += $hpp;
                $pName = $item->unit->product->name ?? 'Produk Terhapus';
                if (!isset($penjualanProduk[$pName])) $penjualanProduk[$pName] = ['name' => $pName, 'qty' => 0, 'omzet' => 0, 'hpp' => 0, 'laba' => 0];
                $penjualanProduk[$pName]['qty'] += $item->qty;
                $penjualanProduk[$pName]['omzet'] += $omzet;
                $penjualanProduk[$pName]['hpp'] += $hpp;
                $penjualanProduk[$pName]['laba'] += $omzet - $hpp;
                $bulanData[$bulan]['penjualan'] += $omzet;
                $bulanData[$bulan]['modal'] += $hpp;
                $bulanData[$bulan]['laba'] += $omzet - $hpp;
            }
        }

        $totalBiayaOperasional = DB::table('expenses')->whereBetween('date', [$from, $to])->sum('amount') ?? 0;
        $labaKotor  = $totalPenjualan - $totalModal;
        $labaBersih = $labaKotor - $totalBiayaOperasional;

        return [
            'from' => $from,
            'to' => $to,
            'totalPenjualan' => $totalPenjualan,
            'totalModal' => $totalModal,
            'totalBiayaOperasional' => $totalBiayaOperasional,
            'labaKotor' => $labaKotor,
            'labaBersih' => $labaBersih,
            'marginPersen' => $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0,
            'penjualanProduk' => collect($penjualanProduk)->sortByDesc('laba')->values()->all(),
            'bulanData' => $bulanData,
            'jumlahTrx' => $transactions->count(),
        ];
    }

    public function labaRugiExport(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());
        $data = $this->getLabaRugiData($from, $to);

        $fileName = "Laporan_Laba_Rugi_{$from}_ke_{$to}.csv";
        $headers  = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $callback = function () use ($data, $from, $to) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['LAPORAN LABA / RUGI']);
            fputcsv($file, ['Periode', $from . ' s/d ' . $to]);
            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Penjualan', $data['totalPenjualan']]);
            fputcsv($file, ['Total HPP (Modal)', $data['totalModal']]);
            fputcsv($file, ['Laba Kotor', $data['labaKotor']]);
            fputcsv($file, ['Biaya Operasional', $data['totalBiayaOperasional']]);
            fputcsv($file, ['Laba Bersih', $data['labaBersih']]);
            fputcsv($file, []);
            fputcsv($file, ['BREAKDOWN PRODUK']);
            fputcsv($file, ['No', 'Nama Produk', 'Qty', 'Omzet', 'HPP', 'Laba', 'Margin %']);
            foreach ($data['penjualanProduk'] as $i => $p) {
                $margin = $p['omzet'] > 0 ? round(($p['laba'] / $p['omzet']) * 100, 1) : 0;
                fputcsv($file, [$i + 1, $p['name'], $p['qty'], $p['omzet'], $p['hpp'], $p['laba'], $margin . '%']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function neracaExportCsv(Request $request)
    {
        $tanggal  = $request->input('tanggal', now()->toDateString());
        $fileName = "Neraca-{$tanggal}.csv";
        $headers  = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        // Ambil data neraca menggunakan logika di atas
        $request->merge(['tanggal' => $tanggal]);
        // Re-run neraca logic inline untuk export
        $totalPengeluaran = DB::table('expenses')->whereDate('date', '<=', $tanggal)->sum('amount') ?? 0;
        $kas = Transaction::where('status', 'paid')->whereDate('created_at', '<=', $tanggal)->sum('paid')
            + KreditPayment::whereDate('paid_at', '<=', $tanggal)->sum('amount')
            - PurchaseOrder::where('status', 'paid')->whereDate('tanggal', '<=', $tanggal)->sum('total')
            - $totalPengeluaran;
        $kreditTrx = Transaction::where('status', 'kredit')->whereDate('created_at', '<=', $tanggal)->with('payments')->get();
        $piutang   = $kreditTrx->sum(fn($t) => max($t->total - $t->payments->sum('amount'), 0));
        $hargaBeliArr = $this->getHargaBeli();
        $nilaiStok = 0;
        foreach (Stock::with('unit')->get() as $s) {
            if (($s->qty ?? 0) <= 0) continue;
            $nilaiStok += $s->qty * ($hargaBeliArr[$s->product_unit_id] ?? ($s->unit->cost ?? 0));
        }
        $totalAset      = max($kas, 0) + $piutang + $nilaiStok;
        $totalKewajiban = PurchaseOrder::whereNotIn('status', ['paid', 'received', 'Received', 'cancelled', 'canceled'])->whereDate('tanggal', '<=', $tanggal)->sum('total');
        $modal          = $totalAset - $totalKewajiban;

        $callback = function () use ($kas, $piutang, $nilaiStok, $totalAset, $totalKewajiban, $modal, $tanggal) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['LAPORAN NERACA']);
            fputcsv($file, ['Per Tanggal', $tanggal]);
            fputcsv($file, []);
            fputcsv($file, ['ASET']);
            fputcsv($file, ['Kas', max($kas, 0)]);
            fputcsv($file, ['Piutang Dagang', $piutang]);
            fputcsv($file, ['Persediaan Barang', $nilaiStok]);
            fputcsv($file, ['TOTAL ASET', $totalAset]);
            fputcsv($file, []);
            fputcsv($file, ['KEWAJIBAN']);
            fputcsv($file, ['Hutang Supplier', $totalKewajiban]);
            fputcsv($file, ['TOTAL KEWAJIBAN', $totalKewajiban]);
            fputcsv($file, []);
            fputcsv($file, ['MODAL']);
            fputcsv($file, ['Modal Bersih', $modal]);
            fputcsv($file, ['TOTAL MODAL', $modal]);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function stockReport(Request $request)
    {
        $suppliers = Supplier::orderBy('nama_supplier', 'asc')->get();
        $query     = StockMutation::with(['unit.product.supplier', 'user']);

        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('created_at', '<=', $request->to);
        if ($request->filled('search')) {
            $query->whereHas(
                'unit.product',
                fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%')
            );
        }
        if ($request->filled('supplier_id')) {
            $query->whereHas('unit.product', fn($q) => $q->where('supplier_id', $request->supplier_id));
        }
        if ($request->filled('status')) $query->where('status', $request->status);

        $data = $query->latest()->paginate(15);
        return view('reports.stock', compact('data', 'suppliers'));
    }
}
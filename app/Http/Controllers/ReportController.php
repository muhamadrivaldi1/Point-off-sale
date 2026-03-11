<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\KreditPayment;
use App\Models\Sale;

use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /* ===============================
       LAPORAN PENJUALAN
    =============================== */
    public function sales(Request $r)
    {
        $from = $r->input('from', now()->startOfMonth()->toDateString());
        $to   = $r->input('to', now()->toDateString());

        $data = Transaction::with(['user', 'member'])
            ->whereIn('status', ['paid', 'kredit'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalOmzet = Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('total');

        return view('reports.sales', compact('data', 'from', 'to', 'totalOmzet'));
    }

    /* ===============================
       EXPORT PENJUALAN KE CSV
    =============================== */
    public function salesCsv(Request $r)
    {
        $from = $r->input('from', now()->startOfMonth()->toDateString());
        $to   = $r->input('to', now()->toDateString());

        $transactions = Transaction::with(['user', 'member', 'items.unit.product'])
            ->whereIn('status', ['paid', 'kredit'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'Laporan_Penjualan_' . now()->format('d-M-Y_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($transactions, $from, $to) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['LAPORAN PENJUALAN'], ';');
            fputcsv($file, ['Periode', date('d/m/Y', strtotime($from)) . ' s/d ' . date('d/m/Y', strtotime($to))], ';');
            fputcsv($file, ['Dicetak', date('d/m/Y H:i:s')], ';');
            fputcsv($file, ['Total Transaksi', $transactions->count() . ' transaksi'], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['No', 'Tanggal', 'Jam', 'Invoice', 'Kasir', 'Member', 'Subtotal', 'Diskon', 'Total', 'Bayar', 'Kembalian', 'Status', 'Jumlah Item'], ';');

            $no = 1;
            $grandTotal = 0;
            $totalDiskon = 0;
            $totalBayar = 0;

            foreach ($transactions as $trx) {
                $itemCount = $trx->items->sum('qty');
                $subtotal  = $trx->items->sum(fn($i) => ($i->price - ($i->discount ?? 0)) * $i->qty);
                $tanggal   = $trx->created_at ? $trx->created_at->format('d/m/Y') : '-';
                $jam       = $trx->created_at ? $trx->created_at->format('H:i:s') : '-';

                fputcsv($file, [
                    $no++,
                    $tanggal,
                    $jam,
                    $trx->trx_number ?? '-',
                    $trx->user->name ?? '-',
                    $trx->member->name ?? 'Non-Member',
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
                $totalBayar  += $trx->paid;
            }

            fputcsv($file, [], ';');
            fputcsv($file, ['', '', '', '', '', 'TOTAL', '', $totalDiskon, $grandTotal, $totalBayar, '', '', ''], ';');

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
            ->findOrFail($id);

        return view('reports.sales_detail', compact('trx'));
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
            $query->whereHas('unit.product', fn($q) => $q->where('name', 'like', '%' . $request->q . '%'));
        }

        $stocks   = $query->orderBy('id', 'desc')->get();
        $filename = 'Laporan_Stok_' . now()->format('d-M-Y_His') . '.csv';
        $headers  = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];

        $callback = function () use ($stocks) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['LAPORAN STOK BARANG'], ';');
            fputcsv($file, ['Dicetak', date('d/m/Y H:i:s')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['No', 'Nama Produk', 'Barcode', 'Satuan', 'Lokasi', 'Stok', 'Harga Jual', 'Harga Beli', 'Nilai Stok (HPP)'], ';');

            $no = 1;
            $totalNilai = 0;

            foreach ($stocks as $stock) {
                $hpp       = $stock->unit->cost ?? 0;
                $nilaiStok = ($stock->qty ?? 0) * $hpp;
                $totalNilai += $nilaiStok;

                fputcsv($file, [
                    $no++,
                    $stock->unit->product->name ?? '-',
                    $stock->unit->barcode ?? '-',
                    $stock->unit->unit_name ?? '-',
                    strtoupper($stock->location ?? '-'),
                    $stock->qty ?? 0,
                    $stock->unit->price ?? 0,
                    $hpp,
                    $nilaiStok,
                ], ';');
            }

            fputcsv($file, [], ';');
            fputcsv($file, ['', '', '', '', '', 'TOTAL NILAI STOK (HPP)', '', '', $totalNilai], ';');
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /* ===============================
       LAPORAN PENERIMAAN
    =============================== */
    public function penerimaan(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $query = PurchaseOrder::with('supplier')
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->supplier_id && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        $data = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

        return view('reports.penerimaan', compact('from', 'to', 'data'));
    }

    public function penerimaanExport(Request $request)
    {
        $dataQuery = $this->getPenerimaanData($request);
        $fileName = "Laporan_Penerimaan_Barang_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($dataQuery) {
            $file = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM untuk Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header CSV
            fputcsv($file, ['No. PO', 'Tanggal', 'Supplier', 'Jenis Pembayaran', 'Total', 'Status']);

            $dataQuery->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->po_number,
                        \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y'),
                        $row->supplier->nama_supplier ?? '-',
                        $row->jenis_pembayaran,
                        $row->total,
                        strtoupper($row->status)
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // FUNGSI HELPER AGAR LOGIKA FILTER SAMA
    private function getPenerimaanData(Request $request)
    {
        $query = PurchaseOrder::with('supplier')->orderBy('tanggal', 'desc');

        // Filter Tanggal
        $from = $request->input('from', date('Y-m-01'));
        $to = $request->input('to', date('Y-m-d'));
        $query->whereBetween('tanggal', [$from, $to]);

        // Filter Supplier
        if ($request->filled('supplier_id') && $request->supplier_id != 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter Status
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /* ===============================
       LAPORAN MUTASI STOK
    =============================== */
    public function stock(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());
        $type = $request->input('type', '');

        $query = StockMutation::with('unit.product');

        if ($type === 'in' || $type === 'out') {
            $query->where('type', $type);
        }

        $query->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc');

        $data = $query->paginate(20)->withQueryString();

        return view('reports.stock', compact('data', 'from', 'to', 'type'));
    }

    /* ===============================
       LAPORAN PIUTANG (HUTANG BERJALAN)
    =============================== */
    public function piutang(Request $request)
    {
        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to', now()->toDateString());
        $status = $request->input('status');
        $search = $request->input('search');

        // Gunakan helper query agar logika filter terpusat
        $query = $this->getPiutangQuery($request);

        // Hitung Total Sisa untuk Badge Header menggunakan clone query
        $totalSisaPiutang = (clone $query)->get()->sum(function ($item) {
            return $item->total - ($item->total_terbayar ?? 0);
        });

        $data = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.piutang', compact('data', 'from', 'to', 'totalSisaPiutang', 'status', 'search'));
    }

    /**
     * Export Laporan ke CSV
     */
    public function piutangExport(Request $request)
    {
        $query = $this->getPiutangQuery($request);
        $fileName = "Laporan_Piutang_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM untuk Excel

            // Header Kolom
            fputcsv($file, ['Tanggal', 'No. Invoice', 'Pelanggan', 'Total TRX', 'Dibayar', 'Sisa Hutang', 'Status']);

            // Chunk data untuk performa memori
            $query->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    $dibayar = $row->total_terbayar ?? 0;
                    $sisa = $row->total - $dibayar;

                    $statusLabel = 'BELUM BAYAR';
                    if ($dibayar >= $row->total && $row->total > 0) $statusLabel = 'LUNAS';
                    elseif ($dibayar > 0) $statusLabel = 'CICILAN';

                    fputcsv($file, [
                        $row->created_at->format('d/m/Y'),
                        $row->trx_number,
                        $row->member->name ?? 'Pelanggan Umum',
                        $row->total,
                        $dibayar,
                        $sisa,
                        $statusLabel
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper Query Logic (Agar View & Export Selalu Sinkron)
     */
    private function getPiutangQuery(Request $request)
    {
        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to', now()->toDateString());
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Transaction::with(['member'])
            ->withSum('cicilan as total_terbayar', 'amount')
            ->where('status', 'kredit')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        // Filter Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx_number', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($m) use ($search) {
                        $m->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Status Pembayaran
        if ($status === 'belum_bayar') {
            $query->has('cicilan', '=', 0);
        } elseif ($status === 'cicilan') {
            $query->whereHas('cicilan')
                ->where(function ($q) {
                    $q->whereRaw('(total - (select ifnull(sum(amount),0) from kredit_payments where transaction_id = transactions.id)) > 0');
                });
        } elseif ($status === 'lunas') {
            $query->whereRaw('(total - (select ifnull(sum(amount),0) from kredit_payments where transaction_id = transactions.id)) <= 0');
        }

        return $query;
    }

    /* ===============================
       LAPORAN HUTANG
    =============================== */
    public function hutang(Request $request)
    {
        // Inisialisasi Tanggal Default
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        // Gunakan helper query
        $query = $this->getHutangQuery($request);

        // Hitung Total Sisa Hutang berdasarkan filter yang sedang aktif
        // Sisa hutang adalah total PO yang statusnya belum 'Received' atau 'Paid'
        $totalHutang = (clone $query)->whereNotIn('status', ['Received', 'paid', 'received'])->sum('total');

        $data = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        return view('reports.hutang', compact('data', 'from', 'to', 'totalHutang'));
    }

    public function hutangExport(Request $request)
    {
        $query = $this->getHutangQuery($request);
        $fileName = "Laporan_Hutang_Supplier_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM untuk Excel

            fputcsv($file, ['Tanggal', 'No. PO', 'Supplier', 'Total TRX', 'Dibayar', 'Sisa Hutang', 'Status']);

            $query->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    $isPaid = in_array(strtolower($row->status), ['received', 'paid']);
                    $dibayar = $isPaid ? $row->total : 0;
                    $sisa = $isPaid ? 0 : $row->total;

                    fputcsv($file, [
                        $row->tanggal ? $row->tanggal->format('d/m/Y') : '-',
                        $row->po_number,
                        $row->supplier->nama_supplier ?? 'Supplier Umum',
                        $row->total,
                        $dibayar,
                        $sisa,
                        strtoupper($row->status ?? 'DRAFT')
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper untuk menyatukan logika filter Hutang
     */
    private function getHutangQuery(Request $request)
    {
        $from   = $request->get('from', now()->startOfMonth()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $search = $request->get('search');
        $status = $request->get('status');

        // Tambahkan whereNotIn agar LUNAS (Received/Paid) tidak ikut tampil secara default
        $query = PurchaseOrder::with('supplier')
            ->whereNotIn('status', ['Received', 'paid', 'received']);

        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($s) use ($search) {
                        $s->where('nama_supplier', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status tambahan (jika user memilih filter Draft/Pending di UI)
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
        $from = $request->get('from', date('Y-m-01'));
        $to = $request->get('to', date('Y-m-d'));
        $search = $request->get('search');

        // Gunakan query builder agar bisa di-clone
        $query = Transaction::with(['user', 'member'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($search) {
            $query->where('trx_number', 'like', "%{$search}%");
        }

        // 1. Hitung TOTAL keseluruhan (untuk Footer) sebelum dipaginate
        $totalDebit = (clone $query)->sum('total');
        $totalKredit = $totalDebit;

        // 2. Ambil data dengan PAGINATION
        $data = $query->latest()->paginate(15)->withQueryString();

        return view('reports.journal', compact('data', 'from', 'to', 'totalDebit', 'totalKredit'));
    }

    public function journalExport(Request $request)
    {
        $from = $request->get('from', date('Y-m-01'));
        $to = $request->get('to', date('Y-m-d'));

        $fileName = "Jurnal_Umum_{$from}_to_{$to}.csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $callback = function () use ($request, $from, $to) {
            $file = fopen('php://output', 'w');
            // BOM untuk Excel agar support karakter khusus
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, ['TANGGAL', 'NO. TRX', 'AKUN & KETERANGAN', 'REF', 'DEBIT', 'KREDIT']);

            $transactions = Transaction::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->get();

            foreach ($transactions as $row) {
                // Baris 1: Kas (Debit)
                fputcsv($file, [$row->created_at->format('d/m/Y'), $row->trx_number, 'Kas dan Bank', '1100', $row->total, 0]);
                // Baris 2: Pendapatan (Kredit)
                fputcsv($file, ['', '', 'Pendapatan Penjualan', '4100', 0, $row->total]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ===============================
       HELPER: Harga beli terakhir per unit
       Ambil dari purchase_order_items terakhir (PO yang sudah received)
    =============================== */
    private function getLabaRugiData($from, $to)
    {
        // Ambil semua transaksi sukses di periode tersebut
        $transactions = \App\Models\Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->with(['items.unit.product', 'payments'])
            ->get();

        $hargaBeliArr = $this->getHargaBeli(); // Asumsi fungsi getHargaBeli sudah ada di controller ini

        $totalPenjualan = 0;
        $totalModal = 0;
        $penjualanProduk = [];
        $bulanData = [];

        foreach ($transactions as $trx) {
            $bulan = $trx->created_at->format('Y-m');
            if (!isset($bulanData[$bulan])) {
                $bulanData[$bulan] = ['penjualan' => 0, 'modal' => 0, 'laba' => 0];
            }

            foreach ($trx->items as $item) {
                $omzet = ($item->price - ($item->discount ?? 0)) * $item->qty;
                $beli  = $hargaBeliArr[$item->product_unit_id] ?? ($item->unit->cost ?? 0);
                $hpp   = $beli * $item->qty;
                $laba  = $omzet - $hpp;

                $totalPenjualan += $omzet;
                $totalModal += $hpp;

                // Grouping per produk untuk breakdown
                $pName = $item->unit->product->name ?? 'Produk Terhapus';
                if (!isset($penjualanProduk[$pName])) {
                    $penjualanProduk[$pName] = ['name' => $pName, 'qty' => 0, 'omzet' => 0, 'hpp' => 0, 'laba' => 0];
                }
                $penjualanProduk[$pName]['qty'] += $item->qty;
                $penjualanProduk[$pName]['omzet'] += $omzet;
                $penjualanProduk[$pName]['hpp'] += $hpp;
                $penjualanProduk[$pName]['laba'] += $laba;

                // Grouping tren bulanan
                $bulanData[$bulan]['penjualan'] += $omzet;
                $bulanData[$bulan]['modal'] += $hpp;
                $bulanData[$bulan]['laba'] += $laba;
            }
        }

        $totalBiayaOperasional = \Illuminate\Support\Facades\DB::table('expenses')
            ->whereBetween('date', [$from, $to])
            ->sum('amount') ?? 0;

        $labaKotor = $totalPenjualan - $totalModal;
        $labaBersih = $labaKotor - $totalBiayaOperasional;
        $marginPersen = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0;

        return [
            'from' => $from,
            'to' => $to,
            'totalPenjualan' => $totalPenjualan,
            'totalModal' => $totalModal,
            'totalBiayaOperasional' => $totalBiayaOperasional,
            'labaKotor' => $labaKotor,
            'labaBersih' => $labaBersih,
            'marginPersen' => $marginPersen,
            'penjualanProduk' => collect($penjualanProduk)->sortByDesc('laba')->values()->all(),
            'bulanData' => $bulanData,
            'jumlahTrx' => $transactions->count(),
        ];
    }

    private function getHargaBeli(): array
    {
        // 1. Ambil ID item PO terakhir untuk setiap produk agar harga yang diambil adalah yang paling baru
        $latestItemIds = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
            // Gunakan LOWER untuk menghindari masalah huruf besar/kecil (Received vs received)
            ->whereIn(DB::raw('LOWER(po.status)'), ['received', 'approved', 'paid'])
            ->select(DB::raw('MAX(poi.id) as max_id'))
            ->groupBy('poi.product_unit_id')
            ->pluck('max_id');

        // 2. Ambil harga berdasarkan ID-ID tersebut
        return DB::table('purchase_order_items')
            ->whereIn('id', $latestItemIds)
            ->pluck('price', 'product_unit_id') // Langsung jadi array [product_unit_id => price]
            ->toArray();
    }

    // Tambahkan ini di dalam ReportController.php
    private function getNeracaData($tanggal)
    {
        // 0. Ambil Total Pengeluaran (Operasional)
        $totalPengeluaran = DB::table('expenses')
            ->whereDate('date', '<=', $tanggal)
            ->sum('amount') ?? 0;

        // 1. KAS
        $kas = \App\Models\Transaction::where('status', 'paid')
            ->whereDate('created_at', '<=', $tanggal)
            ->sum('paid')
            +
            \App\Models\KreditPayment::whereDate('paid_at', '<=', $tanggal)->sum('amount')
            -
            \App\Models\PurchaseOrder::where('status', 'paid')
            ->whereDate('tanggal', '<=', $tanggal)
            ->sum('total_amount')
            -
            $totalPengeluaran;

        // 2. PIUTANG
        $kreditTrx = \App\Models\Transaction::where('status', 'kredit')
            ->whereDate('created_at', '<=', $tanggal)
            ->with('payments')
            ->get();

        $piutang = $kreditTrx->sum(fn($t) => max($t->total - $t->payments->sum('amount'), 0));

        // 3. STOK
        $hargaBeliArr = $this->getHargaBeli();
        $stocks       = \App\Models\Stock::with('unit.product')->get();
        $nilaiStok    = 0;
        $detailStok   = [];

        foreach ($stocks as $stock) {
            if (($stock->qty ?? 0) <= 0) continue;
            $beli = $hargaBeliArr[$stock->product_unit_id] ?? ($stock->unit->cost ?? 0);
            $nilai = $stock->qty * $beli;
            $nilaiStok += $nilai;

            $nama = $stock->unit->product->name ?? '-';
            if (!isset($detailStok[$nama])) {
                $detailStok[$nama] = ['qty' => 0, 'nilai' => 0];
            }
            $detailStok[$nama]['qty']   += $stock->qty;
            $detailStok[$nama]['nilai'] += $nilai;
        }

        $totalAsetLancar = max($kas, 0) + $piutang + $nilaiStok;
        $totalAset = $totalAsetLancar + 0; // Aset Tetap sementara 0

        // 4. KEWAJIBAN
        $totalKewajiban = \App\Models\PurchaseOrder::whereNotIn('status', ['paid', 'cancelled'])
            ->whereDate('tanggal', '<=', $tanggal)
            ->sum('total_amount');

        // 5. LABA & MODAL
        $allTrx = \App\Models\Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereDate('created_at', '<=', $tanggal)
            ->with('items')
            ->get();

        $omzetTotal = 0;
        $hppTotal   = 0;
        foreach ($allTrx as $trx) {
            foreach ($trx->items as $item) {
                $omzetTotal += ($item->price - ($item->discount ?? 0)) * $item->qty;
                $beliItem    = $hargaBeliArr[$item->product_unit_id] ?? ($item->unit->cost ?? 0);
                $hppTotal   += $beliItem * $item->qty;
            }
        }

        $labaDitahan = ($omzetTotal - $hppTotal) - $totalPengeluaran;
        $modal = $totalAset - $totalKewajiban;

        return [
            'tanggal' => $tanggal,
            'totalAset' => $totalAset,
            'totalKewajiban' => $totalKewajiban,
            'modal' => $modal,
            'ringkasan' => [
                'aset' => [
                    'lancar' => [
                        'kas'     => max($kas, 0),
                        'piutang' => $piutang,
                        'stok'    => $nilaiStok,
                        'total'   => $totalAsetLancar,
                    ],
                    'tetap'  => 0,
                    'total'  => $totalAset,
                ],
                'kewajiban' => [
                    'hutang_supplier' => $totalKewajiban,
                    'total'           => $totalKewajiban,
                ],
                'modal' => [
                    'laba_ditahan' => $labaDitahan,
                    'total'        => $modal,
                ],
            ],
            'detailStok' => $detailStok,
            'kreditTrx' => $kreditTrx
        ];
    }

    /* ===============================
       LAPORAN LABA / RUGI
    =============================== */
    public function labaRugi(Request $request)
    {
        // 1. Tentukan Rentang Tanggal
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        // 2. Ambil Transaksi Valid (Paid & Kredit)
        // Eager loading unit.product sangat penting agar tidak lemot
        $transactions = Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->with(['items.unit.product']) // Eager loading
            ->get();

        // 3. Ambil Master Harga Beli
        $hargaBeli = $this->getHargaBeli();

        // 4. Inisialisasi Variabel
        $totalPenjualan = 0;
        $totalModal     = 0;
        $penjualanProduk = [];
        $bulanData       = [];

        // 5. Proses Data Transaksi
        foreach ($transactions as $trx) {
            $bulan = $trx->created_at->format('Y-m');

            if (!isset($bulanData[$bulan])) {
                $bulanData[$bulan] = ['penjualan' => 0, 'modal' => 0, 'laba' => 0];
            }

            foreach ($trx->items as $item) {
                // Hitung Harga Jual Bersih per item
                $hargaJualSatuan = $item->price - ($item->discount ?? 0);
                $totalHargaJual  = $hargaJualSatuan * $item->qty;

                // --- BAGIAN INI YANG DIPERBAIKI (MENGGUNAKAN FALLBACK) ---
                // Ambil Harga Beli/HPP. Jika tidak ada di PO, ambil dari kolom 'cost' di master unit
                $beli = $hargaBeli[$item->product_unit_id] ?? ($item->unit->cost ?? 0);
                $hpp  = $beli * $item->qty;
                // ---------------------------------------------------------

                // Akumulasi Total
                $totalPenjualan += $totalHargaJual;
                $totalModal     += $hpp;

                // Data Grafik Bulanan
                $bulanData[$bulan]['penjualan'] += $totalHargaJual;
                $bulanData[$bulan]['modal']     += $hpp;

                // Breakdown Per Produk
                $namaProduk = $item->unit->product->name ?? 'Produk Tidak Diketahui';

                if (!isset($penjualanProduk[$namaProduk])) {
                    $penjualanProduk[$namaProduk] = [
                        'name'  => $namaProduk,
                        'qty'   => 0,
                        'omzet' => 0,
                        'hpp'   => 0,
                        'laba'  => 0
                    ];
                }

                $penjualanProduk[$namaProduk]['qty']   += $item->qty;
                $penjualanProduk[$namaProduk]['omzet'] += $totalHargaJual;
                $penjualanProduk[$namaProduk]['hpp']   += $hpp;
                $penjualanProduk[$namaProduk]['laba']  += ($totalHargaJual - $hpp);
            }
        }

        // 6. Finalisasi Data Bulanan
        foreach ($bulanData as $b => $v) {
            $bulanData[$b]['laba'] = $v['penjualan'] - $v['modal'];
        }
        ksort($bulanData);

        // 7. Hitung Laba/Rugi
        $labaKotor = $totalPenjualan - $totalModal;

        // Ambil pengeluaran operasional dari tabel yang baru dibuat
        $totalBiayaOperasional = DB::table('expenses')
            // Jika di migrasi pakai 'date', biarkan begini. 
            // Jika pakai 'created_at', ganti 'date' jadi 'created_at'
            ->whereBetween('date', [$from, $to])
            ->sum('amount') ?? 0;

        $labaBersih = $labaKotor - $totalBiayaOperasional;

        // 8. Sorting Produk (Laba Terbesar ke Terkecil)
        usort($penjualanProduk, fn($a, $b) => $b['laba'] <=> $a['laba']);

        // 9. Statistik Tambahan
        $marginPersen   = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0;
        $jumlahTrx      = $transactions->count();
        $jumlahTrxPaid  = $transactions->where('status', 'paid')->count();
        $jumlahTrxKredit = $transactions->where('status', 'kredit')->count();

        // 10. Hitung Piutang (Optimasi Query)
        $totalPiutang = Transaction::where('status', 'kredit')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->with('payments')
            ->get()
            ->sum(function ($t) {
                return max($t->total - $t->payments->sum('amount'), 0);
            });

        // 11. Return View (Pastikan file ada di resources/views/reports/laba_rugi.blade.php)
        return view('reports.laba_rugi', compact(
            'from',
            'to',
            'totalPenjualan',
            'totalModal',
            'labaKotor',
            'totalBiayaOperasional',
            'labaBersih',
            'marginPersen',
            'penjualanProduk',
            'bulanData',
            'jumlahTrx',
            'jumlahTrxPaid',
            'jumlahTrxKredit',
            'totalPiutang'
        ));
    }

    /* ===============================
       LAPORAN NERACA
    =============================== */
    public function neraca(Request $request)
    {
        // Neraca adalah snapshot PER TANGGAL
        $tanggal = $request->input('tanggal', now()->toDateString());

        // 0. Ambil Total Pengeluaran (Operasional) sampai tanggal tersebut
        $totalPengeluaran = DB::table('expenses')
            ->whereDate('date', '<=', $tanggal)
            ->sum('amount') ?? 0;

        // =====================
        // ASET LANCAR
        // =====================

        // 1. Kas — (Masuk - Keluar - Biaya Operasional)
        $kas = Transaction::where('status', 'paid')
            ->whereDate('created_at', '<=', $tanggal)
            ->sum('paid')
            +
            KreditPayment::whereDate('paid_at', '<=', $tanggal)->sum('amount')
            -
            PurchaseOrder::where('status', 'paid')
            ->whereDate('tanggal', '<=', $tanggal)
            ->sum('total_amount')
            -
            $totalPengeluaran; // <--- Pengurangan Biaya Operasional

        // 2. Piutang — Sisa hutang pelanggan
        $kreditTrx = Transaction::where('status', 'kredit')
            ->whereDate('created_at', '<=', $tanggal)
            ->with('payments')
            ->get();

        $piutang = $kreditTrx->sum(fn($t) => max($t->total - $t->payments->sum('amount'), 0));

        // 3. Nilai Stok Barang (Menggunakan Fallback Cost)
        $hargaBeliArr = $this->getHargaBeli();
        $stocks       = Stock::with('unit.product')->get();
        $nilaiStok    = 0;
        $detailStok   = [];

        foreach ($stocks as $stock) {
            if (($stock->qty ?? 0) <= 0) continue;

            // Ambil harga beli dari PO, jika tidak ada ambil dari kolom 'cost' di unit
            $beli = $hargaBeliArr[$stock->product_unit_id] ?? ($stock->unit->cost ?? 0);

            $nilai      = $stock->qty * $beli;
            $nilaiStok += $nilai;

            $nama = $stock->unit->product->name ?? '-';
            if (!isset($detailStok[$nama])) {
                $detailStok[$nama] = ['qty' => 0, 'nilai' => 0];
            }
            $detailStok[$nama]['qty']   += $stock->qty;
            $detailStok[$nama]['nilai'] += $nilai;
        }

        $totalAsetLancar = max($kas, 0) + $piutang + $nilaiStok;
        $asetTetap = 0; // Bisa dikembangkan nanti
        $totalAset = $totalAsetLancar + $asetTetap;

        // =====================
        // KEWAJIBAN
        // =====================

        $hutangSupplier = PurchaseOrder::whereNotIn('status', ['paid', 'cancelled'])
            ->whereDate('tanggal', '<=', $tanggal)
            ->sum('total_amount');

        $totalKewajiban = $hutangSupplier;

        // =====================
        // MODAL
        // =====================

        // Hitung Laba Bersih (Laba Kotor - Biaya Operasional)
        $allTrx = Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereDate('created_at', '<=', $tanggal)
            ->with('items')
            ->get();

        $omzetTotal = 0;
        $hppTotal   = 0;

        foreach ($allTrx as $trx) {
            foreach ($trx->items as $item) {
                $omzetTotal += ($item->price - ($item->discount ?? 0)) * $item->qty;
                // Gunakan harga beli yang sama dengan logika stok
                $beliItem    = $hargaBeliArr[$item->product_unit_id] ?? ($item->unit->cost ?? 0);
                $hppTotal   += $beliItem * $item->qty;
            }
        }

        // Laba Ditahan = Laba Kotor - Biaya Operasional
        $labaDitahan = ($omzetTotal - $hppTotal) - $totalPengeluaran;

        // Dalam persamaan Akuntansi: Modal = Aset - Kewajiban
        $modal = $totalAset - $totalKewajiban;

        // Ringkasan untuk View
        $ringkasan = [
            'aset' => [
                'lancar' => [
                    'kas'     => max($kas, 0),
                    'piutang' => $piutang,
                    'stok'    => $nilaiStok,
                    'total'   => $totalAsetLancar,
                ],
                'tetap'  => $asetTetap,
                'total'  => $totalAset,
            ],
            'kewajiban' => [
                'hutang_supplier' => $hutangSupplier,
                'total'           => $totalKewajiban,
            ],
            'modal' => [
                'laba_ditahan' => $labaDitahan,
                'total'        => $modal,
            ],
        ];

        return view('reports.neraca', compact(
            'tanggal',
            'ringkasan',
            'detailStok',
            'kreditTrx',
            'totalAset',
            'totalKewajiban',
            'modal'
        ));
    }

    public function labaRugiExport(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        // Ambil data (asumsi kamu sudah punya fungsi helper getLabaRugiData atau sejenisnya)
        // Jika belum ada helper, panggil logika hitungan yang ada di fungsi index Anda
        $data = $this->getLabaRugiData($from, $to);

        $fileName = "Laporan_Laba_Rugi_{$from}_ke_{$to}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($data, $from, $to) {
            $file = fopen('php://output', 'w');

            // Header Dokumen
            fputcsv($file, ['LAPORAN LABA / RUGI']);
            fputcsv($file, ['Periode', $from . ' s/d ' . $to]);
            fputcsv($file, []);

            // Ringkasan KPI
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Penjualan', $data['totalPenjualan']]);
            fputcsv($file, ['Total HPP (Modal)', $data['totalModal']]);
            fputcsv($file, ['Laba Kotor', $data['labaKotor']]);
            fputcsv($file, ['Biaya Operasional', $data['totalBiayaOperasional']]);
            fputcsv($file, ['Laba Bersih', $data['labaBersih']]);
            fputcsv($file, []);

            // Detail Produk
            fputcsv($file, ['BREAKDOWN PRODUK']);
            fputcsv($file, ['No', 'Nama Produk', 'Qty', 'Omzet', 'HPP', 'Laba', 'Margin %']);

            foreach ($data['penjualanProduk'] as $index => $p) {
                $margin = $p['omzet'] > 0 ? round(($p['laba'] / $p['omzet']) * 100, 1) : 0;
                fputcsv($file, [
                    $index + 1,
                    $p['name'],
                    $p['qty'],
                    $p['omzet'],
                    $p['hpp'],
                    $p['laba'],
                    $margin . '%'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function neracaExportCsv(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $data = $this->getNeracaData($tanggal);

        $fileName = "Neraca-{$tanggal}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Judul
            fputcsv($file, ['LAPORAN NERACA']);
            fputcsv($file, ['Per Tanggal', $data['tanggal']]);
            fputcsv($file, []); // Baris kosong

            // Bagian ASET
            fputcsv($file, ['ASET']);
            fputcsv($file, ['Kas', $data['ringkasan']['aset']['lancar']['kas']]);
            fputcsv($file, ['Piutang Dagang', $data['ringkasan']['aset']['lancar']['piutang']]);
            fputcsv($file, ['Persediaan Barang', $data['ringkasan']['aset']['lancar']['stok']]);
            fputcsv($file, ['TOTAL ASET', $data['totalAset']]);
            fputcsv($file, []);

            // Bagian KEWAJIBAN
            fputcsv($file, ['KEWAJIBAN']);
            fputcsv($file, ['Hutang Supplier', $data['totalKewajiban']]);
            fputcsv($file, ['TOTAL KEWAJIBAN', $data['totalKewajiban']]);
            fputcsv($file, []);

            // Bagian MODAL
            fputcsv($file, ['MODAL']);
            fputcsv($file, ['Laba Ditahan', $data['ringkasan']['modal']['laba_ditahan']]);
            fputcsv($file, ['TOTAL MODAL', $data['modal']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stockReport(Request $request)
    {
        // Ambil semua supplier untuk dropdown filter
        $suppliers = \App\Models\Supplier::orderBy('nama_supplier', 'asc')->get();

        // Query utama dengan Eager Loading agar tidak berat (N+1 Problem)
        $query = \App\Models\StockMutation::with(['unit.product.supplier', 'user']);

        // Filter Tanggal
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Filter Nama Barang / SKU (Mencari ke tabel products)
        if ($request->filled('search')) {
            $query->whereHas('unit.product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Filter Supplier
        if ($request->filled('supplier_id')) {
            $query->whereHas('unit.product', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        // Filter Status (Pembelian, Penjualan, dll)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->paginate(25);

        return view('reports.stock', compact('data', 'suppliers'));
    }
}

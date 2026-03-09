<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductUnit;
use App\Models\KreditPayment;
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

            fputcsv($file, ['No','Tanggal','Jam','Invoice','Kasir','Member','Subtotal','Diskon','Total','Bayar','Kembalian','Status','Jumlah Item'], ';');

            $no = 1; $grandTotal = 0; $totalDiskon = 0; $totalBayar = 0;

            foreach ($transactions as $trx) {
                $itemCount = $trx->items->sum('qty');
                $subtotal  = $trx->items->sum(fn($i) => ($i->price - ($i->discount ?? 0)) * $i->qty);
                $tanggal   = $trx->created_at ? $trx->created_at->format('d/m/Y') : '-';
                $jam       = $trx->created_at ? $trx->created_at->format('H:i:s') : '-';

                fputcsv($file, [
                    $no++, $tanggal, $jam,
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
            fputcsv($file, ['','','','','','TOTAL','', $totalDiskon, $grandTotal, $totalBayar,'','',''], ';');

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
            fputcsv($file, ['No','Nama Produk','Barcode','Satuan','Lokasi','Stok','Harga Jual','Harga Beli','Nilai Stok (HPP)'], ';');

            $no = 1; $totalNilai = 0;

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
            fputcsv($file, ['','','','','','TOTAL NILAI STOK (HPP)','','', $totalNilai], ';');
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
       LAPORAN PIUTANG
    =============================== */
    public function piutang(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $data = Transaction::with(['member', 'payments'])
            ->where('status', 'kredit')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.piutang', compact('data', 'from', 'to'));
    }

    /* ===============================
       LAPORAN HUTANG
    =============================== */
    public function hutang(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        $query = PurchaseOrder::with('supplier')->where('status', '!=', 'paid');

        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);

        $data = $query->orderBy('tanggal', 'desc')->paginate(20);

        return view('reports.hutang', compact('data', 'from', 'to'));
    }

    public function hutangDetail($id)
    {
        $po = PurchaseOrder::with(['supplier', 'items.unit.product'])->findOrFail($id);
        return view('reports.hutang_detail', compact('po'));
    }

    public function hutangPay($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        return view('reports.hutang_pay', compact('po'));
    }

    /* ===============================
       JURNAL
    =============================== */
    public function journal(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $data = Transaction::with(['user', 'member'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.journal', compact('data', 'from', 'to'));
    }

    /* ===============================
       HELPER: Harga beli terakhir per unit
       Ambil dari purchase_order_items terakhir (PO yang sudah received)
    =============================== */
    private function getHargaBeli(): array
    {
        // Ambil harga beli terakhir tiap product_unit_id dari PO yang sudah diterima
        $rows = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
            ->whereIn('po.status', ['received', 'approved', 'paid'])
            ->select('poi.product_unit_id', DB::raw('MAX(po.tanggal) as tgl_terakhir'))
            ->groupBy('poi.product_unit_id')
            ->get();

        $hargaBeli = [];

        foreach ($rows as $row) {
            $item = DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
                ->where('poi.product_unit_id', $row->product_unit_id)
                ->where('po.tanggal', $row->tgl_terakhir)
                ->whereIn('po.status', ['received', 'approved', 'paid'])
                ->select('poi.price')
                ->first();

            if ($item) {
                $hargaBeli[$row->product_unit_id] = (float) $item->price;
            }
        }

        return $hargaBeli;
    }

    /* ===============================
       LAPORAN LABA / RUGI
    =============================== */
    public function labaRugi(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        // Ambil semua transaksi paid dan kredit (kredit = sudah terjual, stok sudah berkurang)
        $transactions = Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->with('items.unit')
            ->get();

        // Harga beli terakhir per unit dari PO
        $hargaBeli = $this->getHargaBeli();

        $totalPenjualan = 0;
        $totalModal     = 0;
        $penjualanProduk = [];
        $bulanData       = [];

        foreach ($transactions as $trx) {
            $bulan = $trx->created_at->format('Y-m');

            if (!isset($bulanData[$bulan])) {
                $bulanData[$bulan] = ['penjualan' => 0, 'modal' => 0, 'laba' => 0];
            }

            foreach ($trx->items as $item) {
                $hargaJual  = ($item->price - ($item->discount ?? 0)) * $item->qty;
                $beli       = $hargaBeli[$item->product_unit_id] ?? 0;
                $hpp        = $beli * $item->qty;

                $totalPenjualan += $hargaJual;
                $totalModal     += $hpp;

                $bulanData[$bulan]['penjualan'] += $hargaJual;
                $bulanData[$bulan]['modal']     += $hpp;

                $namaProduk = $item->unit->product->name ?? '-';
                if (!isset($penjualanProduk[$namaProduk])) {
                    $penjualanProduk[$namaProduk] = ['name' => $namaProduk, 'qty' => 0, 'omzet' => 0, 'hpp' => 0, 'laba' => 0];
                }
                $penjualanProduk[$namaProduk]['qty']   += $item->qty;
                $penjualanProduk[$namaProduk]['omzet'] += $hargaJual;
                $penjualanProduk[$namaProduk]['hpp']   += $hpp;
                $penjualanProduk[$namaProduk]['laba']  += ($hargaJual - $hpp);
            }
        }

        // Hitung laba per bulan
        foreach ($bulanData as $b => $v) {
            $bulanData[$b]['laba'] = $v['penjualan'] - $v['modal'];
        }
        ksort($bulanData);

        // Laba kotor
        $labaKotor = $totalPenjualan - $totalModal;

        // Biaya operasional dari PO yang masuk di periode ini (opsional — bisa dikembangkan)
        $totalBiayaOperasional = 0;

        $labaBersih = $labaKotor - $totalBiayaOperasional;

        // Urutkan produk berdasarkan laba tertinggi
        usort($penjualanProduk, fn($a, $b) => $b['laba'] <=> $a['laba']);

        // Persentase margin
        $marginPersen = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0;

        // Jumlah transaksi
        $jumlahTrx      = $transactions->count();
        $jumlahTrxPaid  = $transactions->where('status', 'paid')->count();
        $jumlahTrxKredit = $transactions->where('status', 'kredit')->count();

        // Total piutang (kredit belum lunas) di periode ini
        $totalPiutang = Transaction::where('status', 'kredit')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get()
            ->sum(fn($t) => max($t->total - $t->payments()->sum('amount'), 0));

        return view('reports.laba_rugi', compact(
            'from', 'to',
            'totalPenjualan', 'totalModal',
            'labaKotor', 'totalBiayaOperasional', 'labaBersih',
            'marginPersen', 'penjualanProduk', 'bulanData',
            'jumlahTrx', 'jumlahTrxPaid', 'jumlahTrxKredit',
            'totalPiutang'
        ));
    }

    /* ===============================
       LAPORAN NERACA
    =============================== */
    public function neraca(Request $request)
    {
        // Neraca adalah snapshot PER TANGGAL, bukan range
        $tanggal = $request->input('tanggal', now()->toDateString());

        // =====================
        // ASET LANCAR
        // =====================

        // 1. Kas — total uang masuk dari transaksi paid s/d tanggal ini
        $kas = Transaction::where('status', 'paid')
            ->whereDate('created_at', '<=', $tanggal)
            ->sum('paid')
            +
            // + pembayaran cicilan kredit yang masuk
            KreditPayment::whereDate('paid_at', '<=', $tanggal)->sum('amount')
            -
            // - uang belanja ke supplier (PO yang sudah paid)
            PurchaseOrder::where('status', 'paid')
                ->whereDate('tanggal', '<=', $tanggal)
                ->sum('total_amount');

        // 2. Piutang — sisa hutang dari transaksi kredit yang belum lunas
        $kreditTrx = Transaction::where('status', 'kredit')
            ->whereDate('created_at', '<=', $tanggal)
            ->with('payments')
            ->get();

        $piutang = $kreditTrx->sum(fn($t) => max($t->total - $t->payments->sum('amount'), 0));

        // 3. Nilai stok barang (qty saat ini × harga beli terakhir)
        $hargaBeli = $this->getHargaBeli();

        $stocks    = Stock::with('unit')->get();
        $nilaiStok = 0;
        $detailStok = [];

        foreach ($stocks as $stock) {
            if (($stock->qty ?? 0) <= 0) continue;
            $beli      = $hargaBeli[$stock->product_unit_id] ?? ($stock->unit->price ?? 0) * 0.7;
            $nilai     = $stock->qty * $beli;
            $nilaiStok += $nilai;

            $nama = $stock->unit->product->name ?? '-';
            if (!isset($detailStok[$nama])) {
                $detailStok[$nama] = ['qty' => 0, 'nilai' => 0];
            }
            $detailStok[$nama]['qty']   += $stock->qty;
            $detailStok[$nama]['nilai'] += $nilai;
        }

        $totalAsetLancar = max($kas, 0) + $piutang + $nilaiStok;

        // =====================
        // ASET TETAP
        // =====================
        // Sementara 0 — bisa dikembangkan dengan tabel assets
        $asetTetap = 0;

        $totalAset = $totalAsetLancar + $asetTetap;

        // =====================
        // KEWAJIBAN
        // =====================

        // Hutang ke supplier — PO yang belum paid
        $hutangSupplier = PurchaseOrder::whereNotIn('status', ['paid', 'cancelled'])
            ->whereDate('tanggal', '<=', $tanggal)
            ->sum('total_amount');

        // Hutang dari kredit yang kelebihan bayar (kembalian belum diberikan) — biasanya 0
        $totalKewajiban = $hutangSupplier;

        // =====================
        // MODAL
        // =====================
        $modal = $totalAset - $totalKewajiban;

        // Laba ditahan — dari semua transaksi s/d tanggal ini
        $allTrx = Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereDate('created_at', '<=', $tanggal)
            ->with('items.unit')
            ->get();

        $hargaBeliArr = $this->getHargaBeli();
        $omzetTotal   = 0;
        $hppTotal     = 0;

        foreach ($allTrx as $trx) {
            foreach ($trx->items as $item) {
                $omzetTotal += ($item->price - ($item->discount ?? 0)) * $item->qty;
                $hppTotal   += ($hargaBeliArr[$item->product_unit_id] ?? 0) * $item->qty;
            }
        }

        $labaDitahan = $omzetTotal - $hppTotal;

        // Ringkasan per kategori
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
            'tanggal', 'ringkasan', 'detailStok',
            'kreditTrx', 'totalAset', 'totalKewajiban', 'modal'
        ));
    }

    public function exportLabaRugi(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        // TODO: ambil data laba-rugi dari database
        $data = []; // Contoh dummy

        // Bisa export ke Excel, CSV, PDF, dsb.
        return response()->json([
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);
    }
}
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
use App\Models\Account;
use App\Models\KreditPayment;
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
        $headers  = [
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
                fputcsv($file, [
                    $no++,
                    $trx->created_at?->format('d/m/Y') ?? '-',
                    $trx->created_at?->format('H:i:s') ?? '-',
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
        $trx = Transaction::with(['items.unit.product', 'user', 'member'])->findOrFail($id);
        return view('reports.sales_detail', compact('trx'));
    }

    /* ===============================
       EXPORT STOK KE CSV
    =============================== */
    public function stockCsv(Request $request)
    {
        $query = Stock::with('unit.product');
        if ($request->filled('location')) $query->where('location', $request->location);
        if ($request->filled('q')) $query->whereHas('unit.product', fn($q) => $q->where('name', 'like', '%' . $request->q . '%'));

        $stocks   = $query->orderBy('id', 'desc')->get();
        $filename = 'Laporan_Stok_' . now()->format('d-M-Y_His') . '.csv';
        $headers  = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];

        $callback = function () use ($stocks) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['LAPORAN STOK BARANG'], ';');
            fputcsv($file, ['Dicetak', date('d/m/Y H:i:s')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['No', 'Nama Produk', 'Barcode', 'Satuan', 'Stok', 'Harga Jual', 'Harga Beli', 'Nilai Stok (HPP)'], ';');
            $no = 1;
            $totalNilai = 0;
            foreach ($stocks as $stock) {
                $hpp = $stock->unit->cost ?? 0;
                $nilaiStok = ($stock->qty ?? 0) * $hpp;
                $totalNilai += $nilaiStok;
                fputcsv($file, [
                    $no++,
                    $stock->unit->product->name ?? '-',
                    $stock->unit->barcode ?? '-',
                    $stock->unit->unit_name ?? '-',
                    $stock->qty ?? 0,
                    $stock->unit->price ?? 0,
                    $hpp,
                    $nilaiStok,
                ], ';');
            }
            fputcsv($file, [], ';');
            fputcsv($file, ['', '', '', '', 'TOTAL NILAI STOK (HPP)', '', '', $totalNilai], ';');
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

        if ($request->status && $request->status !== 'all') $query->where('status', $request->status);
        if ($request->supplier_id && $request->supplier_id !== 'all') $query->where('supplier_id', $request->supplier_id);

        $data = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        return view('reports.penerimaan', compact('from', 'to', 'data'));
    }

    public function penerimaanExport(Request $request)
    {
        $dataQuery = $this->getPenerimaanData($request);
        $fileName  = "Laporan_Penerimaan_Barang_" . date('Ymd_His') . ".csv";
        $headers   = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $callback = function () use ($dataQuery) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['No. PO', 'Tanggal', 'Supplier', 'Jenis Pembayaran', 'Total', 'Status']);
            $dataQuery->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->po_number,
                        Carbon::parse($row->tanggal)->format('d/m/Y'),
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

    private function getPenerimaanData(Request $request)
    {
        $from  = $request->input('from', date('Y-m-01'));
        $to    = $request->input('to', date('Y-m-d'));
        $query = PurchaseOrder::with('supplier')->orderBy('tanggal', 'desc')
            ->whereBetween('tanggal', [$from, $to]);
        if ($request->filled('supplier_id') && $request->supplier_id != 'all') $query->where('supplier_id', $request->supplier_id);
        if ($request->filled('status') && $request->status != 'all') $query->where('status', $request->status);
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
        if ($type === 'in' || $type === 'out') $query->where('type', $type);
        $query->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->orderBy('created_at', 'desc');

        $data = $query->paginate(20)->withQueryString();
        return view('reports.stock', compact('data', 'from', 'to', 'type'));
    }

    /* ===============================
       LAPORAN PIUTANG
    =============================== */
    public function piutang(Request $request)
    {
        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to', now()->toDateString());
        $status = $request->input('status');
        $search = $request->input('search');

        $query = $this->getPiutangQuery($request);

        $totalSisaPiutang = (clone $query)->get()->sum(function ($item) {
            return $item->total - ($item->total_terbayar ?? 0);
        });

        $data = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        return view('reports.piutang', compact('data', 'from', 'to', 'totalSisaPiutang', 'status', 'search'));
    }

    public function piutangExport(Request $request)
    {
        $query    = $this->getPiutangQuery($request);
        $fileName = "Laporan_Piutang_" . date('Ymd_His') . ".csv";
        $headers  = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Tanggal', 'No. Invoice', 'Pelanggan', 'Total TRX', 'Dibayar', 'Sisa Hutang', 'Status']);
            $query->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    $dibayar = $row->total_terbayar ?? 0;
                    $sisa    = $row->total - $dibayar;
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

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx_number', 'like', "%{$search}%")
                    ->orWhereHas('member', fn($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }
        if ($status === 'belum_bayar') {
            $query->has('cicilan', '=', 0);
        } elseif ($status === 'cicilan') {
            $query->whereHas('cicilan')
                ->whereRaw('(total - (select ifnull(sum(amount),0) from kredit_payments where transaction_id = transactions.id)) > 0');
        } elseif ($status === 'lunas') {
            $query->whereRaw('(total - (select ifnull(sum(amount),0) from kredit_payments where transaction_id = transactions.id)) <= 0');
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

        $query       = $this->getHutangQuery($request);
        $totalHutang = (clone $query)->sum('total'); // ← pakai total

        $data = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();
        return view('reports.hutang', compact('data', 'from', 'to', 'totalHutang'));
    }

    public function hutangExport(Request $request)
    {
        $query    = $this->getHutangQuery($request);
        $fileName = "Laporan_Hutang_Supplier_" . date('Ymd_His') . ".csv";
        $headers  = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Tanggal', 'No. PO', 'Supplier', 'Total', 'Status']);
            $query->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->tanggal ? $row->tanggal->format('d/m/Y') : '-',
                        $row->po_number,
                        $row->supplier->nama_supplier ?? 'Supplier Umum',
                        $row->total,   // ← pakai total
                        strtoupper($row->status ?? 'DRAFT')
                    ]);
                }
            });
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

        // PO yang belum lunas = hutang
        $query = PurchaseOrder::with('supplier')
            ->whereNotIn('status', ['paid', 'received', 'Received', 'cancelled', 'canceled']);

        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('nama_supplier', 'like', "%{$search}%"));
            });
        }
        if ($status) $query->where('status', $status);

        return $query;
    }

    /* ===============================
       JURNAL
    =============================== */
    public function journal(Request $request)
    {
        $from    = $request->get('from', date('Y-m-01'));
        $to      = $request->get('to', date('Y-m-d'));
        $search  = $request->get('search');
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
        $transaction->trx_number    = 'TRX-MANUAL-' . strtoupper(uniqid());
        $transaction->user_id       = auth()->id();
        $transaction->account_id    = $request->account_id;
        $transaction->total         = $request->total;
        $transaction->paid          = $request->total;
        $transaction->accepted      = $request->total;
        $transaction->change        = 0;
        $transaction->description   = $request->description;
        $transaction->type          = $request->type;
        $transaction->status        = 'paid';
        $transaction->payment_method = 'cash';
        $transaction->created_at    = $request->date . ' ' . date('H:i:s');
        $transaction->updated_at    = now();
        $transaction->save();

        return redirect()->back()->with('success', 'Transaksi berhasil disimpan ke Jurnal!');
    }

    /* ===============================
       EXPORT JURNAL (CSV)
    =============================== */
    public function journalExport(Request $request)
    {
        $from     = $request->get('from', date('Y-m-01'));
        $to       = $request->get('to', date('Y-m-d'));
        $fileName = "Jurnal_Umum_{$from}_to_{$to}.csv";
        $headers  = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName"];

        $callback = function () use ($from, $to) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['TANGGAL', 'NO. TRX', 'AKUN & KETERANGAN', 'REF', 'DEBIT', 'KREDIT']);

            $transactions = Transaction::with('account')
                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->get();

            foreach ($transactions as $row) {
                $isIncome = $row->type == 'income' || $row->type == null;
                $akunNama = $row->account->name ?? ($isIncome ? 'Pendapatan Penjualan' : 'Beban/Lainnya');
                $akunKode = $row->account->code ?? ($isIncome ? '4100' : '5100');
                if ($isIncome) {
                    fputcsv($file, [$row->created_at->format('d/m/Y'), $row->trx_number, 'Kas dan Bank', '1100', $row->total, 0]);
                    fputcsv($file, ['', '', $akunNama . ' - ' . $row->description, $akunKode, 0, $row->total]);
                } else {
                    fputcsv($file, [$row->created_at->format('d/m/Y'), $row->trx_number, $akunNama . ' - ' . $row->description, $akunKode, $row->total, 0]);
                    fputcsv($file, ['', '', 'Kas dan Bank', '1100', 0, $row->total]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ===============================
       HELPER: Harga beli terakhir
    =============================== */
    private function getHargaBeli(): array
    {
        $latestItemIds = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
            ->whereIn(DB::raw('LOWER(po.status)'), ['received', 'approved', 'paid'])
            ->select(DB::raw('MAX(poi.id) as max_id'))
            ->groupBy('poi.product_unit_id')
            ->pluck('max_id');

        return DB::table('purchase_order_items')
            ->whereIn('id', $latestItemIds)
            ->pluck('price', 'product_unit_id')
            ->toArray();
    }

    /* ===============================
       LAPORAN LABA / RUGI
    =============================== */
    public function labaRugi(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $transactions = Transaction::whereIn('status', ['paid', 'kredit'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->with(['items.unit.product'])
            ->get();

        $hargaBeli       = $this->getHargaBeli();
        $totalPenjualan  = 0;
        $totalModal      = 0;
        $penjualanProduk = [];
        $bulanData       = [];

        foreach ($transactions as $trx) {
            $bulan = $trx->created_at->format('Y-m');
            if (!isset($bulanData[$bulan])) $bulanData[$bulan] = ['penjualan' => 0, 'modal' => 0, 'laba' => 0];

            foreach ($trx->items as $item) {
                $hargaJual       = ($item->price - ($item->discount ?? 0)) * $item->qty;
                $beli            = $hargaBeli[$item->product_unit_id] ?? ($item->unit->cost ?? 0);
                $hpp             = $beli * $item->qty;
                $totalPenjualan += $hargaJual;
                $totalModal     += $hpp;

                $bulanData[$bulan]['penjualan'] += $hargaJual;
                $bulanData[$bulan]['modal']     += $hpp;

                $nama = $item->unit->product->name ?? 'Produk Tidak Diketahui';
                if (!isset($penjualanProduk[$nama])) $penjualanProduk[$nama] = ['name' => $nama, 'qty' => 0, 'omzet' => 0, 'hpp' => 0, 'laba' => 0];
                $penjualanProduk[$nama]['qty']   += $item->qty;
                $penjualanProduk[$nama]['omzet'] += $hargaJual;
                $penjualanProduk[$nama]['hpp']   += $hpp;
                $penjualanProduk[$nama]['laba']  += ($hargaJual - $hpp);
            }
        }

        foreach ($bulanData as $b => $v) $bulanData[$b]['laba'] = $v['penjualan'] - $v['modal'];
        ksort($bulanData);

        $labaKotor             = $totalPenjualan - $totalModal;
        $totalBiayaOperasional = DB::table('expenses')->whereBetween('date', [$from, $to])->sum('amount') ?? 0;
        $labaBersih            = $labaKotor - $totalBiayaOperasional;

        usort($penjualanProduk, fn($a, $b) => $b['laba'] <=> $a['laba']);

        $marginPersen     = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0;
        $jumlahTrx        = $transactions->count();
        $jumlahTrxPaid    = $transactions->where('status', 'paid')->count();
        $jumlahTrxKredit  = $transactions->where('status', 'kredit')->count();

        $totalPiutang = Transaction::where('status', 'kredit')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->with('payments')
            ->get()
            ->sum(fn($t) => max($t->total - $t->payments->sum('amount'), 0));

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

        $data = $query->latest()->paginate(25);
        return view('reports.stock', compact('data', 'suppliers'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    // ─────────────────────────────────────
    // HELPER: generate nomor PO/PR
    // ─────────────────────────────────────
    private function generateNumber(string $jenis): string
    {
        $prefix = $jenis === 'PO' ? 'PO' : 'PR';
        return $prefix . '-' . now()->format('YmdHis');
    }

    // ─────────────────────────────────────
    // HELPER: recalculate total
    // ─────────────────────────────────────
    private function recalculateTotal(PurchaseOrder $po): void
    {
        $po->refresh();
        $grandTotal = $po->items->sum(fn($i) => $i->qty * $i->price);
        $ppnRp      = $grandTotal * ($po->ppn ?? 0) / 100;
        $po->update(['total' => $grandTotal + $ppnRp]);
    }

    // ─────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        // Filter
        if ($request->supplier_id && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->cari) {
            $query->where('po_number', 'like', '%' . $request->cari . '%');
        }
        if ($request->dari) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->sampai) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        // Urutkan PO terbaru berdasarkan tanggal + jam (created_at)
        $pos = $query
            ->orderByRaw("tanggal DESC, created_at DESC")
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.index', compact('pos', 'suppliers'));
    }

    // ─────────────────────────────────────
    // CREATE — buat draft PO baru
    // ─────────────────────────────────────
    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $units     = ProductUnit::with('product')->get();

        $po = PurchaseOrder::create([
            'user_id'          => Auth::id(),
            'po_number'        => $this->generateNumber('Pembelian'),
            'tanggal'          => Carbon::now()->format('Y-m-d'),
            'status'           => 'draft',
            'jenis_transaksi'  => 'Pembelian',
            'jenis_pembayaran' => 'Cash',
            'ppn'              => 0,
            'total'            => 0,
            'gudang'           => 'Gudang Utama',
        ]);

        return view('po.create', compact('suppliers', 'units', 'po'));
    }

    // ─────────────────────────────────────
    // STORE — simpan header PO baru (dari draft kosong)
    // ─────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'po_number'       => 'required|string|max:50',
            'supplier_id'     => 'required|exists:suppliers,id',
            'tanggal'         => 'required|date',
            'jenis_transaksi' => 'required|in:Pembelian,PO',
        ]);

        // Cari draft yang sedang aktif (nomor sama)
        $po = PurchaseOrder::where('po_number', $request->po_number)
            ->where('status', 'draft')
            ->first();

        if (!$po) {
            $po = new PurchaseOrder();
            $po->user_id = Auth::id();
            $po->status  = 'draft';
        }

        $po->fill([
            'po_number'            => $request->po_number,
            'supplier_id'          => $request->supplier_id,
            'tanggal'              => Carbon::parse($request->tanggal)->format('Y-m-d'),
            'gudang'               => $request->gudang,
            'jenis_transaksi'      => $request->jenis_transaksi,
            'jenis_pembayaran'     => $request->jenis_transaksi === 'PO' ? 'Cash' : ($request->jenis_pembayaran ?? 'Cash'),
            'nomor_faktur'         => $request->nomor_faktur,
            'tanggal_faktur'       => $request->tanggal_faktur ? Carbon::parse($request->tanggal_faktur)->format('Y-m-d') : null,
            'jk_waktu'             => $request->jenis_transaksi === 'PO' ? 0 : ($request->jk_waktu ?? 0),
            'tanggal_jatuh_tempo'  => $request->tanggal_jatuh_tempo ? Carbon::parse($request->tanggal_jatuh_tempo)->format('Y-m-d') : null,
            'ppn'                  => $request->jenis_transaksi === 'PO' ? 0 : ($request->ppn ?? 0),
            'bulan_lapor'          => $request->jenis_transaksi === 'PO' ? null : $request->bulan_lapor,
        ]);

        $po->save();
        $this->recalculateTotal($po);

        return redirect()->route('po.edit', $po->id)
            ->with('success', 'Header PO berhasil disimpan.');
    }

    // ─────────────────────────────────────
    // EDIT — tampilkan form edit/input
    // ─────────────────────────────────────
    public function edit($id)
    {
        $po        = PurchaseOrder::with('items.unit.product', 'supplier')->findOrFail($id);
        $units     = ProductUnit::with('product')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.create', compact('po', 'units', 'suppliers'));
    }

    // ─────────────────────────────────────
    // UPDATE HEADER — simpan perubahan header (PO yang sudah ada supplier)
    // ─────────────────────────────────────
    public function updateHeader(Request $request, $id)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'Header tidak bisa diubah — PO sudah dikunci.');
        }

        $po->update([
            'po_number'           => $request->po_number ?? $po->po_number,
            'supplier_id'         => $request->supplier_id,
            'tanggal'             => Carbon::parse($request->tanggal)->format('Y-m-d'),
            'gudang'              => $request->gudang,
            'jenis_transaksi'     => $request->jenis_transaksi ?? $po->jenis_transaksi,
            'jenis_pembayaran'    => $request->jenis_transaksi === 'PO' ? 'Cash' : ($request->jenis_pembayaran ?? $po->jenis_pembayaran),
            'nomor_faktur'        => $request->nomor_faktur,
            'tanggal_faktur'      => $request->tanggal_faktur ? Carbon::parse($request->tanggal_faktur)->format('Y-m-d') : null,
            'jk_waktu'            => $request->jenis_transaksi === 'PO' ? 0 : ($request->jk_waktu ?? 0),
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo ? Carbon::parse($request->tanggal_jatuh_tempo)->format('Y-m-d') : null,
            'ppn'                 => $request->jenis_transaksi === 'PO' ? 0 : ($request->ppn ?? 0),
            'bulan_lapor'         => $request->jenis_transaksi === 'PO' ? null : $request->bulan_lapor,
        ]);

        $this->recalculateTotal($po);

        return redirect()->route('po.edit', $po->id)
            ->with('success', 'Header PO berhasil diperbarui.');
    }

    // ─────────────────────────────────────
    // ADD ITEM
    // ─────────────────────────────────────
    public function addItem(Request $request, $poId)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty'             => 'required|numeric|min:1',
            'price'           => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::findOrFail($poId);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah dikunci, tidak bisa tambah item.');
        }

        // Hitung harga setelah diskon
        $diskonPersen  = (float) ($request->diskon_persen ?? 0);
        $hargaAfterDis = $request->price * (1 - $diskonPersen / 100);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_unit_id'   => $request->product_unit_id,
            'qty'               => $request->qty,
            'price'             => $hargaAfterDis,   // simpan harga setelah diskon
            'bonus_nama'        => $request->bonus_nama ?: null,
            'bonus_qty'         => $request->bonus_qty ?? 0,
        ]);

        $this->recalculateTotal($po);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    // ─────────────────────────────────────
    // DELETE ITEM
    // ─────────────────────────────────────
    public function deleteItem($itemId)
    {
        $item = PurchaseOrderItem::findOrFail($itemId);
        $po   = PurchaseOrder::findOrFail($item->purchase_order_id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah dikunci, tidak bisa hapus item.');
        }

        $item->delete();
        $this->recalculateTotal($po);

        return back()->with('success', 'Item berhasil dihapus.');
    }

    // ─────────────────────────────────────
    // APPROVE
    // ─────────────────────────────────────
    public function approve($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah diproses.');
        }
        if ($po->items()->count() === 0) {
            return back()->with('error', 'PO belum memiliki item.');
        }
        if (!$po->supplier_id) {
            return back()->with('error', 'Supplier belum dipilih.');
        }

        $po->update(['status' => 'approved']);

        return redirect()->route('po.index')
            ->with('success', 'PO #' . $po->po_number . ' berhasil di-approve.');
    }

    // ─────────────────────────────────────
    // RECEIVE — terima barang, update stok
    // ─────────────────────────────────────
    public function receive($id)
    {
        DB::transaction(function () use ($id) {

            $po = PurchaseOrder::with('items')->findOrFail($id);

            if ($po->status !== 'approved') {
                throw new \Exception('PO belum approved.');
            }

            foreach ($po->items as $item) {

                // Update stok — cari berdasarkan warehouse gudang PO
                $warehouseId = \App\Models\Warehouse::where('name', $po->gudang)
                    ->orWhere('is_active', true)
                    ->value('id') ?? 1;

                $stock = Stock::firstOrCreate(
                    ['product_unit_id' => $item->product_unit_id, 'warehouse_id' => $warehouseId],
                    ['qty' => 0]
                );
                $stock->increment('qty', $item->qty);

                // Catat mutasi stok
                $lastMutation = \App\Models\StockMutation::where('unit_id', $item->product_unit_id)
                    ->latest('created_at')->first();

                $stockBefore = $lastMutation?->stock_after ?? 0;
                $stockAfter  = $stockBefore + $item->qty;

                \App\Models\StockMutation::create([
                    'unit_id'      => $item->product_unit_id,
                    'user_id'      => Auth::id(),
                    'type'         => 'in',
                    'qty'          => $item->qty,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'reference'    => $po->po_number,
                    'description'  => 'Pembelian Barang — ' . $po->po_number,
                ]);
            }

            $po->update(['status' => 'received']);
        });

        return redirect()->route('po.index')
            ->with('success', 'Barang berhasil diterima & stok diperbarui.');
    }

    // ─────────────────────────────────────
    // CANCEL
    // ─────────────────────────────────────
    public function cancel($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO tidak bisa dibatalkan (bukan draft).');
        }

        $po->update(['status' => 'canceled']);

        return redirect()->route('po.index')
            ->with('success', 'PO berhasil dibatalkan.');
    }

    // ─────────────────────────────────────
    // DESTROY — hapus permanen
    // ─────────────────────────────────────
    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->items()->delete();
        $po->delete();

        return back()->with('success', 'PO berhasil dihapus.');
    }

    // ─────────────────────────────────────
    // SHOW — detail view (opsional)
    // ─────────────────────────────────────
    public function show($id)
    {
        $po = PurchaseOrder::with('supplier', 'items.unit.product')->findOrFail($id);
        return view('po.show', compact('po'));
    }
}

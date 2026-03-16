<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{

    // =====================================================================
    // GENERATE NOMOR PO / PR
    // =====================================================================
    private function generateNumber(string $jenis): string
    {
        $prefix = $jenis === 'PO' ? 'PO' : 'PR';
        return $prefix . '-' . now()->format('YmdHis');
    }


    // =====================================================================
    // HITUNG ULANG TOTAL PO
    // =====================================================================
    private function recalculateTotal(PurchaseOrder $po): void
    {
        $po->refresh();

        $grandTotal = $po->items->sum(fn($i) => $i->qty * $i->price);
        $ppnRp      = $grandTotal * ($po->ppn ?? 0) / 100;

        $po->update([
            'total' => $grandTotal + $ppnRp
        ]);
    }


    // =====================================================================
    // HELPER: Resolve warehouse_id dari nama gudang
    // =====================================================================
    private function resolveWarehouseId(string $gudang): int
    {
        return Warehouse::where('name', $gudang)->value('id') ?? 1;
    }


    // =====================================================================
    // HELPER: Tambah stok
    // =====================================================================
    private function incrementStock(int $productUnitId, int $warehouseId, float $qty): void
    {
        $stock = Stock::firstOrCreate(
            [
                'product_unit_id' => $productUnitId,
                'warehouse_id'    => $warehouseId,
            ],
            ['qty' => 0]
        );

        $stock->increment('qty', $qty);
    }


    // =====================================================================
    // HELPER: Kurangi stok
    // =====================================================================
    private function decrementStock(int $productUnitId, int $warehouseId, float $qty): void
    {
        $stock = Stock::where('product_unit_id', $productUnitId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($stock) {
            $stock->decrement('qty', $qty);
        }
    }


    // =====================================================================
    // LIST PO
    // =====================================================================
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

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

        $pos = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.index', compact('pos', 'suppliers'));
    }


    // =====================================================================
    // CREATE PO
    // =====================================================================
    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $units     = ProductUnit::with('product')->get();

        // Selalu buat draft baru
        $po = PurchaseOrder::create([
            'user_id'          => Auth::id(),
            'po_number'        => $this->generateNumber('PO'),
            'tanggal'          => now(),
            'status'           => 'draft',
            'jenis_transaksi'  => 'Pembelian',
            'jenis_pembayaran' => 'Cash',
            'ppn'              => 0,
            'total'            => 0,
            'gudang'           => 'Gudang Utama',
        ]);

        return view('po.create', compact('suppliers', 'units', 'po'));
    }


    // =====================================================================
    // EDIT PO
    // =====================================================================
    public function edit($id)
    {
        $po        = PurchaseOrder::with('items.unit.product', 'supplier')->findOrFail($id);
        $units     = ProductUnit::with('product')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.create', compact('po', 'units', 'suppliers'));
    }


    // =====================================================================
    // UPDATE HEADER
    // Hanya bisa diubah saat status = draft
    // =====================================================================
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
            'jenis_pembayaran'    => $request->jenis_transaksi === 'PO'
                ? 'Cash'
                : ($request->jenis_pembayaran ?? $po->jenis_pembayaran),

            'nomor_faktur'        => $request->nomor_faktur,
            'tanggal_faktur'      => $request->tanggal_faktur
                ? Carbon::parse($request->tanggal_faktur)->format('Y-m-d')
                : null,

            'jk_waktu'            => $request->jenis_transaksi === 'PO'
                ? 0
                : ($request->jk_waktu ?? 0),

            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo
                ? Carbon::parse($request->tanggal_jatuh_tempo)->format('Y-m-d')
                : null,

            'ppn'                 => $request->jenis_transaksi === 'PO'
                ? 0
                : ($request->ppn ?? 0),

            'bulan_lapor'         => $request->jenis_transaksi === 'PO'
                ? null
                : $request->bulan_lapor,
        ]);

        $this->recalculateTotal($po);

        return redirect()->route('po.edit', $po->id)
            ->with('success', 'Header PO berhasil diperbarui.');
    }


    // =====================================================================
    // TAMBAH ITEM
    // Draft           → simpan item saja
    // Approved        → simpan item saja (belum ada stok)
    // Received        → simpan item + langsung tambah stok
    // =====================================================================
    public function addItem(Request $request, $poId)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty'             => 'required|numeric|min:1',
            'price'           => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::findOrFail($poId);

        if ($po->status === 'canceled') {
            return back()->with('error', 'PO yang sudah dibatalkan tidak dapat diubah.');
        }

        $diskonPersen     = (float) ($request->diskon_persen ?? 0);
        $hargaAfterDiskon = $request->price * (1 - $diskonPersen / 100);

        DB::transaction(function () use ($po, $request, $hargaAfterDiskon) {

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_unit_id'   => $request->product_unit_id,
                'qty'               => $request->qty,
                'price'             => $hargaAfterDiskon,
                'bonus_nama'        => $request->bonus_nama ?: null,
                'bonus_qty'         => $request->bonus_qty ?? 0,
            ]);

            // Jika sudah received → langsung update stok
            if ($po->status === 'received') {
                $warehouseId = $this->resolveWarehouseId($po->gudang);
                $this->incrementStock($request->product_unit_id, $warehouseId, $request->qty);
            }

            $this->recalculateTotal($po);
        });

        return redirect()->route('po.edit', $po->id)
            ->with('success', 'Item berhasil ditambahkan.' .
                ($po->status === 'received' ? ' Stok produk telah diperbarui.' : ''));
    }


    // =====================================================================
    // EDIT ITEM (QTY & HARGA)
    // Draft           → ubah item saja
    // Approved        → ubah item saja
    // Received        → ubah item + sesuaikan selisih stok
    // =====================================================================
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'qty'   => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $item = PurchaseOrderItem::findOrFail($itemId);
        $po   = PurchaseOrder::findOrFail($item->purchase_order_id);

        if ($po->status === 'canceled') {
            return back()->with('error', 'PO yang sudah dibatalkan tidak dapat diubah.');
        }

        DB::transaction(function () use ($po, $item, $request) {

            // Jika received → hitung selisih qty untuk koreksi stok
            if ($po->status === 'received') {
                $selisih     = $request->qty - $item->qty;
                $warehouseId = $this->resolveWarehouseId($po->gudang);

                if ($selisih > 0) {
                    // Qty bertambah → tambah stok
                    $this->incrementStock($item->product_unit_id, $warehouseId, $selisih);
                } elseif ($selisih < 0) {
                    // Qty berkurang → kurangi stok
                    $this->decrementStock($item->product_unit_id, $warehouseId, abs($selisih));
                }
            }

            $item->update([
                'qty'   => $request->qty,
                'price' => $request->price,
            ]);

            $this->recalculateTotal($po);
        });

        return redirect()->route('po.edit', $po->id)
            ->with('success', 'Item berhasil diperbarui.' .
                ($po->status === 'received' ? ' Stok produk telah disesuaikan.' : ''));
    }


    // =====================================================================
    // HAPUS ITEM
    // Draft           → hapus item saja
    // Approved        → hapus item saja
    // Received        → hapus item + reverse stok
    // =====================================================================
    public function deleteItem($itemId)
    {
        $item = PurchaseOrderItem::findOrFail($itemId);
        $po   = PurchaseOrder::findOrFail($item->purchase_order_id);

        if ($po->status === 'canceled') {
            return back()->with('error', 'PO yang sudah dibatalkan tidak dapat diubah.');
        }

        // Minimal 1 item
        if ($po->items()->count() <= 1) {
            return back()->with('error', 'PO harus memiliki minimal 1 item.');
        }

        DB::transaction(function () use ($po, $item) {

            // Jika received → reverse stok terlebih dahulu
            if ($po->status === 'received') {
                $warehouseId = $this->resolveWarehouseId($po->gudang);
                $this->decrementStock($item->product_unit_id, $warehouseId, $item->qty);
            }

            $item->delete();

            $this->recalculateTotal($po);
        });

        return redirect()->route('po.edit', $po->id)
            ->with('success', 'Item berhasil dihapus.' .
                ($po->status === 'received' ? ' Stok produk telah disesuaikan.' : ''));
    }


    // =====================================================================
    // APPROVE PO
    // =====================================================================
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


    // =====================================================================
    // RECEIVE BARANG
    // =====================================================================
    public function receive($id)
    {
        DB::transaction(function () use ($id) {

            $po = PurchaseOrder::with('items')->findOrFail($id);

            if ($po->status !== 'approved') {
                throw new \Exception('PO belum approved.');
            }

            $warehouseId = $this->resolveWarehouseId($po->gudang);

            foreach ($po->items as $item) {
                $this->incrementStock($item->product_unit_id, $warehouseId, $item->qty);
            }

            $po->update(['status' => 'received']);
        });

        return redirect()->route('po.index')
            ->with('success', 'Barang berhasil diterima & stok diperbarui.');
    }


    // =====================================================================
    // CANCEL PO
    // =====================================================================
    public function cancel($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO tidak bisa dibatalkan.');
        }

        $po->update(['status' => 'canceled']);

        return redirect()->route('po.index')
            ->with('success', 'PO berhasil dibatalkan.');
    }


    // =====================================================================
    // HAPUS PO
    // =====================================================================
    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        $po->items()->delete();
        $po->delete();

        return back()->with('success', 'PO berhasil dihapus.');
    }


    // =====================================================================
    // SHOW
    // =====================================================================
    public function show($id)
    {
        $po = PurchaseOrder::with('supplier', 'items.unit.product')
            ->findOrFail($id);

        return view('po.show', compact('po'));
    }
}
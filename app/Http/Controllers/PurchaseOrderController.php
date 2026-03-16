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

class PurchaseOrderController extends Controller
{
    // =====================================================================
    // HELPER METHODS
    // =====================================================================
    private function generateNumber(string $jenis): string
    {
        $prefix = $jenis === 'PO' ? 'PO' : 'PR';
        return $prefix . '-' . now()->format('YmdHis');
    }

    private function recalculateTotal(PurchaseOrder $po): void
    {
        $po->refresh();
        $grandTotal = $po->items->sum(fn($i) => (float)$i->qty * (float)$i->price);
        $ppnRp = $grandTotal * ($po->ppn ?? 0) / 100;

        $po->update([
            'total' => $grandTotal + $ppnRp
        ]);
    }

    private function resolveWarehouseId(string $gudangName): int
    {
        return Warehouse::where('name', $gudangName)->value('id') ?? 1;
    }

    private function incrementStock(int $productUnitId, int $warehouseId, float $qty): void
    {
        $stock = Stock::firstOrCreate(
            ['product_unit_id' => $productUnitId, 'warehouse_id' => $warehouseId],
            ['qty' => 0]
        );
        $stock->increment('qty', $qty);
    }

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
    // MAIN METHODS
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

        $pos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.index', compact('pos', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $units = ProductUnit::with('product')->get(); 

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

    public function edit($id)
    {
        $po = PurchaseOrder::with(['items.unit.product', 'supplier'])->findOrFail($id);

        $units = ProductUnit::with('product')
            ->withSum('stock as total_stok', 'qty')
            ->get()
            ->map(function ($unit) {
                return (object) [
                    'id' => $unit->id,
                    'unit_name' => $unit->unit_name,
                    'price' => $unit->price,
                    'product' => (object) [
                        'name' => $unit->product->nama_produk ?? $unit->product->name ?? '-'
                    ],
                    'stok' => $unit->total_stok ?? 0
                ];
            });

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.edit', compact('po', 'units', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);

        if ($po->status === 'canceled') {
            return back()->with('error', 'PO yang dibatalkan tidak bisa diubah.');
        }

        DB::transaction(function () use ($po, $request) {
            $warehouseId = $this->resolveWarehouseId($po->gudang);

            if ($po->status === 'received') {
                foreach ($po->items as $oldItem) {
                    $this->decrementStock($oldItem->product_unit_id, $warehouseId, $oldItem->qty);
                }
            }

            $po->update($request->only(['supplier_id', 'tanggal', 'jenis_pembayaran', 'ppn', 'gudang']));
            $po->items()->delete();

            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    $newItem = PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_unit_id'   => $itemData['product_id'],
                        'qty'               => $itemData['qty'],
                        'price'             => $itemData['harga_satuan'],
                    ]);

                    if ($po->status === 'received') {
                        $this->incrementStock($newItem->product_unit_id, $warehouseId, $newItem->qty);
                    }
                }
            }

            $this->recalculateTotal($po);
        });

        return redirect()->route('po.index')->with('success', 'Perubahan Purchase Order berhasil disimpan.');
    }

    public function approve($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'draft') return back()->with('error', 'PO sudah diproses.');
        
        $po->update(['status' => 'approved']);
        return back()->with('success', 'PO Approved.');
    }

    public function receive($id)
    {
        DB::transaction(function () use ($id) {
            $po = PurchaseOrder::with('items')->findOrFail($id);
            if ($po->status !== 'approved') throw new \Exception('PO belum approved.');

            $warehouseId = $this->resolveWarehouseId($po->gudang);
            foreach ($po->items as $item) {
                $this->incrementStock($item->product_unit_id, $warehouseId, $item->qty);
            }
            $po->update(['status' => 'received']);
        });

        return back()->with('success', 'Barang diterima & stok bertambah.');
    }

    public function cancel($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status === 'received') return back()->with('error', 'Tidak bisa membatalkan PO yang sudah diterima.');

        $po->update(['status' => 'canceled']);
        return back()->with('success', 'PO dibatalkan.');
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);

        DB::transaction(function () use ($po) {
            if ($po->status === 'received') {
                $warehouseId = $this->resolveWarehouseId($po->gudang);
                foreach ($po->items as $item) {
                    $this->decrementStock($item->product_unit_id, $warehouseId, $item->qty);
                }
            }
            $po->items()->delete();
            $po->delete();
        });

        return back()->with('success', 'PO berhasil dihapus.');
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'items.unit.product'])->findOrFail($id);
        return view('po.show', compact('po'));
    }

    /**
     * Menghapus satu item barang dan kembali ke halaman edit
     */
    public function deleteItem($id)
    {
        DB::transaction(function () use ($id) {
            $item = PurchaseOrderItem::with('purchaseOrder')->findOrFail($id);
            $po = $item->purchaseOrder;

            if ($po->status === 'received') {
                $warehouseId = $this->resolveWarehouseId($po->gudang);
                $this->decrementStock($item->product_unit_id, $warehouseId, $item->qty);
            }

            $item->delete();
            $this->recalculateTotal($po);
        });

        return back()->with('success', 'Item berhasil dihapus dari daftar.');
    }

    /**
     * Menambah satu item barang dan kembali ke halaman edit
     */
    public function addItem(Request $request, $id)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {
            $po = PurchaseOrder::findOrFail($id);

            $item = PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_unit_id'   => $request->product_unit_id,
                'qty'               => $request->qty,
                'price'             => $request->price,
            ]);

            if ($po->status === 'received') {
                $warehouseId = $this->resolveWarehouseId($po->gudang);
                $this->incrementStock($item->product_unit_id, $warehouseId, $item->qty);
            }

            $this->recalculateTotal($po);
        });

        return back()->with('success', 'Barang berhasil ditambahkan.');
    }
}
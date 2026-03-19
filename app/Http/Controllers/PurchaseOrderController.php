<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\Warehouse;
use App\Observers\PurchaseOrderItemObserver;
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

        $totalHna     = 0;
        $totalDiskBrg = 0;
        $totalOngkir  = 0;

        foreach ($po->items as $item) {
            $hna           = (float)$item->qty * (float)$item->price;
            $disk          = $hna * ((float)($item->diskon_persen ?? 0) / 100);
            $ongkir        = (float)($item->ongkir ?? 0);
            $totalHna     += $hna;
            $totalDiskBrg += $disk;
            $totalOngkir  += $ongkir;
        }

        $subTotal = $totalHna - $totalDiskBrg + $totalOngkir;

        $discNotaPersen = (float)($po->disc_nota_persen ?? 0);
        $discNotaRupiah = (float)($po->disc_nota_rupiah ?? 0);
        if ($discNotaPersen > 0) {
            $discNotaRupiah = round($subTotal * $discNotaPersen / 100, 0);
        }
        $afterDisc      = $subTotal - $discNotaRupiah;
        $ppnRp          = round($afterDisc * ((float)($po->ppn ?? 0) / 100), 0);
        $biayaTransport = (float)($po->biaya_transport ?? 0);
        $totalNetto     = $afterDisc + $ppnRp + $biayaTransport;

        $po->update([
            'total_hna'        => $totalHna,
            'total_disk_brg'   => $totalDiskBrg,
            'disc_nota_rupiah' => $discNotaRupiah,
            'total_netto'      => $totalNetto,
            'total'            => $totalNetto,
        ]);
    }

    private function resolveWarehouseId(string $gudangName): int
    {
        return Warehouse::where('name', $gudangName)->value('id') ?? 1;
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

        $pos       = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.index', compact('pos', 'suppliers'));
    }

    public function create()
    {
        $po = PurchaseOrder::create([
            'user_id'          => Auth::id(),
            'po_number'        => $this->generateNumber('PR'),
            'tanggal'          => now(),
            'status'           => 'draft',
            'jenis_transaksi'  => 'Pembelian',
            'jenis_pembayaran' => 'Cash',
            'ppn'              => 0,
            'total'            => 0,
            'gudang'           => 'Gudang Utama',
            'disc_nota_persen' => 0,
            'disc_nota_rupiah' => 0,
            'biaya_transport'  => 0,
        ]);

        return redirect()->route('po.edit', $po->id);
    }

    public function edit($id)
    {
        $po = PurchaseOrder::with(['items.unit.product', 'supplier'])->findOrFail($id);

        $units = ProductUnit::with('product')
            ->withSum('stock as total_stok', 'qty')
            ->get()
            ->map(function ($unit) {
                return (object) [
                    'id'        => $unit->id,
                    'unit_name' => $unit->unit_name,
                    'price'     => $unit->price,
                    'product'   => (object) [
                        'name' => $unit->product->nama_produk ?? $unit->product->name ?? '-'
                    ],
                    'stok' => $unit->total_stok ?? 0,
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

            if ($po->status === 'received' && $po->jenis_transaksi !== 'PO') {
                foreach ($po->items as $oldItem) {
                    $this->decrementStock($oldItem->product_unit_id, $warehouseId, $oldItem->qty);
                }
            }

            $po->update($request->only([
                'supplier_id','tanggal','jenis_pembayaran','ppn','gudang',
                'disc_nota_persen','disc_nota_rupiah','biaya_transport',
            ]));
            $po->items()->delete();

            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_unit_id'   => $itemData['product_id'],
                        'qty'               => $itemData['qty'],
                        'price'             => $itemData['harga_satuan'],
                        'diskon_persen'     => $itemData['diskon_persen'] ?? 0,
                        'ongkir'            => $itemData['ongkir'] ?? 0,
                        'bonus_nama'        => $itemData['bonus_nama'] ?? null,
                        'bonus_qty'         => $itemData['bonus_qty'] ?? 0,
                    ]);
                }
            }

            $this->recalculateTotal($po);
        });

        return redirect()->route('po.index')->with('success', 'Perubahan Purchase Order berhasil disimpan.');
    }

    /**
     * APPROVE:
     * ✅ PR → tambah stok + catat mutasi untuk semua item yang sudah ada (dibuat saat draft)
     * ❌ PO → tidak tambah stok, hanya ubah status
     */
    public function approve($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah diproses.');
        }

        DB::transaction(function () use ($po) {
            // Ubah status ke approved dulu agar observer tahu
            $po->update(['status' => 'approved']);

            // Untuk PR: catat stok masuk untuk semua item yang sudah ada sejak draft
            if ($po->jenis_transaksi !== 'PO') {
                foreach ($po->items as $item) {
                    // Update supplier di produk
                    if ($item->unit && $item->unit->product) {
                        $item->unit->product->update(['supplier_id' => $po->supplier_id]);
                    }

                    // Tambah stok + catat mutasi menggunakan helper observer
                    PurchaseOrderItemObserver::tambahStokDanMutasi($item, $po);
                }
            }
        });

        return back()->with('success', 'PO Approved. Stok masuk telah dicatat.');
    }

    /**
     * RECEIVE:
     * Status berubah dari approved → received.
     * Stok sudah ditambah saat approve, jadi di sini hanya update status.
     */
    public function receive($id)
    {
        DB::transaction(function () use ($id) {
            $po = PurchaseOrder::with('items')->findOrFail($id);
            if ($po->status !== 'approved') {
                throw new \Exception('PO belum approved.');
            }

            $po->update(['status' => 'received']);
        });

        return back()->with('success', 'Barang diterima. Status diperbarui ke Received.');
    }

    public function updateHeader(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal'     => 'required|date',
            'gudang'      => 'nullable|string',
        ]);

        $po = PurchaseOrder::findOrFail($id);

        $po->update([
            'supplier_id'         => $request->supplier_id,
            'tanggal'             => $request->tanggal,
            'gudang'              => $request->gudang,
            'nomor_faktur'        => $request->nomor_faktur,
            'tanggal_faktur'      => $request->tanggal_faktur,
            'jenis_pembayaran'    => $request->jenis_pembayaran,
            'jk_waktu'            => $request->jk_waktu ?? 0,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'ppn'                 => $request->ppn,
            'bulan_lapor'         => $request->bulan_lapor,
            'jenis_transaksi'     => $request->jenis_transaksi,
            'po_number'           => $request->po_number,
            'disc_nota_persen'    => $request->disc_nota_persen ?? 0,
            'disc_nota_rupiah'    => $request->disc_nota_rupiah ?? 0,
            'biaya_transport'     => $request->biaya_transport ?? 0,
        ]);

        $this->recalculateTotal($po);

        return redirect()->route('po.edit', $id)->with('success', 'Header transaksi berhasil disimpan.');
    }

    public function cancel($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);

        if ($po->status === 'received') {
            return back()->with('error', 'Tidak bisa membatalkan PO yang sudah diterima.');
        }

        DB::transaction(function () use ($po) {
            // Jika sudah approved dan PR, kembalikan stok yang sudah ditambah saat approve
            if ($po->status === 'approved' && $po->jenis_transaksi !== 'PO') {
                foreach ($po->items as $item) {
                    $stock = Stock::where('product_unit_id', $item->product_unit_id)->first();
                    if ($stock) {
                        $before = $stock->qty;
                        $after  = max($before - $item->qty, 0);
                        $stock->update(['qty' => $after]);

                        StockMutation::create([
                            'unit_id'      => $item->product_unit_id,
                            'user_id'      => $po->user_id ?? null,
                            'type'         => 'out',
                            'status'       => 'retur_pembelian',
                            'qty'          => $item->qty,
                            'stock_before' => $before,
                            'stock_after'  => $after,
                            'reference'    => $po->po_number ?? '-',
                            'description'  => 'Pembatalan PO (stok dikembalikan)',
                        ]);
                    }
                }
            }

            $po->update(['status' => 'canceled']);
        });

        return back()->with('success', 'PO dibatalkan.');
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);

        DB::transaction(function () use ($po) {
            // Kembalikan stok jika sudah approved atau received dan PR
            if (in_array($po->status, ['approved', 'received']) && $po->jenis_transaksi !== 'PO') {
                foreach ($po->items as $item) {
                    $stock = Stock::where('product_unit_id', $item->product_unit_id)->first();
                    if ($stock) {
                        $stock->decrement('qty', $item->qty);
                    }
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

    public function deleteItem($id)
    {
        DB::transaction(function () use ($id) {
            $item = PurchaseOrderItem::with('purchaseOrder')->findOrFail($id);
            $po   = $item->purchaseOrder;

            // Kembalikan stok jika sudah approved/received dan PR
            if (in_array($po->status, ['approved', 'received']) && $po->jenis_transaksi !== 'PO') {
                $stock = Stock::where('product_unit_id', $item->product_unit_id)->first();
                if ($stock) {
                    $before = $stock->qty;
                    $after  = max($before - $item->qty, 0);
                    $stock->update(['qty' => $after]);

                    StockMutation::create([
                        'unit_id'      => $item->product_unit_id,
                        'user_id'      => $po->user_id ?? null,
                        'type'         => 'out',
                        'status'       => 'retur_pembelian',
                        'qty'          => $item->qty,
                        'stock_before' => $before,
                        'stock_after'  => $after,
                        'reference'    => $po->po_number ?? '-',
                        'description'  => 'Hapus Item PO (stok dikembalikan)',
                    ]);
                }
            }

            $item->delete();
            $this->recalculateTotal($po);
        });

        return back()->with('success', 'Item berhasil dihapus.');
    }

    /**
     * Tambah item:
     * - Observer handle stok jika status sudah approved dan jenis PR
     * - Draft → observer skip otomatis
     * - PO → observer skip otomatis
     */
    public function addItem(Request $request, $id)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty'             => 'required|numeric|min:0.01',
            'price'           => 'required|numeric|min:0',
            'diskon_persen'   => 'nullable|numeric|min:0|max:100',
            'ongkir'          => 'nullable|numeric|min:0',
            'bonus_qty'       => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {
            $po = PurchaseOrder::findOrFail($id);

            // Observer akan otomatis tambah stok jika status bukan draft dan bukan PO
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_unit_id'   => $request->product_unit_id,
                'qty'               => $request->qty,
                'price'             => $request->price,
                'diskon_persen'     => $request->diskon_persen ?? 0,
                'ongkir'            => $request->ongkir ?? 0,
                'bonus_nama'        => $request->bonus_nama ?? null,
                'bonus_qty'         => $request->bonus_qty ?? 0,
            ]);

            $this->recalculateTotal($po);
        });

        return back()->with('success', 'Barang berhasil ditambahkan.');
    }
}
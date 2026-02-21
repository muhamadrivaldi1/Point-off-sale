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

class PurchaseOrderController extends Controller
{
    // -------------------------------------------------------
    // INDEX
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->status) {
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

        $pos       = $query->latest()->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.index', compact('pos', 'suppliers'));
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------
    public function create()
    {
        $po = PurchaseOrder::create([
            'user_id'   => Auth::id(),
            'status'    => 'draft',
            'po_number' => 'PO-' . now()->format('YmdHis'),
        ]);

        return redirect()->route('po.edit', $po->id);
    }

    // -------------------------------------------------------
    // EDIT
    // -------------------------------------------------------
    public function edit($id)
    {
        $po        = PurchaseOrder::with('items.unit.product')->findOrFail($id);
        $units     = ProductUnit::with('product')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.edit', compact('po', 'units', 'suppliers'));
    }

    // -------------------------------------------------------
    // UPDATE HEADER
    // -------------------------------------------------------
    public function updateHeader(Request $request, $id)
    {
        $request->validate([
            'supplier_id'         => 'required|exists:suppliers,id',
            'tanggal'             => 'required|date',
            'jenis_pembayaran'    => 'required|in:Cash,Kredit,Transfer',
            'nomor_faktur'        => 'nullable|string|max:100',
            'tanggal_faktur'      => 'nullable|date',
            'jk_waktu'            => 'nullable|integer|min:0',
            'tanggal_jatuh_tempo' => 'nullable|date',
            'ppn'                 => 'nullable|numeric|min:0|max:100',
            'disc_nota_persen'    => 'nullable|numeric|min:0|max:100',
            'disc_nota_rupiah'    => 'nullable|numeric|min:0',
            'keterangan'          => 'nullable|string',
            'gudang'              => 'nullable|string|max:100',
            'bulan_lapor'         => 'nullable|string|max:7',
            'jenis_transaksi'     => 'nullable|in:Pembelian,PO',
        ]);

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah dikunci, header tidak bisa diubah.');
        }

        $po->update([
            'supplier_id'         => $request->supplier_id,
            'tanggal'             => $request->tanggal,
            'gudang'              => $request->gudang,
            'jenis_transaksi'     => $request->jenis_transaksi ?? 'Pembelian',
            'nomor_faktur'        => $request->nomor_faktur,
            'tanggal_faktur'      => $request->tanggal_faktur,
            'jenis_pembayaran'    => $request->jenis_pembayaran,
            'jk_waktu'            => $request->jk_waktu,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'ppn'                 => $request->ppn ?? 0,
            'disc_nota_persen'    => $request->disc_nota_persen ?? 0,
            'disc_nota_rupiah'    => $request->disc_nota_rupiah ?? 0,
            'keterangan'          => $request->keterangan,
            'bulan_lapor'         => $request->bulan_lapor,
        ]);

        // Hitung ulang total jika header berubah
        $this->recalculateTotal($po);

        return back()->with('success', 'Header PO berhasil disimpan.');
    }

    // -------------------------------------------------------
    // ADD ITEM
    // -------------------------------------------------------
    public function addItem(Request $request, $poId)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty'             => 'required|numeric|min:1',
            'price'           => 'required|numeric|min:0',
            'bonus_nama'      => 'nullable|string|max:100',
            'bonus_qty'       => 'nullable|numeric|min:0',
        ]);

        $po = PurchaseOrder::findOrFail($poId);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah dikunci, tidak bisa menambah item.');
        }

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_unit_id'   => $request->product_unit_id,
            'qty'               => $request->qty,
            'price'             => $request->price,
            'bonus_nama'        => $request->filled('bonus_nama') ? trim($request->bonus_nama) : null,
            'bonus_qty'         => $request->bonus_qty ?? 0,
        ]);

        $this->recalculateTotal($po);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // RECALCULATE TOTAL
    // -------------------------------------------------------
    private function recalculateTotal(PurchaseOrder $po): void
    {
        $po->refresh();

        $grandTotal = $po->items->sum(fn($i) => $i->qty * $i->price);

        // Diskon nota
        $diskonRp = ($grandTotal * ($po->disc_nota_persen ?? 0) / 100) + ($po->disc_nota_rupiah ?? 0);

        $ppnRp      = ($grandTotal - $diskonRp) * ($po->ppn ?? 0) / 100;
        $total      = $grandTotal - $diskonRp + $ppnRp;

        $po->update(['total' => $total]);
    }

    // -------------------------------------------------------
    // DELETE ITEM
    // -------------------------------------------------------
    public function deleteItem($id)
    {
        $item = PurchaseOrderItem::findOrFail($id);
        $po   = $item->purchaseOrder;

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah dikunci, item tidak bisa dihapus.');
        }

        $item->delete();

        $this->recalculateTotal($po);

        return back()->with('success', 'Item berhasil dihapus.');
    }

    // -------------------------------------------------------
    // APPROVE
    // -------------------------------------------------------
    public function approve($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah diproses sebelumnya.');
        }
        if ($po->items()->count() === 0) {
            return back()->with('error', 'PO belum memiliki item.');
        }
        if (empty($po->supplier_id)) {
            return back()->with('error', 'Supplier belum dipilih, isi header PO terlebih dahulu.');
        }

        $po->update(['status' => 'approved']);

        return redirect()->route('po.index')->with('success', 'PO berhasil di-approve.');
    }

    // -------------------------------------------------------
    // RECEIVE — Terima barang + update stok
    // -------------------------------------------------------
    public function receive($id)
    {
        DB::transaction(function () use ($id) {

            $po = PurchaseOrder::with('items')->findOrFail($id);

            if ($po->status !== 'approved') {
                throw new \Exception('PO belum approved.');
            }

            foreach ($po->items as $item) {
                $stock = Stock::firstOrCreate([
                    'product_unit_id' => $item->product_unit_id,
                ]);
                $stock->increment('qty', $item->qty);
            }

            $po->update(['status' => 'received']);
        });

        return redirect()->route('po.index')->with('success', 'Barang berhasil diterima & stok diperbarui.');
    }

    // -------------------------------------------------------
    // CANCEL
    // -------------------------------------------------------
    public function cancel($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO tidak bisa dibatalkan karena sudah diproses.');
        }

        $po->update(['status' => 'canceled']);

        return redirect()->route('po.index')->with('success', 'PO berhasil dibatalkan.');
    }

    // -------------------------------------------------------
    // DESTROY
    // -------------------------------------------------------
    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        // Hapus semua item terlebih dahulu
        $po->items()->delete();

        // Hapus PO
        $po->delete();

        return back()->with('success', 'PO ' . $po->po_number . ' berhasil dihapus.');
    }
}
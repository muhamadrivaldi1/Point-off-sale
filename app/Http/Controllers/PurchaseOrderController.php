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

    private function generateNumber(string $jenis): string
    {
        $prefix = $jenis === 'PO' ? 'PO' : 'PR';
        return $prefix . '-' . now()->format('YmdHis');
    }

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

        $pos = $query->latest()->paginate(10)->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.index', compact('pos', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $units = ProductUnit::with('product')->get();

        $po = PurchaseOrder::create([
            'user_id' => Auth::id(),
            'po_number' => $this->generateNumber('Pembelian'),
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'status' => 'draft',
            'jenis_transaksi' => 'Pembelian',
            'jenis_pembayaran' => 'Cash',
            'ppn' => 0,
            'total' => 0,
            'gudang' => 'Gudang Utama',
        ]);

        return view('po.create', compact('suppliers', 'units', 'po'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'po_number' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal' => 'required|date',
            'jenis_transaksi' => 'required|in:Pembelian,PO',
            'jenis_pembayaran' => 'required|in:Cash,Kredit,Transfer',
        ]);

        $po = PurchaseOrder::where('po_number', $request->po_number)
            ->where('status', 'draft')
            ->first();

        if (!$po) {

            $po = new PurchaseOrder();
            $po->user_id = Auth::id();
            $po->status = 'draft';
        }

        $po->po_number = $request->po_number;
        $po->supplier_id = $request->supplier_id;
        $po->tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');
        $po->jenis_transaksi = $request->jenis_transaksi;
        $po->jenis_pembayaran = $request->jenis_pembayaran;
        $po->gudang = $request->gudang;
        $po->ppn = $request->ppn ?? 0;

        $po->save();

        $this->recalculateTotal($po);

        return redirect()->route('po.edit', $po->id)
            ->with('success', 'Header PO berhasil disimpan.');
    }

    public function edit($id)
    {
        $po = PurchaseOrder::with('items.unit.product')->findOrFail($id);

        $units = ProductUnit::with('product')->get();

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('po.create', compact('po', 'units', 'suppliers'));
    }

    public function addItem(Request $request, $poId)
    {

        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        $po = PurchaseOrder::findOrFail($poId);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah dikunci');
        }

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_unit_id' => $request->product_unit_id,
            'qty' => $request->qty,
            'price' => $request->price
        ]);

        $this->recalculateTotal($po);

        return back()->with('success', 'Item berhasil ditambahkan');
    }

    private function recalculateTotal(PurchaseOrder $po)
    {

        $po->refresh();

        $grandTotal = $po->items->sum(function ($i) {
            return $i->qty * $i->price;
        });

        $ppnRp = $grandTotal * ($po->ppn ?? 0) / 100;

        $total = $grandTotal + $ppnRp;

        $po->update([
            'total' => $total
        ]);
    }

    public function approve($id)
    {

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO sudah diproses');
        }

        if ($po->items()->count() === 0) {
            return back()->with('error', 'PO belum memiliki item');
        }

        $po->update([
            'status' => 'approved'
        ]);

        return redirect()->route('po.index')
            ->with('success', 'PO berhasil di approve');
    }

    public function receive($id)
    {
        DB::transaction(function () use ($id) {

            $po = PurchaseOrder::with('items')->findOrFail($id);

            if ($po->status !== 'approved') {
                throw new \Exception('PO belum approved');
            }

            foreach ($po->items as $item) {

                // 1. Update stock
                $stock = Stock::firstOrCreate(
                    ['product_unit_id' => $item->product_unit_id],
                    ['qty' => 0]
                );

                $stock->increment('qty', $item->qty);

                // 2. Catat StockMutation
                $lastMutation = \App\Models\StockMutation::where('unit_id', $item->product_unit_id)
                    ->latest('created_at')
                    ->first();

                $stockBefore = $lastMutation ? $lastMutation->stock_after : 0;
                $stockAfter = $stockBefore + $item->qty;

                \App\Models\StockMutation::create([
                    'unit_id' => $item->product_unit_id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'qty' => $item->qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference' => $po->po_number,
                    'description' => 'Pembelian Barang'
                ]);
            }

            $po->update([
                'status' => 'received'
            ]);
        });

        return redirect()->route('po.index')
            ->with('success', 'Barang berhasil diterima & stok diperbarui');
    }

    public function cancel($id)
    {

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO tidak bisa dibatalkan');
        }

        $po->update([
            'status' => 'canceled'
        ]);

        return redirect()->route('po.index')
            ->with('success', 'PO berhasil dibatalkan');
    }

    public function destroy($id)
    {

        $po = PurchaseOrder::findOrFail($id);

        $po->items()->delete();

        $po->delete();

        return back()->with('success', 'PO berhasil dihapus');
    }

    public function show($id)
    {
        $po = \App\Models\PurchaseOrder::with([
            'supplier',
            'items.unit.product'
        ])->findOrFail($id);

        return view('po.show', compact('po'));
    }
    public function updateHeader(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            // tambahkan validasi lain jika perlu
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $po->update([
            'tanggal' => $request->tanggal,
            'supplier_id' => $request->supplier_id,
            'po_number' => $request->po_number,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['message' => 'Header PO berhasil diperbarui']);
    }
}

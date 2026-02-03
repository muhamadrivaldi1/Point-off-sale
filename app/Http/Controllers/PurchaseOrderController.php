<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductUnit;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class PurchaseOrderController extends Controller
{
    /* ===============================
       LIST PO
    =============================== */
    public function index()
    {
        return view('po.index', [
            'pos' => PurchaseOrder::with('user')->latest()->get()
        ]);
    }

    /* ===============================
       BUAT PO BARU (DRAFT)
    =============================== */
    public function create()
    {
        return view('po.create', [
            'units' => ProductUnit::with('product')->get()
        ]);
    }

    public function store()
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . now()->format('YmdHis'),
            'user_id' => Auth::id(),
            'status' => 'draft'
        ]);

        return redirect()->route('po.edit', $po->id);
    }

    /* ===============================
       EDIT PO
    =============================== */
    public function edit($id)
    {
        $po = PurchaseOrder::with('items.unit.product')->findOrFail($id);
        return view('po.edit', compact('po'));
    }

    /* ===============================
       TAMBAH ITEM PO
    =============================== */
    public function addItem(Request $request, $poId)
    {
        $po = PurchaseOrder::findOrFail($poId);

        if ($po->status !== 'draft') {
            abort(403, 'PO sudah dikunci');
        }

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_unit_id' => $request->product_unit_id,
            'qty' => $request->qty,
            'price' => $request->price
        ]);

        return back();
    }

    /* ===============================
       UPDATE ITEM PO
    =============================== */
    public function updateItem(Request $request, $id)
    {
        $item = PurchaseOrderItem::findOrFail($id);

        if ($item->purchaseOrder->status !== 'draft') {
            abort(403);
        }

        $item->update([
            'qty' => $request->qty,
            'price' => $request->price
        ]);

        return back();
    }

    /* ===============================
       HAPUS ITEM PO
    =============================== */
    public function deleteItem($id)
    {
        $item = PurchaseOrderItem::findOrFail($id);

        if ($item->purchaseOrder->status !== 'draft') {
            abort(403);
        }

        $item->delete();
        return back();
    }

    /* ===============================
       APPROVE PO
    =============================== */
    public function approve($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if (!in_array(Auth::user()->role, ['owner'])) {
            abort(403);
        }

        $po->update(['status' => 'approved']);

        return back()->with('success', 'PO disetujui');
    }


    /* ===============================
       TERIMA BARANG → STOK MASUK
    =============================== */
    public function receive($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);

        if ($po->status !== 'approved') {
            abort(403);
        }

        DB::transaction(function () use ($po) {
            foreach ($po->items as $item) {
                Stock::updateOrCreate(
                    [
                        'product_unit_id' => $item->product_unit_id,
                        'location' => 'gudang'
                    ],
                    [
                        'qty' => DB::raw('qty + ' . $item->qty)
                    ]
                );
            }

            $po->update(['status' => 'received']);
        });

        return back()->with('success', 'Barang masuk gudang');
    }
}

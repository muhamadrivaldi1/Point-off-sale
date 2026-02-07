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
    public function index()
    {
        $pos = PurchaseOrder::latest()->paginate(10);
        return view('po.index', compact('pos'));
    }

    public function create()
    {
        $po = PurchaseOrder::firstOrCreate(
            ['user_id' => Auth::id(), 'status' => 'draft'],
            ['po_number' => 'PO-' . now()->format('YmdHis')]
        );

        return redirect()->route('po.edit', $po->id);
    }

    public function edit($id)
    {
        $po = PurchaseOrder::with('items.unit.product')->findOrFail($id);
        $units = ProductUnit::with('product')->get();
        return view('po.edit', compact('po', 'units'));
    }

    public function cancel($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO tidak bisa dibatalkan');
        }

        $po->update(['status' => 'canceled']);

        return redirect()->route('po.index')->with('success', 'PO berhasil dibatalkan');
    }


    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return back()->with('error', 'PO tidak bisa dihapus');
        }

        $po->items()->delete();
        $po->delete();

        return back()->with('success', 'PO berhasil dihapus');
    }

    public function addItem(Request $request, $poId)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty' => 'required|integer|min:1',
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

        return back()->with('success', 'Item berhasil ditambahkan');
    }

    public function deleteItem($id)
    {
        $item = PurchaseOrderItem::findOrFail($id);

        if ($item->purchaseOrder->status !== 'draft') {
            return back()->with('error', 'PO sudah dikunci');
        }

        $item->delete();
        return back()->with('success', 'Item dihapus');
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

        return redirect()
            ->route('po.index')
            ->with('success', 'PO berhasil di-approve');
    }

    public function receive($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'approved') {
            return back()->with('error', 'PO hanya bisa diterima jika status sudah approved');
        }

        $po->update(['status' => 'received']);

        return redirect()->route('po.index')->with('success', 'PO berhasil diterima');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\ProductUnit;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /* ===============================
       LIST STOK
    =============================== */
    public function index(Request $request)
    {
        $stocks = Stock::with(['unit.product', 'warehouse'])
            ->when($request->q, function ($q) use ($request) {
                $q->whereHas('unit.product', function ($p) use ($request) {
                    $p->where('name', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->warehouse_id, function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            })
            ->latest()
            ->paginate(10);

        $warehouses = Warehouse::orderBy('name')->get();

        return view('stocks.index', compact('stocks', 'warehouses'));
    }

    /* ===============================
       FORM TAMBAH STOK
    =============================== */
    public function create()
    {
        $units = ProductUnit::with('product')
            ->orderBy('product_id')
            ->get();

        $warehouses = Warehouse::orderBy('name')->get();

        return view('stocks.create', compact('units', 'warehouses'));
    }

    /* ===============================
       SIMPAN / TAMBAH STOK
    =============================== */
    public function store(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'qty'             => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $stock = Stock::where('product_unit_id', $request->product_unit_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                $stock->increment('qty', $request->qty);
            } else {
                Stock::create([
                    'product_unit_id' => $request->product_unit_id,
                    'warehouse_id'    => $request->warehouse_id,
                    'qty'             => $request->qty,
                ]);
            }
        });

        return redirect()
            ->route('stocks.index')
            ->with('success', 'Stok berhasil ditambahkan');
    }

    /* ===============================
       FORM EDIT
    =============================== */
    public function edit($id)
    {
        $stock = Stock::findOrFail($id);

        $units = ProductUnit::with('product')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('stocks.edit', compact('stock', 'units', 'warehouses'));
    }

    /* ===============================
       UPDATE STOK
    =============================== */
    public function update(Request $request, $id)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'qty'             => 'required|integer|min:0',
        ]);

        $stock = Stock::findOrFail($id);

        $stock->update([
            'product_unit_id' => $request->product_unit_id,
            'warehouse_id'    => $request->warehouse_id,
            'qty'             => $request->qty,
        ]);

        return redirect()
            ->route('stocks.index')
            ->with('success', 'Stok berhasil diupdate');
    }

    /* ===============================
       DELETE STOK
    =============================== */
    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();

        return redirect()
            ->route('stocks.index')
            ->with('success', 'Stok berhasil dihapus');
    }

    /* ===============================
       TRANSFER GUDANG → TOKO
       (Optional jika masih dipakai)
    =============================== */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'from_warehouse'  => 'required|exists:warehouses,id',
            'to_warehouse'    => 'required|exists:warehouses,id',
            'qty'             => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $from = Stock::where('product_unit_id', $request->product_unit_id)
                ->where('warehouse_id', $request->from_warehouse)
                ->lockForUpdate()
                ->first();

            if (!$from || $from->qty < $request->qty) {
                abort(400, 'Stok tidak mencukupi');
            }

            $from->decrement('qty', $request->qty);

            $to = Stock::where('product_unit_id', $request->product_unit_id)
                ->where('warehouse_id', $request->to_warehouse)
                ->lockForUpdate()
                ->first();

            if ($to) {
                $to->increment('qty', $request->qty);
            } else {
                Stock::create([
                    'product_unit_id' => $request->product_unit_id,
                    'warehouse_id'    => $request->to_warehouse,
                    'qty'             => $request->qty,
                ]);
            }
        });

        return back()->with('success', 'Transfer stok berhasil');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\ProductUnit;
use App\Models\Warehouse;
use App\Models\StockTransfer; 
use App\Models\StockAdjustment; 
use App\Models\StockMutation; // WAJIB DIIMPORT
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

            $oldQty = $stock ? $stock->qty : 0;

            if ($stock) {
                $stock->increment('qty', $request->qty);
            } else {
                $stock = Stock::create([
                    'product_unit_id' => $request->product_unit_id,
                    'warehouse_id'    => $request->warehouse_id,
                    'qty'             => $request->qty,
                ]);
            }

            // CATAT KE HISTORY MUTASI
            StockMutation::create([
                'unit_id'      => $request->product_unit_id,
                'user_id'      => Auth::id(),
                'type'         => 'in',
                'status'       => 'pembelian', // atau 'masuk'
                'qty'          => $request->qty,
                'stock_before' => $oldQty,
                'stock_after'  => $stock->qty,
                'reference'    => 'IN-' . time(),
                'description'  => 'Penambahan stok manual'
            ]);
        });

        return redirect()
            ->route('stocks.index')
            ->with('success', 'Stok berhasil ditambahkan dan tercatat di history');
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
        MUTASI STOK (TRANSFER)
    =============================== */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'from_warehouse'  => 'required|exists:warehouses,id',
            'to_warehouse'    => 'required|exists:warehouses,id|different:from_warehouse',
            'qty'             => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $from = Stock::where('product_unit_id', $request->product_unit_id)
                    ->where('warehouse_id', $request->from_warehouse)
                    ->lockForUpdate()
                    ->first();

                if (!$from || $from->qty < $request->qty) {
                    throw new \Exception('Stok asal tidak mencukupi untuk transfer.');
                }

                $warehouseFrom = Warehouse::find($request->from_warehouse);
                $warehouseTo   = Warehouse::find($request->to_warehouse);
                $reference     = 'TRF-' . strtoupper(uniqid());

                // 1. Kurangi stok asal & CATAT HISTORY KELUAR
                $oldQtyFrom = $from->qty;
                $from->decrement('qty', $request->qty);

                StockMutation::create([
                    'unit_id'      => $request->product_unit_id,
                    'user_id'      => Auth::id(),
                    'type'         => 'out',
                    'status'       => StockMutation::STATUS_MUTASI,
                    'qty'          => $request->qty,
                    'stock_before' => $oldQtyFrom,
                    'stock_after'  => $from->qty,
                    'reference'    => $reference,
                    'description'  => "Transfer keluar ke: " . $warehouseTo->name,
                ]);

                // 2. Tambah stok tujuan & CATAT HISTORY MASUK
                $to = Stock::where('product_unit_id', $request->product_unit_id)
                    ->where('warehouse_id', $request->to_warehouse)
                    ->lockForUpdate()
                    ->first();

                $oldQtyTo = $to ? $to->qty : 0;
                
                if ($to) {
                    $to->increment('qty', $request->qty);
                } else {
                    $to = Stock::create([
                        'product_unit_id' => $request->product_unit_id,
                        'warehouse_id'    => $request->to_warehouse,
                        'qty'             => $request->qty,
                    ]);
                }

                StockMutation::create([
                    'unit_id'      => $request->product_unit_id,
                    'user_id'      => Auth::id(),
                    'type'         => 'in',
                    'status'       => StockMutation::STATUS_MUTASI,
                    'qty'          => $request->qty,
                    'stock_before' => $oldQtyTo,
                    'stock_after'  => $to->qty,
                    'reference'    => $reference,
                    'description'  => "Transfer masuk dari: " . $warehouseFrom->name,
                ]);

                // 3. Log Transfer
                StockTransfer::create([
                    'product_unit_id' => $request->product_unit_id,
                    'qty'             => $request->qty,
                    'from_location'   => $warehouseFrom->name,
                    'to_location'     => $warehouseTo->name,
                    'user_id'         => Auth::id(),
                    'reference'       => $reference,
                ]);
            });

            return back()->with('success', 'Transfer stok berhasil dan tercatat di history');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /* ===============================
        STOK OPNAME (ADJUSTMENT)
    =============================== */
    public function opname(Request $request)
    {
        $request->validate([
            'stock_id'     => 'required|exists:stocks,id',
            'physical_qty' => 'required|integer|min:0',
            'note'         => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $stock = Stock::with('warehouse')->lockForUpdate()->find($request->stock_id);
                
                if (!$stock) {
                    throw new \Exception('Data stok tidak ditemukan.');
                }

                $system_qty     = $stock->qty;
                $physical_qty   = $request->physical_qty;
                $adjustment_qty = abs($physical_qty - $system_qty);
                $type           = ($physical_qty > $system_qty) ? 'in' : 'out';

                // 1. Catat ke StockMutation (HISTORY UTAMA)
                StockMutation::create([
                    'unit_id'      => $stock->product_unit_id,
                    'user_id'      => Auth::id(),
                    'type'         => $type,
                    'status'       => StockMutation::STATUS_OPNAME,
                    'qty'          => $adjustment_qty,
                    'stock_before' => $system_qty,
                    'stock_after'  => $physical_qty,
                    'reference'    => 'OPN-' . time(),
                    'description'  => "Opname gudang " . ($stock->warehouse->name ?? '-') . ". Note: " . $request->note,
                ]);

                // 2. Catat riwayat opname ke tabel stock_adjustments
                StockAdjustment::create([
                    'product_unit_id' => $stock->product_unit_id,
                    'location'        => $stock->warehouse->name ?? 'Gudang Tidak Diketahui',
                    'system_qty'      => $system_qty,
                    'physical_qty'    => $physical_qty,
                    'adjustment_qty'  => ($physical_qty - $system_qty),
                    'note'            => $request->note,
                    'user_id'         => Auth::id(),
                ]);

                // 3. Update stok utama
                $stock->update(['qty' => $physical_qty]);
            });

            return back()->with('success', 'Stok Opname berhasil masuk ke history mutasi');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses opname: ' . $e->getMessage());
        }
    }
}
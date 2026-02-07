<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /* ===============================
       LIST STOK
    =============================== */
    public function index(Request $request)
    {
        $stocks = Stock::with('unit.product')
            ->when($request->q, function ($q) use ($request) {
                $q->whereHas('unit.product', function ($p) use ($request) {
                    $p->where('name', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->location, function ($q) use ($request) {
                $q->where('location', $request->location);
            })
            ->orderBy('location')
            ->paginate(10);

        return view('stocks.index', compact('stocks'));
    }

    /* ===============================
       FORM TAMBAH STOK
    =============================== */
    public function create()
    {
        $units = ProductUnit::with('product')
            ->orderBy('product_id')
            ->get();

        return view('stocks.create', compact('units'));
    }

    /* ===============================
       SIMPAN / TAMBAH STOK
       (AUTO UPDATE JIKA SUDAH ADA)
    =============================== */
    public function store(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'location'        => 'required|in:gudang,toko',
            'qty'             => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $stock = Stock::where('product_unit_id', $request->product_unit_id)
                ->where('location', $request->location)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                // ➕ kalau sudah ada → tambah qty
                $stock->increment('qty', $request->qty);
            } else {
                // ➕ kalau belum ada → buat baru
                Stock::create([
                    'product_unit_id' => $request->product_unit_id,
                    'location'        => $request->location,
                    'qty'             => $request->qty,
                ]);
            }
        });

        return redirect()
            ->route('stocks.index')
            ->with('success', 'Stok berhasil ditambahkan');
    }

    /* ===============================
       TRANSFER GUDANG → TOKO
    =============================== */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'qty'             => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            // 🔒 Ambil stok gudang
            $gudang = Stock::where('product_unit_id', $request->product_unit_id)
                ->where('location', 'gudang')
                ->lockForUpdate()
                ->first();

            if (!$gudang || $gudang->qty < $request->qty) {
                abort(400, 'Stok gudang tidak mencukupi');
            }

            // ➖ kurangi gudang
            $gudang->decrement('qty', $request->qty);

            // ➕ tambah stok toko
            $toko = Stock::where('product_unit_id', $request->product_unit_id)
                ->where('location', 'toko')
                ->lockForUpdate()
                ->first();

            if ($toko) {
                $toko->increment('qty', $request->qty);
            } else {
                Stock::create([
                    'product_unit_id' => $request->product_unit_id,
                    'location' => 'toko',
                    'qty' => $request->qty,
                ]);
            }
        });

        return back()->with('success', 'Transfer stok berhasil');
    }
}

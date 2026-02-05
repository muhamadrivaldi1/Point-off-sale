<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /* ===============================
       LIST STOK
    =============================== */
    public function index()
    {
        $stocks = Stock::with('unit.product')
        ->whereHas('unit')
        ->paginate(10);
        return view('stocks.index', compact('stocks'));
    }

    /* ===============================
       TRANSFER GUDANG → TOKO
    =============================== */
    public function transfer(Request $r)
    {
        $r->validate([
            'unit_id' => 'required|exists:product_units,id',
            'qty'     => 'required|numeric|min:1'
        ]);

        DB::transaction(function () use ($r) {

            $gudang = Stock::where([
                'product_unit_id' => $r->unit_id,
                'location' => 'gudang'
            ])->lockForUpdate()->first();

            if (!$gudang || $gudang->qty < $r->qty) {
                abort(400, 'Stok gudang tidak mencukupi');
            }

            // kurangi gudang
            $gudang->decrement('qty', $r->qty);

            // tambah / buat stok toko
            Stock::updateOrCreate(
                [
                    'product_unit_id' => $r->unit_id,
                    'location' => 'toko'
                ],
                [
                    'qty' => DB::raw('qty + '.$r->qty)
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Transfer stok berhasil'
        ]);
    }
}

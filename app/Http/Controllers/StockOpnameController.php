<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockTransferController extends Controller
{

    public function index()
    {
        $transfers = StockTransfer::latest()->paginate(10);

        return view('stock_transfers.index', compact('transfers'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required',
            'qty' => 'required|numeric|min:1',
            'from_location' => 'required',
            'to_location' => 'required'
        ]);

        try {

            DB::transaction(function () use ($request) {

                $unit = $request->product_unit_id;
                $qty = $request->qty;
                $reference = 'TRF-' . now()->timestamp;

                $fromStock = Stock::where('product_unit_id', $unit)
                    ->where('location', $request->from_location)
                    ->lockForUpdate()
                    ->firstOrFail();

                $toStock = Stock::where('product_unit_id', $unit)
                    ->where('location', $request->to_location)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($fromStock->qty < $qty) {
                    throw new \Exception('Stok tidak mencukupi untuk mutasi');
                }

                // stok keluar
                $beforeFrom = $fromStock->qty;

                $fromStock->qty -= $qty;
                $fromStock->save();

                $afterFrom = $fromStock->qty;

                StockMutation::create([
                    'unit_id' => $unit,
                    'user_id' => Auth::id(),
                    'type' => 'out',
                    'status' => 'mutasi',
                    'qty' => $qty,
                    'stock_before' => $beforeFrom,
                    'stock_after' => $afterFrom,
                    'reference' => $reference,
                    'description' => 'Mutasi keluar dari ' . $request->from_location
                ]);

                // stok masuk
                $beforeTo = $toStock->qty;

                $toStock->qty += $qty;
                $toStock->save();

                $afterTo = $toStock->qty;

                StockMutation::create([
                    'unit_id' => $unit,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'status' => 'mutasi',
                    'qty' => $qty,
                    'stock_before' => $beforeTo,
                    'stock_after' => $afterTo,
                    'reference' => $reference,
                    'description' => 'Mutasi masuk ke ' . $request->to_location
                ]);

                StockTransfer::create([
                    'product_unit_id' => $unit,
                    'qty' => $qty,
                    'from_location' => $request->from_location,
                    'to_location' => $request->to_location,
                    'user_id' => Auth::id(),
                    'reference' => $reference
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Mutasi barang berhasil'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        return view('stocks.index', [
            'stocks' => Stock::with('unit.product')->get()
        ]);
    }

    public function transfer(Request $r)
    {
        if ($r->qty <= 0) abort(400);

        Stock::where([
            'product_unit_id' => $r->unit_id,
            'location' => 'gudang'
        ])->decrement('qty', $r->qty);

        Stock::updateOrCreate(
            ['product_unit_id'=>$r->unit_id,'location'=>'toko'],
            ['qty'=>DB::raw("qty+$r->qty")]
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PriceRule;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

class PriceRuleController extends Controller
{
    public function index()
    {
        return view('price_rules.index', [
            'units' => ProductUnit::with('priceRules', 'product')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'min_qty' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        PriceRule::create([
            'product_unit_id' => $request->product_unit_id,
            'min_qty' => $request->min_qty,
            'price' => $request->price
        ]);

        return back()->with('success', 'Harga bertingkat ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $rule = PriceRule::findOrFail($id);

        $rule->update([
            'min_qty' => $request->min_qty,
            'price' => $request->price
        ]);

        return back()->with('success', 'Harga bertingkat diupdate');
    }

    public function destroy($id)
    {
        PriceRule::findOrFail($id)->delete();
        return back()->with('success', 'Harga bertingkat dihapus');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ProductPrice;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

class PriceRuleController extends Controller
{
    /**
     * Tampilkan halaman harga bertingkat
     */
    public function index()
    {
        $units = ProductUnit::with([
            'product',
            'priceRules' => function ($q) {
                $q->orderBy('min_qty');
            }
        ])->get();

        return view('master.price_rules.index', compact('units'));
    }

    /**
     * Simpan harga bertingkat baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_id'    => 'required|exists:product_units,id',
            'price_type' => 'required|in:retail,wholesale,member',
            'min_qty'    => 'required|integer|min:1',
            'price'      => 'required|numeric|min:0',
        ]);

        $unit = ProductUnit::findOrFail($request->unit_id);

        ProductPrice::create([
            'unit_id'    => $unit->id,
            'product_id' => $unit->product_id,
            'price_type' => $request->price_type,
            'min_qty'    => $request->min_qty,
            'price'      => $request->price,
        ]);

        return back()->with('success', 'Harga bertingkat berhasil ditambahkan');
    }

    /**
     * Update harga bertingkat
     */
    public function update(Request $request, $id)
    {
        $rule = ProductPrice::findOrFail($id);

        $request->validate([
            'price_type' => 'required|in:retail,wholesale,member',
            'min_qty'    => 'required|integer|min:1',
            'price'      => 'required|numeric|min:0',
        ]);

        $rule->update([
            'price_type' => $request->price_type,
            'min_qty'    => $request->min_qty,
            'price'      => $request->price,
        ]);

        return back()->with('success', 'Harga bertingkat berhasil diperbarui');
    }

    /**
     * Hapus harga bertingkat
     */
    public function destroy($id)
    {
        ProductPrice::findOrFail($id)->delete();

        return back()->with('success', 'Harga bertingkat berhasil dihapus');
    }
}

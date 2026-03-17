<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductUnit;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('units');

        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->q}%")
                  ->orWhere('sku', 'like', "%{$request->q}%");
        }

        $products = $query->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $this->validateProduct($request);

        $product = Product::create([
            'name'   => $request->name,
            'sku'    => $request->sku,
            'is_bkp' => $request->is_bkp ?? 0
        ]);

        if (!empty($request->units)) {
            $this->saveUnits($product, $request->units);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil disimpan');
    }

    public function edit(Product $product)
    {
        $product->load('units');
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->validateProduct($request);

        $product->update([
            'name'   => $request->name,
            'sku'    => $request->sku,
            'is_bkp' => $request->is_bkp ?? 0
        ]);

        $product->units()->delete();

        if (!empty($request->units)) {
            $this->saveUnits($product, $request->units);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Product $product)
    {
        $product->units()->delete();
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }

    private function validateProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku'  => 'nullable|string|max:100',
            'units' => 'required|array|min:1',
            'units.*.name'       => 'required|string|max:50',
            'units.*.conversion' => 'required|numeric|min:1',
            'units.*.price'      => 'required|numeric|min:0',
            'units.*.barcode'    => 'nullable|string|max:50',
        ]);
    }

    private function saveUnits(Product $product, array $units)
    {
        foreach ($units as $unit) {
            ProductUnit::create([
                'product_id' => $product->id,
                'unit_name'  => $unit['name'],
                'conversion' => $unit['conversion'],
                'barcode'    => $unit['barcode'] ?? null,
                'price'      => $unit['price'],
            ]);
        }
    }
}
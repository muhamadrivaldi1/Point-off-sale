<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('units');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('sku', 'like', "%{$request->q}%");
            });
        }

        $products = $query->latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $this->validateProduct($request);

        DB::beginTransaction();

        try {
            $product = Product::create([
                'name'       => $request->name,
                'sku'        => $request->sku,
                'is_bkp'     => $request->is_bkp ?? 0,
                'min_stock'  => $request->min_stock, // ✅ tambahan
            ]);

            $this->saveUnits($product, $request->units);

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors('Gagal menyimpan produk: ' . $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        $product->load('units');
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->validateProduct($request);

        DB::beginTransaction();

        try {
            $product->update([
                'name'       => $request->name,
                'sku'        => $request->sku,
                'is_bkp'     => $request->is_bkp ?? 0,
                'min_stock'  => $request->min_stock, // ✅ tambahan
            ]);

            // Hapus unit lama (simple & aman)
            $product->units()->delete();

            // Simpan ulang unit
            $this->saveUnits($product, $request->units);

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors('Gagal update produk: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();

        try {
            $product->units()->delete();
            $product->delete();

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors('Gagal hapus produk');
        }
    }

    private function validateProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku'  => 'nullable|string|max:100',

            // ✅ tambahan stok minimal
            'min_stock' => 'required|integer|min:0',

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
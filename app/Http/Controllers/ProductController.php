<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductUnit;

class ProductController extends Controller
{
    /* ===============================
       TAMPILAN LIST PRODUK
    =============================== */
    public function index()
    {
        $products = Product::with('units')->get();
        return view('products.index', compact('products'));
    }

    /* ===============================
       FORM TAMBAH PRODUK
    =============================== */
    public function create()
    {
        return view('products.create');
    }

    /* ===============================
       SIMPAN PRODUK BARU
    =============================== */
    public function store(Request $r)
    {
        $this->validateProduct($r);

        $product = Product::create([
            'name'   => $r->name,
            'sku'    => $r->sku,
            'is_bkp' => $r->is_bkp ?? 0
        ]);

        $this->saveUnits($product, $r->units);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil disimpan');
    }

    /* ===============================
       FORM EDIT PRODUK
    =============================== */
    public function edit(Product $product)
    {
        $product->load('units');
        return view('products.edit', compact('product'));
    }

    /* ===============================
       UPDATE PRODUK
    =============================== */
    public function update(Request $r, Product $product)
    {
        $this->validateProduct($r);

        $product->update([
            'name'   => $r->name,
            'sku'    => $r->sku,
            'is_bkp' => $r->is_bkp ?? 0
        ]);

        // reset unit
        $product->units()->delete();

        $this->saveUnits($product, $r->units);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui');
    }

    /* ===============================
       HAPUS PRODUK
    =============================== */
    public function destroy(Product $product)
    {
        $product->units()->delete();
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }

    /* ===============================
       VALIDASI
    =============================== */
    private function validateProduct(Request $r)
    {
        $r->validate([
            'name' => 'required|string',
            'sku'  => 'nullable|string',
            'units' => 'required|array|min:1',
            'units.*.name'       => 'required|string',
            'units.*.conversion' => 'required|numeric|min:1',
            'units.*.price'      => 'required|numeric|min:0'
        ]);
    }

    /* ===============================
       SIMPAN UNIT
    =============================== */
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

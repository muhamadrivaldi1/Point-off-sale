<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductUnit;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index', [
            'products' => Product::with('units')->get()
        ]);
    }

    public function store(Request $r)
    {
        $product = Product::create($r->only('name','sku','is_bkp'));

        foreach ($r->units as $unit) {
            ProductUnit::create([
                'product_id' => $product->id,
                'unit_name' => $unit['name'],
                'conversion' => $unit['conversion'],
                'barcode' => $unit['barcode'],
                'price' => $unit['price'],
            ]);
        }

        return back();
    }
}

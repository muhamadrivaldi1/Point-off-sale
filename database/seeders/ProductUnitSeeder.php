<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductUnit;
use App\Models\Product;

class ProductUnitSeeder extends Seeder
{
    public function run(): void
    {
        $roti = Product::where('sku','RTS-001')->first();
        $rokok = Product::where('sku','RKF-001')->first();

        ProductUnit::insert([
            [
                'product_id' => $roti->id,
                'unit_name' => 'PCS',
                'conversion' => 1,
                'barcode' => '111111',
                'price' => 5000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => $rokok->id,
                'unit_name' => 'PCS',
                'conversion' => 1,
                'barcode' => '222222',
                'price' => 25000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

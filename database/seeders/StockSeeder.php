<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductUnit;
use App\Models\Stock;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $units = ProductUnit::all();

        foreach ($units as $unit) {
            Stock::insert([
                [
                    'product_unit_id' => $unit->id,
                    'qty' => 100,
                    'location' => 'gudang',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_unit_id' => $unit->id,
                    'qty' => 50,
                    'location' => 'toko',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductUnit;
use App\Models\PriceRule;

class PriceRuleSeeder extends Seeder
{
    public function run(): void
    {
        $roti = ProductUnit::where('barcode', '111111')->first();

        if (!$roti) {
            return; // AMAN: kalau belum ada, skip
        }

        PriceRule::insert([
            [
                'product_unit_id' => $roti->id,
                'min_qty' => 5,
                'price' => 4500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_unit_id' => $roti->id,
                'min_qty' => 10,
                'price' => 4000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

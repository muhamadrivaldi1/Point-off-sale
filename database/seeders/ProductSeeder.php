<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        Product::insert([
            [
                'name' => 'Roti Sobek',
                'sku' => 'RTS-001',
                'is_bkp' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rokok Filter',
                'sku' => 'RKF-001',
                'is_bkp' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

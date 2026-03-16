<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Warehouse::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            [
                'name'      => 'Gudang Utama (Pusat)',
                'code'      => 'WH-CENTRAL',
                'is_active' => true, // Set satu gudang aktif secara default
            ],
            [
                'name'      => 'Gudang Cadangan (Transit)',
                'code'      => 'WH-TRANSIT',
                'is_active' => false,
            ],
            [
                'name'      => 'Gudang Retur Barang',
                'code'      => 'WH-RETURN',
                'is_active' => false,
            ],
        ];

        foreach ($data as $item) {
            Warehouse::create($item);
        }
    }
}
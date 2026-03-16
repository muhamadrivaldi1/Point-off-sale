<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductUnit;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan data lama agar tidak menumpuk
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Stock::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Ambil semua unit produk
        $units = ProductUnit::all();

        // 3. Ambil ID Gudang Utama dari WarehouseSeeder
        $mainWarehouse = Warehouse::where('code', 'WH-CENTRAL')->first();
        
        // Jika gudang utama tidak ditemukan, ambil gudang pertama yang ada
        $warehouseId = $mainWarehouse ? $mainWarehouse->id : 1;

        if ($units->isEmpty()) {
            $this->command->error("Gagal: Tidak ada data di product_units. Jalankan ProductUnitSeeder dulu!");
            return;
        }

        // 4. Loop untuk mengisi stok
        foreach ($units as $unit) {
            Stock::insert([
                [
                    'product_unit_id' => $unit->id,
                    'qty'             => 100,
                    // Sekarang menggunakan ID dari tabel warehouses
                    'warehouse_id'    => $warehouseId, 
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            ]);
        }

        $this->command->info("StockSeeder BERHASIL! Stok telah dimasukkan ke Gudang Utama.");
    }
}
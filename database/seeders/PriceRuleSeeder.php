<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;

class PriceRuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan tabel tujuan agar tidak ada data ganda
        // Disable foreign key checks sebentar jika truncate bermasalah
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_prices')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Ambil data dari product_units (Roti Sobek atau data pertama)
        $pu = ProductUnit::where('barcode', '111111')->first();

        if (!$pu) {
            $pu = ProductUnit::first();
        }

        if ($pu) {
            // 3. Masukkan data ke product_prices
            // Pastikan price_type menggunakan nilai ENUM yang diizinkan database kamu
            DB::table('product_prices')->insert([
                [
                    'product_id' => $pu->product_id, 
                    'unit_id'    => $pu->id, 
                    'price_type' => 'wholesale', // Nilai ENUM yang valid (untuk Grosir)
                    'min_qty'    => 5,
                    'price'      => 4500,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_id' => $pu->product_id,
                    'unit_id'    => $pu->id,
                    'price_type' => 'member',    // Nilai ENUM yang valid (untuk Member)
                    'min_qty'    => 10,
                    'price'      => 4000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $this->command->info("PriceRuleSeeder BERHASIL! Data masuk untuk Product ID: " . $pu->product_id);
        } else {
            $this->command->error("Gagal: Tabel product_units masih kosong! Harap jalankan ProductSeeder/ProductUnitSeeder dulu.");
        }
    }
}
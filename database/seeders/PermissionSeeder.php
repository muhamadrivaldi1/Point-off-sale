<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'akses_pos', 
            'akses_transaksi', 
            'akses_produk', 
            'akses_supplier', 
            'akses_member', 
            'kelola_user', 
            'akses_stok', 
            'akses_pembelian', 
            'akses_sesi_kasir', 
            'akses_retur', 
            'akses_laporan',
            'akses_gudang'
        ];

        foreach ($permissions as $perm) {
            // firstOrCreate digunakan agar tidak terjadi error duplicate jika data sudah ada
            Permission::firstOrCreate(['name' => $perm]);
        }
    }
}
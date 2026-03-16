<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel sebelum seeding untuk menghindari duplikat kode_supplier
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Supplier::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $suppliers = [
            [
                'kode_supplier'  => 'SUP001',
                'nama_supplier'  => 'PT. Maju Bersama Jaya',
                'npwp'           => '01.234.567.8-001.000',
                'alamat'         => 'Jl. Industri No. 12, Jakarta Barat',
                'telepon'        => '021-5551234',
                'email'          => 'sales@majubersama.com',
                'bank'           => 'BCA',
                'nomor_rekening' => '8800123456',
                'cp'             => 'Bapak Budi',
                'jabatan_cp'     => 'Sales Manager',
                'telepon_cp'     => '08123456789',
            ],
            [
                'kode_supplier'  => 'SUP002',
                'nama_supplier'  => 'CV. Sembako Sejahtera',
                'npwp'           => '02.987.654.3-002.000',
                'alamat'         => 'Kawasan Pergudangan Blok C, Tangerang',
                'telepon'        => '021-7778899',
                'email'          => 'info@sembakosejahtera.id',
                'bank'           => 'Mandiri',
                'nomor_rekening' => '1230009988776',
                'cp'             => 'Ibu Susi',
                'jabatan_cp'     => 'Admin Gudang',
                'telepon_cp'     => '08567890123',
            ],
            [
                'kode_supplier'  => 'SUP003',
                'nama_supplier'  => 'PT. Pangan Nusantara',
                'npwp'           => '03.111.222.3-003.000',
                'alamat'         => 'Kawasan Industri Jababeka, Bekasi',
                'telepon'        => '021-89894455',
                'email'          => 'contact@pangannusantara.co.id',
                'bank'           => 'BNI',
                'nomor_rekening' => '0098765432',
                'cp'             => 'Andi Prasetyo',
                'jabatan_cp'     => 'Distributor Manager',
                'telepon_cp'     => '081122334455',
            ],
            [
                'kode_supplier'  => 'SUP004',
                'nama_supplier'  => 'PT ABCD', // Nama supplier yang ada di screenshot Anda
                'npwp'           => '04.444.555.6-004.000',
                'alamat'         => 'Jl. Raya Bogor KM 25',
                'telepon'        => '021-1234567',
                'email'          => 'admin@ptabcd.com',
                'bank'           => 'BRI',
                'nomor_rekening' => '00112233445566',
                'cp'             => 'Eko',
                'jabatan_cp'     => 'Staff Logistik',
                'telepon_cp'     => '087766554433',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
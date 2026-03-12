<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key check agar truncate tidak error
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('accounts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Cek kolom mana saja yang ada di tabel
        $hasDescription    = Schema::hasColumn('accounts', 'description');
        $hasNormalBalance  = Schema::hasColumn('accounts', 'normal_balance');
        $hasIsActive       = Schema::hasColumn('accounts', 'is_active');

        $now      = now();
        $accounts = [];

        // ──────────────────────────────────────────────────────
        //  1. ASET (normal: DEBIT)
        // ──────────────────────────────────────────────────────
        $accounts[] = ['code' => '1-1001', 'name' => 'Kas Tunai',                  'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Uang tunai di laci kasir (cash in hand)'];
        $accounts[] = ['code' => '1-1002', 'name' => 'Kas Bank',                   'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Saldo rekening bank (transfer masuk)'];
        $accounts[] = ['code' => '1-1003', 'name' => 'Kas QRIS',                   'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Saldo QRIS / dompet digital'];
        $accounts[] = ['code' => '1-2001', 'name' => 'Piutang Usaha',              'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Tagihan kepada pelanggan dari penjualan kredit'];
        $accounts[] = ['code' => '1-2002', 'name' => 'Piutang Lain-Lain',          'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Piutang selain dari penjualan'];
        $accounts[] = ['code' => '1-2010', 'name' => 'Uang Muka (DP) Diterima',   'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'DP dari pelanggan kredit, mengurangi piutang'];
        $accounts[] = ['code' => '1-3001', 'name' => 'Persediaan Barang Dagang',   'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Stok barang yang ada di gudang / toko'];
        $accounts[] = ['code' => '1-4001', 'name' => 'Perlengkapan Toko',          'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Perlengkapan habis pakai (tas, kertas struk, dll)'];
        $accounts[] = ['code' => '1-4002', 'name' => 'Peralatan Toko',             'type' => 'asset',     'normal_balance' => 'debit',  'description' => 'Mesin kasir, scanner, display, rak, dll'];

        // ──────────────────────────────────────────────────────
        //  2. KEWAJIBAN / HUTANG (normal: KREDIT)
        // ──────────────────────────────────────────────────────
        $accounts[] = ['code' => '2-1001', 'name' => 'Hutang Dagang',              'type' => 'liability', 'normal_balance' => 'kredit', 'description' => 'Kewajiban kepada supplier dari pembelian kredit'];
        $accounts[] = ['code' => '2-1002', 'name' => 'Hutang Lain-Lain',           'type' => 'liability', 'normal_balance' => 'kredit', 'description' => 'Kewajiban non-dagang jangka pendek'];

        // ──────────────────────────────────────────────────────
        //  3. EKUITAS / MODAL (normal: KREDIT)
        // ──────────────────────────────────────────────────────
        $accounts[] = ['code' => '3-1001', 'name' => 'Modal Pemilik',              'type' => 'equity',    'normal_balance' => 'kredit', 'description' => 'Modal awal / investasi pemilik'];
        $accounts[] = ['code' => '3-1002', 'name' => 'Laba Ditahan',               'type' => 'equity',    'normal_balance' => 'kredit', 'description' => 'Akumulasi laba/rugi tahun-tahun sebelumnya'];
        $accounts[] = ['code' => '3-1003', 'name' => 'Prive (Pengambilan Pemilik)','type' => 'equity',    'normal_balance' => 'debit',  'description' => 'Penarikan kas oleh pemilik (kontra ekuitas)'];

        // ──────────────────────────────────────────────────────
        //  4. PENDAPATAN (normal: KREDIT)
        // ──────────────────────────────────────────────────────
        $accounts[] = ['code' => '4-1001', 'name' => 'Pendapatan Penjualan',       'type' => 'income',    'normal_balance' => 'kredit', 'description' => 'Omzet dari penjualan barang (cash, transfer, QRIS, kredit)'];
        $accounts[] = ['code' => '4-1002', 'name' => 'Pendapatan Jasa Tagihan',    'type' => 'income',    'normal_balance' => 'kredit', 'description' => 'Biaya admin/jasa dari pembayaran tagihan'];
        $accounts[] = ['code' => '4-1003', 'name' => 'Diskon Pembelian Diterima',  'type' => 'income',    'normal_balance' => 'kredit', 'description' => 'Potongan harga dari supplier saat membeli'];
        $accounts[] = ['code' => '4-1004', 'name' => 'Pendapatan Lain-Lain',       'type' => 'income',    'normal_balance' => 'kredit', 'description' => 'Pendapatan di luar kegiatan utama toko'];
        $accounts[] = ['code' => '4-9001', 'name' => 'Retur & Potongan Penjualan', 'type' => 'income',    'normal_balance' => 'debit',  'description' => 'Kontra pendapatan: retur atau diskon ke pelanggan'];

        // ──────────────────────────────────────────────────────
        //  5. HPP / COGS (normal: DEBIT)
        // ──────────────────────────────────────────────────────
        $accounts[] = ['code' => '5-1001', 'name' => 'Harga Pokok Penjualan (HPP)','type' => 'cogs',      'normal_balance' => 'debit',  'description' => 'Biaya pokok barang yang terjual'];
        $accounts[] = ['code' => '5-1002', 'name' => 'Pembelian Barang Dagang',    'type' => 'cogs',      'normal_balance' => 'debit',  'description' => 'Total nilai pembelian stok dari supplier'];
        $accounts[] = ['code' => '5-1003', 'name' => 'Biaya Angkut Pembelian',     'type' => 'cogs',      'normal_balance' => 'debit',  'description' => 'Ongkir/biaya pengiriman saat membeli dari supplier'];
        $accounts[] = ['code' => '5-9001', 'name' => 'Retur Pembelian',            'type' => 'cogs',      'normal_balance' => 'kredit', 'description' => 'Kontra HPP: barang dikembalikan ke supplier'];

        // ──────────────────────────────────────────────────────
        //  6. BEBAN / BIAYA OPERASIONAL (normal: DEBIT)
        // ──────────────────────────────────────────────────────
        // 6-1xxx Tagihan Utilitas — sinkron dengan fitur Bayar Tagihan POS
        $accounts[] = ['code' => '6-1001', 'name' => 'Beban Listrik',              'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Tagihan listrik toko / kantor'];
        $accounts[] = ['code' => '6-1002', 'name' => 'Beban Air',                  'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Tagihan air / PDAM'];
        $accounts[] = ['code' => '6-1003', 'name' => 'Beban Internet',             'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Tagihan internet / WiFi toko'];
        $accounts[] = ['code' => '6-1004', 'name' => 'Beban Telepon',              'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Tagihan telepon kabel / pulsa operasional'];
        $accounts[] = ['code' => '6-1005', 'name' => 'Beban BPJS',                 'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Iuran BPJS Kesehatan / Ketenagakerjaan'];
        $accounts[] = ['code' => '6-1006', 'name' => 'Beban Gas',                  'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Tagihan gas LPG / PGN'];
        $accounts[] = ['code' => '6-1007', 'name' => 'Beban TV Kabel',             'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Tagihan TV kabel / berlangganan streaming'];
        $accounts[] = ['code' => '6-1099', 'name' => 'Beban Tagihan Lain-Lain',    'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Tagihan selain kategori di atas (iuran RT, sewa titik, dll)'];
        // 6-2xxx SDM
        $accounts[] = ['code' => '6-2001', 'name' => 'Beban Gaji & Upah',          'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Gaji karyawan dan upah harian'];
        // 6-3xxx Tempat
        $accounts[] = ['code' => '6-3001', 'name' => 'Beban Sewa Tempat',          'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Sewa toko / ruko / lapak'];
        // 6-4xxx Operasional Umum
        $accounts[] = ['code' => '6-4001', 'name' => 'Beban Perlengkapan',         'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Konsumsi perlengkapan habis pakai'];
        $accounts[] = ['code' => '6-4002', 'name' => 'Beban Pemeliharaan & Perbaikan','type' => 'expense','normal_balance' => 'debit',  'description' => 'Biaya service, reparasi peralatan toko'];
        $accounts[] = ['code' => '6-4003', 'name' => 'Beban Transportasi & Pengiriman','type' => 'expense','normal_balance' => 'debit', 'description' => 'Ongkos kirim ke pelanggan, biaya bensin operasional'];
        $accounts[] = ['code' => '6-4004', 'name' => 'Beban Administrasi & ATK',   'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Alat tulis, kertas, biaya admin bank'];
        $accounts[] = ['code' => '6-4005', 'name' => 'Beban Promosi & Iklan',      'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Biaya pemasaran, cetak spanduk, iklan media sosial'];
        $accounts[] = ['code' => '6-9001', 'name' => 'Beban Lain-Lain',            'type' => 'expense',   'normal_balance' => 'debit',  'description' => 'Pengeluaran operasional di luar kategori di atas'];

        // ──────────────────────────────────────────────────────
        //  Bersihkan kolom yang tidak ada di tabel, lalu insert
        // ──────────────────────────────────────────────────────
        $rows = array_map(function ($a) use ($hasDescription, $hasNormalBalance, $hasIsActive, $now) {
            $row = [
                'code'       => $a['code'],
                'name'       => $a['name'],
                'type'       => $a['type'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasNormalBalance) {
                $row['normal_balance'] = $a['normal_balance'];
            }
            if ($hasDescription) {
                $row['description'] = $a['description'];
            }
            if ($hasIsActive) {
                $row['is_active'] = true;
            }

            return $row;
        }, $accounts);

        DB::table('accounts')->insert($rows);

        $this->command->info('✅ AccountSeeder: ' . count($rows) . ' akun berhasil dibuat.');

        if (!$hasDescription || !$hasNormalBalance || !$hasIsActive) {
            $this->command->warn('⚠️  Beberapa kolom belum ada di tabel accounts.');
            $this->command->warn('   Jalankan migration dulu: php artisan migrate');
            $this->command->warn('   Lalu jalankan ulang seeder ini.');
        }

        $this->printJournalMap();
    }

    private function printJournalMap(): void
    {
        $map = [
            '─────────────────────────────────────────────────────────────────',
            '  PANDUAN PEMETAAN JURNAL OTOMATIS — POS SYSTEM',
            '─────────────────────────────────────────────────────────────────',
            '',
            '1. PENJUALAN TUNAI (cash / transfer / QRIS)',
            '      D  1-1001 / 1-1002 / 1-1003   Kas (sesuai metode bayar)',
            '      K  4-1001                      Pendapatan Penjualan',
            '',
            '2. PENJUALAN KREDIT (status = kredit)',
            '   a) Saat transaksi disimpan:',
            '      D  1-2001                      Piutang Usaha',
            '      K  4-1001                      Pendapatan Penjualan',
            '   b) Bila ada DP:',
            '      D  1-1001/1002/1003             Kas (sesuai metode DP)',
            '      K  1-2001                      Piutang Usaha',
            '   c) Pelunasan / cicilan:',
            '      D  1-1001/1002/1003             Kas',
            '      K  1-2001                      Piutang Usaha',
            '',
            '3. BAYAR TAGIHAN (status = bayar_tagihan)',
            '      D  6-1001..6-1099               Beban (sesuai kategori)',
            '      K  1-1001/1002/1003             Kas (sesuai metode bayar)',
            '',
            '4. PEMBELIAN TUNAI',
            '      D  5-1002                      Pembelian Barang Dagang',
            '      K  1-1001/1002/1003             Kas',
            '',
            '5. PEMBELIAN KREDIT',
            '   a) Saat nota dibuat:',
            '      D  5-1002                      Pembelian Barang Dagang',
            '      K  2-1001                      Hutang Dagang',
            '   b) Bayar nota (full/DP/cicilan):',
            '      D  2-1001                      Hutang Dagang',
            '      K  1-1001/1002/1003             Kas',
            '',
            '─────────────────────────────────────────────────────────────────',
            '  KAS KELUAR  → KREDIT Kas, DEBIT akun lawan (beban/hutang/dll)',
            '  KAS MASUK   → DEBIT  Kas, KREDIT akun lawan (pendapatan/dll)',
            '─────────────────────────────────────────────────────────────────',
        ];

        foreach ($map as $line) {
            $this->command->line($line);
        }
    }
}
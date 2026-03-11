<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cek dulu, kalau kolom BELUM ada baru buat. Kalau sudah ada, skip bagian ini.
        if (!Schema::hasColumn('stock_mutations', 'status')) {
            Schema::table('stock_mutations', function (Blueprint $table) {
                $table->string('status')->nullable()->after('type');
                $table->index('status');
            });
        }

        // 2. Jalankan pengisian data (ini tetap aman dijalankan meski kolom sudah ada)
        DB::statement("UPDATE stock_mutations SET status = 'penjualan' WHERE description LIKE '%Penjualan%'");
        DB::statement("UPDATE stock_mutations SET status = 'pembelian' WHERE description LIKE '%Pembelian%'");
        DB::statement("UPDATE stock_mutations SET status = 'opname' WHERE description LIKE '%Opname%'");
        DB::statement("UPDATE stock_mutations SET status = 'retur_pembelian' WHERE description LIKE '%Retur Pembelian%'");
        DB::statement("UPDATE stock_mutations SET status = 'retur_penjualan' WHERE description LIKE '%Retur Penjualan%'");
        DB::statement("UPDATE stock_mutations SET status = 'mutasi' WHERE description LIKE '%Mutasi%'");
        
        // 3. Isi sisa yang masih NULL dengan nilai 'type' (in/out)
        DB::statement("UPDATE stock_mutations SET status = type WHERE status IS NULL");
    }

    public function down(): void
    {
        if (Schema::hasColumn('stock_mutations', 'status')) {
            Schema::table('stock_mutations', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
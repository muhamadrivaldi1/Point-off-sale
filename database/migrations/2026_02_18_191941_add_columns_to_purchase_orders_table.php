<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('purchase_orders', function (Blueprint $table) {
        $table->foreignId('supplier_id')->nullable()->after('user_id')->constrained('suppliers')->nullOnDelete();
        $table->date('tanggal')->nullable()->after('supplier_id');
        $table->string('nomor_faktur')->nullable()->after('tanggal');
        $table->date('tanggal_faktur')->nullable()->after('nomor_faktur');
        $table->enum('jenis_pembayaran', ['Cash','Kredit','Transfer'])->default('Cash')->after('tanggal_faktur');
        $table->integer('jk_waktu')->nullable()->after('jenis_pembayaran');
        $table->date('tanggal_jatuh_tempo')->nullable()->after('jk_waktu');
        $table->decimal('ppn', 5, 2)->default(0)->after('tanggal_jatuh_tempo');
        $table->decimal('disc_nota_persen', 5, 2)->default(0)->after('ppn');
        $table->decimal('disc_nota_rupiah', 15, 2)->default(0)->after('disc_nota_persen');
        $table->decimal('total', 15, 2)->default(0)->after('disc_nota_rupiah');
        $table->text('keterangan')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('purchase_orders', function (Blueprint $table) {
        $table->dropForeign(['supplier_id']);
        $table->dropColumn([
            'supplier_id', 'tanggal', 'nomor_faktur', 'tanggal_faktur',
            'jenis_pembayaran', 'jk_waktu', 'tanggal_jatuh_tempo',
            'ppn', 'disc_nota_persen', 'disc_nota_rupiah', 'total', 'keterangan'
        ]);
    });
    }
};

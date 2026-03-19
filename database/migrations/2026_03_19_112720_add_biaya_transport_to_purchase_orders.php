<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Hanya tambah jika belum ada
            if (!Schema::hasColumn('purchase_orders', 'biaya_transport')) {
                $table->decimal('biaya_transport', 15, 2)->default(0)->after('disc_nota_rupiah');
            }
            if (!Schema::hasColumn('purchase_orders', 'total_hna')) {
                $table->decimal('total_hna', 15, 2)->default(0)->after('biaya_transport');
            }
            if (!Schema::hasColumn('purchase_orders', 'total_disk_brg')) {
                $table->decimal('total_disk_brg', 15, 2)->default(0)->after('total_hna');
            }
            if (!Schema::hasColumn('purchase_orders', 'total_netto')) {
                $table->decimal('total_netto', 15, 2)->default(0)->after('total_disk_brg');
            }
        });
    }
 
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['biaya_transport', 'total_hna', 'total_disk_brg', 'total_netto']);
        });
    }
};
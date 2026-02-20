<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Hanya tambah jika belum ada
            if (!Schema::hasColumn('stocks', 'warehouse_id')) {
                $table->foreignId('warehouse_id')
                      ->nullable()
                      ->after('product_unit_id')
                      ->constrained('warehouses')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (Schema::hasColumn('stocks', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
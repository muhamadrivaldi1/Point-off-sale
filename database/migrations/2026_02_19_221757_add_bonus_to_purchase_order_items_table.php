<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('bonus_unit_id')
                  ->nullable()
                  ->after('product_unit_id')
                  ->constrained('product_units')
                  ->nullOnDelete();

            $table->decimal('bonus_qty', 15, 2)
                  ->default(0)
                  ->after('bonus_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['bonus_unit_id']);
            $table->dropColumn(['bonus_unit_id','bonus_qty']);
        });
    }
};

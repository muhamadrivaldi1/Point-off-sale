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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_unit_id')->constrained();
            $table->string('location');
            $table->integer('system_qty'); // Stok di komputer
            $table->integer('physical_qty'); // Stok asli hasil hitung manual
            $table->integer('adjustment_qty'); // Selisihnya (+ atau -)
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};

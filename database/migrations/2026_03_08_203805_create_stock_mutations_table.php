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
    Schema::create('stock_mutations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('unit_id')->constrained('product_units')->onDelete('cascade');
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->enum('type', ['in', 'out', 'adjustment']); // Masuk, Keluar, Penyesuaian
        $table->integer('qty');
        $table->integer('stock_before');
        $table->integer('stock_after');
        $table->string('reference')->nullable(); // Contoh: No. Invoice atau No. Nota
        $table->string('description')->nullable(); // Keterangan tambahan
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};

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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique(); // Contoh: 260302.02.01.056
            $table->foreignId('user_id')->constrained(); // Kasir
            $table->decimal('total_nilai', 15, 2);
            $table->enum('cara_bayar', ['Tunai', 'Kredit']);
            $table->string('nama_customer')->default('UMUM');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

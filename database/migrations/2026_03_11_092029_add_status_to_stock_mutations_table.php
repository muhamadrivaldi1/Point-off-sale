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
        Schema::table('stock_mutations', function (Blueprint $table) {
            // Menambahkan kolom status untuk kategori mutasi
            $table->string('status')->nullable()->after('type');
            // Index agar pencarian filter nanti cepat
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            //
        });
    }
};

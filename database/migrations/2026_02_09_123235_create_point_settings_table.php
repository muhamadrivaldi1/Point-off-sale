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
        Schema::create('point_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // BKP, Non-BKP, Makanan, dll
            $table->decimal('point_per_rupiah', 10, 4); // misal 0.001 point per 1 rupiah
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('struk_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko')->default('NAMA TOKO');
            $table->string('tagline')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('npwp')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('footer_text')->nullable();
            $table->boolean('tampil_npwp')->default(true);
            $table->boolean('tampil_member')->default(true);
            $table->boolean('tampil_poin')->default(true);
            $table->boolean('tampil_footer_ttd')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struk_settings');
    }
};
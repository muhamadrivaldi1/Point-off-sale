<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::table('suppliers', function (Blueprint $table) {
            $table->string('npwp')->nullable();
            $table->string('telepon2')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('bank')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('cp')->nullable();
            $table->string('jabatan_cp')->nullable();
            $table->string('telepon_cp')->nullable();
            $table->string('nomor_seri_fp')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'npwp',
                'telepon2',
                'fax',
                'email',
                'nama_rekening',
                'bank',
                'nomor_rekening',
                'cp',
                'jabatan_cp',
                'telepon_cp',
                'nomor_seri_fp'
            ]);
        });
    }
};
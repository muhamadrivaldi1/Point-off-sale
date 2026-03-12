<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {

            // Tambah kolom jika belum ada
            if (!Schema::hasColumn('accounts', 'normal_balance')) {
                $table->enum('normal_balance', ['debit', 'kredit'])
                    ->default('debit')
                    ->after('type')
                    ->comment('Saldo normal akun: debit atau kredit');
            }

            if (!Schema::hasColumn('accounts', 'description')) {
                $table->string('description', 255)
                    ->nullable()
                    ->after('normal_balance')
                    ->comment('Keterangan singkat akun');
            }

            if (!Schema::hasColumn('accounts', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('description')
                    ->comment('Status aktif akun');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['normal_balance', 'description', 'is_active']);
        });
    }
};

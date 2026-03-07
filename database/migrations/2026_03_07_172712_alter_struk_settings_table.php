<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struk_settings', function (Blueprint $table) {
            // Tambah kolom baru jika belum ada
            if (!Schema::hasColumn('struk_settings', 'tagline'))
                $table->string('tagline')->nullable()->after('nama_toko');

            if (!Schema::hasColumn('struk_settings', 'email'))
                $table->string('email')->nullable()->after('telepon');

            if (!Schema::hasColumn('struk_settings', 'website'))
                $table->string('website')->nullable()->after('email');

            if (!Schema::hasColumn('struk_settings', 'footer_text'))
                $table->text('footer_text')->nullable()->after('website');

            if (!Schema::hasColumn('struk_settings', 'label_tanda_terima'))
                $table->string('label_tanda_terima')->default('Tanda Terima')->after('footer_text');

            if (!Schema::hasColumn('struk_settings', 'label_hormat_kami'))
                $table->string('label_hormat_kami')->default('Hormat Kami')->after('label_tanda_terima');

            if (!Schema::hasColumn('struk_settings', 'teks_kredit'))
                $table->string('teks_kredit')->default('Harap dilunasi secepatnya')->after('label_hormat_kami');

            if (!Schema::hasColumn('struk_settings', 'tampil_npwp'))
                $table->boolean('tampil_npwp')->default(true)->after('teks_kredit');

            if (!Schema::hasColumn('struk_settings', 'tampil_member'))
                $table->boolean('tampil_member')->default(true)->after('tampil_npwp');

            if (!Schema::hasColumn('struk_settings', 'tampil_poin'))
                $table->boolean('tampil_poin')->default(true)->after('tampil_member');

            if (!Schema::hasColumn('struk_settings', 'tampil_footer_ttd'))
                $table->boolean('tampil_footer_ttd')->default(true)->after('tampil_poin');

            if (!Schema::hasColumn('struk_settings', 'tampil_footer_text'))
                $table->boolean('tampil_footer_text')->default(true)->after('tampil_footer_ttd');
        });

        // Update default value pada data yang sudah ada
        DB::table('struk_settings')->where('id', 1)->update([
            'label_tanda_terima' => 'Tanda Terima',
            'label_hormat_kami'  => 'Hormat Kami',
            'teks_kredit'        => 'Harap dilunasi secepatnya',
        ]);
    }

    public function down(): void
    {
        Schema::table('struk_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tagline', 'email', 'website', 'footer_text',
                'label_tanda_terima', 'label_hormat_kami', 'teks_kredit',
                'tampil_npwp', 'tampil_member', 'tampil_poin',
                'tampil_footer_ttd', 'tampil_footer_text',
            ]);
        });
    }
};
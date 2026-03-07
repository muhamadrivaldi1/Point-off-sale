<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrukSetting extends Model
{
    protected $table = 'struk_settings';

    protected $fillable = [
        'nama_toko',
        'tagline',
        'alamat',
        'kota',
        'npwp',
        'telepon',
        'email',
        'website',
        'footer_text',
        'label_tanda_terima',
        'label_hormat_kami',
        'teks_kredit',
        'tampil_npwp',
        'tampil_member',
        'tampil_poin',
        'tampil_footer_ttd',
        'tampil_footer_text',
    ];

    protected $casts = [
        'tampil_npwp'        => 'boolean',
        'tampil_member'      => 'boolean',
        'tampil_poin'        => 'boolean',
        'tampil_footer_ttd'  => 'boolean',
        'tampil_footer_text' => 'boolean',
    ];

    public static function getSetting(): self
    {
        return self::first() ?? self::create([
            'nama_toko'          => 'NAMA TOKO ANDA',
            'alamat'             => 'Alamat Toko',
            'kota'               => 'Kota / Kabupaten',
            'telepon'            => '08xx-xxxx-xxxx',
            'footer_text'        => 'Terima kasih telah berbelanja!',
            'label_tanda_terima' => 'Tanda Terima',
            'label_hormat_kami'  => 'Hormat Kami',
            'teks_kredit'        => 'Harap dilunasi secepatnya',
            'tampil_npwp'        => true,
            'tampil_member'      => true,
            'tampil_poin'        => true,
            'tampil_footer_ttd'  => true,
            'tampil_footer_text' => true,
        ]);
    }
}
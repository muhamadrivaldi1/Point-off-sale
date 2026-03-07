<?php

namespace App\Http\Controllers;

use App\Models\StrukSetting;
use Illuminate\Http\Request;

class StrukSettingController extends Controller
{
    public function index()
    {
        $setting = StrukSetting::getSetting();
        return view('struk.setting', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_toko'          => 'required|string|max:100',
            'tagline'            => 'nullable|string|max:150',
            'alamat'             => 'nullable|string|max:255',
            'kota'               => 'nullable|string|max:100',
            'npwp'               => 'nullable|string|max:30',
            'telepon'            => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
            'website'            => 'nullable|string|max:100',
            'footer_text'        => 'nullable|string|max:500',
            'label_tanda_terima' => 'nullable|string|max:50',
            'label_hormat_kami'  => 'nullable|string|max:50',
            'teks_kredit'        => 'nullable|string|max:100',
        ]);

        $setting = StrukSetting::getSetting();

        $setting->update([
            'nama_toko'          => $request->nama_toko,
            'tagline'            => $request->tagline,
            'alamat'             => $request->alamat,
            'kota'               => $request->kota,
            'npwp'               => $request->npwp,
            'telepon'            => $request->telepon,
            'email'              => $request->email,
            'website'            => $request->website,
            'footer_text'        => $request->footer_text,
            'label_tanda_terima' => $request->label_tanda_terima ?? 'Tanda Terima',
            'label_hormat_kami'  => $request->label_hormat_kami  ?? 'Hormat Kami',
            'teks_kredit'        => $request->teks_kredit        ?? 'Harap dilunasi secepatnya',
            'tampil_npwp'        => $request->boolean('tampil_npwp'),
            'tampil_member'      => $request->boolean('tampil_member'),
            'tampil_poin'        => $request->boolean('tampil_poin'),
            'tampil_footer_ttd'  => $request->boolean('tampil_footer_ttd'),
            'tampil_footer_text' => $request->boolean('tampil_footer_text'),
        ]);

        return redirect()->route('struk.setting')
                         ->with('success', 'Pengaturan struk berhasil disimpan!');
    }
}
@extends('layouts.app')

@section('title', 'Kelola Struk')

@section('content')

<style>
    .struk-page { max-width: 820px; margin: 0 auto; }

    .page-header-card {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 14px; padding: 24px 28px; margin-bottom: 24px;
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
    }
    .page-header-card .title { color:#fff; font-size:1.15rem; font-weight:700; margin:0; }
    .page-header-card .subtitle { color:#94a3b8; font-size:.82rem; margin:3px 0 0; }
    .page-header-card .icon-wrap {
        width:48px; height:48px; background:rgba(255,255,255,.1);
        border-radius:12px; display:flex; align-items:center; justify-content:center;
        font-size:1.5rem; color:#fff; flex-shrink:0;
    }

    .info-card { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden; }
    .info-card .card-head {
        padding:18px 24px; border-bottom:1px solid #f1f5f9;
        display:flex; align-items:center; justify-content:space-between;
    }
    .info-card .card-head span { font-weight:600; font-size:.95rem; color:#1e293b; }

    .info-table { width:100%; border-collapse:collapse; }
    .info-table tr { border-bottom:1px solid #f1f5f9; }
    .info-table tr:last-child { border-bottom:none; }
    .info-table td { padding:13px 24px; font-size:.88rem; vertical-align:middle; }
    .info-table td:first-child {
        width:42%; color:#64748b; font-weight:500; background:#f8fafc;
        display:flex; align-items:center; gap:8px;
    }
    .info-table td:last-child { color:#1e293b; font-weight:500; }
    .field-icon {
        width:28px; height:28px; border-radius:7px;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:.82rem; flex-shrink:0;
    }
    .badge-on  { background:#dcfce7; color:#166534; font-size:.75rem; padding:3px 10px; border-radius:20px; font-weight:600; }
    .badge-off { background:#fee2e2; color:#991b1b; font-size:.75rem; padding:3px 10px; border-radius:20px; font-weight:600; }
    .section-divider {
        padding:8px 24px; background:#f8fafc;
        font-size:.72rem; font-weight:700; text-transform:uppercase;
        letter-spacing:1px; color:#94a3b8; border-bottom:1px solid #f1f5f9;
    }

    .edit-card { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden; }
    .edit-card .card-head {
        padding:18px 24px; border-bottom:1px solid #f1f5f9;
        font-weight:600; font-size:.95rem; color:#1e293b;
        display:flex; align-items:center; gap:8px;
    }
    .edit-card .card-body-inner { padding:24px; }

    .form-label { font-size:.83rem; font-weight:600; color:#475569; margin-bottom:5px; }
    .form-control {
        border:1.5px solid #e2e8f0; border-radius:9px;
        padding:9px 13px; font-size:.88rem;
        transition:border-color .2s, box-shadow .2s;
    }
    .form-control:focus {
        border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); outline:none;
    }
    .section-label {
        font-size:.72rem; font-weight:700; text-transform:uppercase;
        letter-spacing:1px; color:#94a3b8; margin:22px 0 12px;
        display:flex; align-items:center; gap:8px;
    }
    .section-label::after { content:''; flex:1; height:1px; background:#f1f5f9; }

    .toggle-group { display:flex; flex-wrap:wrap; gap:10px; }
    .toggle-item {
        display:flex; align-items:center; gap:8px; cursor:pointer;
        background:#f8fafc; border:1.5px solid #e2e8f0;
        border-radius:9px; padding:8px 14px;
        transition:border-color .2s, background .2s; user-select:none;
    }
    .toggle-item.active { border-color:#3b82f6; background:#eff6ff; }
    .toggle-item span { font-size:.83rem; font-weight:500; color:#475569; }
    .toggle-item input { cursor:pointer; accent-color:#3b82f6; width:15px; height:15px; }

    .btn-save {
        background:linear-gradient(135deg,#2563eb,#3b82f6); color:#fff; border:none;
        padding:10px 24px; border-radius:9px; font-weight:600; font-size:.9rem;
        display:inline-flex; align-items:center; gap:6px;
        transition:opacity .2s,transform .15s;
    }
    .btn-save:hover { opacity:.9; transform:translateY(-1px); color:#fff; }
    .btn-cancel {
        background:#f1f5f9; color:#475569; border:none; text-decoration:none;
        padding:10px 20px; border-radius:9px; font-weight:600; font-size:.9rem;
        display:inline-flex; align-items:center; gap:6px; transition:background .2s;
    }
    .btn-cancel:hover { background:#e2e8f0; color:#334155; }
    .btn-edit-top {
        background:#fff; color:#2563eb; border:1.5px solid #2563eb;
        padding:7px 18px; border-radius:9px; font-weight:600; font-size:.85rem;
        display:inline-flex; align-items:center; gap:6px;
        transition:background .2s,color .2s; text-decoration:none;
    }
    .btn-edit-top:hover { background:#2563eb; color:#fff; }

    .alert-success-custom {
        background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:10px;
        color:#166534; padding:12px 18px; font-size:.88rem;
        display:flex; align-items:center; gap:10px; margin-bottom:20px;
    }
</style>

<div class="struk-page">

    <div class="page-header-card">
        <div>
            <p class="title"><i class="bi bi-receipt-cutoff me-2"></i>Kelola Struk</p>
            <p class="subtitle">Atur semua informasi yang tampil di struk cetak</p>
        </div>
        <div class="icon-wrap"><i class="bi bi-shop"></i></div>
    </div>

    @if(session('success'))
    <div class="alert-success-custom">
        <i class="bi bi-check-circle-fill text-success fs-5"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- ==================== MODE LIHAT ==================== --}}
    @if(!request('edit'))
    <div class="info-card">
        <div class="card-head">
            <span><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Struk Toko</span>
            <a href="?edit=1" class="btn-edit-top"><i class="bi bi-pencil"></i> Edit</a>
        </div>

        <div class="section-divider">🏪 Identitas Toko</div>
        <table class="info-table">
            <tr>
                <td><span class="field-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-shop"></i></span>Nama Toko</td>
                <td>{{ $setting->nama_toko ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon" style="background:#f3e8ff;color:#7c3aed"><i class="bi bi-chat-quote"></i></span>Tagline / Slogan</td>
                <td>{{ $setting->tagline ?: '-' }}</td>
            </tr>
        </table>

        <div class="section-divider">📍 Lokasi</div>
        <table class="info-table">
            <tr>
                <td><span class="field-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-geo-alt"></i></span>Alamat</td>
                <td style="white-space:pre-line">{{ $setting->alamat ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon bg-success bg-opacity-10 text-success"><i class="bi bi-building"></i></span>Kota / Kabupaten</td>
                <td>{{ $setting->kota ?: '-' }}</td>
            </tr>
        </table>

        <div class="section-divider">📞 Kontak & Pajak</div>
        <table class="info-table">
            <tr>
                <td><span class="field-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-file-earmark-text"></i></span>NPWP</td>
                <td>{{ $setting->npwp ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon bg-info bg-opacity-10 text-info"><i class="bi bi-telephone"></i></span>No. Telepon / HP</td>
                <td>{{ $setting->telepon ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-envelope"></i></span>Email</td>
                <td>{{ $setting->email ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon bg-dark bg-opacity-10 text-dark"><i class="bi bi-globe"></i></span>Website</td>
                <td>{{ $setting->website ?: '-' }}</td>
            </tr>
        </table>

        <div class="section-divider">🧾 Teks di Struk</div>
        <table class="info-table">
            <tr>
                <td><span class="field-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-card-text"></i></span>Pesan Footer</td>
                <td style="white-space:pre-line">{{ $setting->footer_text ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-pen"></i></span>Label Kiri Bawah</td>
                <td>{{ $setting->label_tanda_terima ?? 'Tanda Terima' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon bg-success bg-opacity-10 text-success"><i class="bi bi-pen"></i></span>Label Kanan Bawah</td>
                <td>{{ $setting->label_hormat_kami ?? 'Hormat Kami' }}</td>
            </tr>
            <tr>
                <td><span class="field-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-credit-card"></i></span>Teks Nota Kredit</td>
                <td>{{ $setting->teks_kredit ?? 'Harap dilunasi secepatnya' }}</td>
            </tr>
        </table>

        <div class="section-divider">⚙️ Tampilkan di Struk</div>
        <table class="info-table">
            <tr>
                <td><span class="field-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-toggle-on"></i></span>NPWP</td>
                <td><span class="{{ $setting->tampil_npwp ? 'badge-on' : 'badge-off' }}">{{ $setting->tampil_npwp ? 'Tampil' : 'Disembunyikan' }}</span></td>
            </tr>
            <tr>
                <td><span class="field-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-toggle-on"></i></span>Info Member</td>
                <td><span class="{{ $setting->tampil_member ? 'badge-on' : 'badge-off' }}">{{ $setting->tampil_member ? 'Tampil' : 'Disembunyikan' }}</span></td>
            </tr>
            <tr>
                <td><span class="field-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-toggle-on"></i></span>Poin Member</td>
                <td><span class="{{ $setting->tampil_poin ? 'badge-on' : 'badge-off' }}">{{ $setting->tampil_poin ? 'Tampil' : 'Disembunyikan' }}</span></td>
            </tr>
            <tr>
                <td><span class="field-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-toggle-on"></i></span>Tanda Tangan</td>
                <td><span class="{{ $setting->tampil_footer_ttd ? 'badge-on' : 'badge-off' }}">{{ $setting->tampil_footer_ttd ? 'Tampil' : 'Disembunyikan' }}</span></td>
            </tr>
            <tr>
                <td><span class="field-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-toggle-on"></i></span>Pesan Footer</td>
                <td><span class="{{ ($setting->tampil_footer_text ?? true) ? 'badge-on' : 'badge-off' }}">{{ ($setting->tampil_footer_text ?? true) ? 'Tampil' : 'Disembunyikan' }}</span></td>
            </tr>
        </table>
    </div>

    {{-- ==================== MODE EDIT ==================== --}}
    @else
    <div class="edit-card">
        <div class="card-head">
            <i class="bi bi-pencil-square text-primary"></i> Edit Pengaturan Struk
        </div>
        <div class="card-body-inner">
            <form method="POST" action="{{ route('struk.setting.update') }}">
                @csrf

                <p class="section-label">🏪 Identitas Toko</p>
                <div class="row g-3 mb-1">
                    <div class="col-md-6">
                        <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text" name="nama_toko" class="form-control"
                               value="{{ old('nama_toko', $setting->nama_toko) }}" required>
                        @error('nama_toko')<div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tagline / Slogan</label>
                        <input type="text" name="tagline" class="form-control"
                               value="{{ old('tagline', $setting->tagline) }}"
                               placeholder="Melayani dengan sepenuh hati">
                    </div>
                </div>

                <p class="section-label">📍 Lokasi</p>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"
                              placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan">{{ old('alamat', $setting->alamat) }}</textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label">Kota / Kabupaten</label>
                    <input type="text" name="kota" class="form-control"
                           value="{{ old('kota', $setting->kota) }}"
                           placeholder="Kab. Minahasa Selatan">
                </div>

                <p class="section-label">📞 Kontak & Pajak</p>
                <div class="row g-3 mb-1">
                    <div class="col-md-6">
                        <label class="form-label">NPWP</label>
                        <input type="text" name="npwp" class="form-control"
                               value="{{ old('npwp', $setting->npwp) }}" placeholder="xx.xxx.xxx.x-xxx.xxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Telepon / HP</label>
                        <input type="text" name="telepon" class="form-control"
                               value="{{ old('telepon', $setting->telepon) }}" placeholder="08xx-xxxx-xxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $setting->email) }}" placeholder="toko@email.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Website</label>
                        <input type="text" name="website" class="form-control"
                               value="{{ old('website', $setting->website) }}" placeholder="www.tokoku.com">
                    </div>
                </div>

                <p class="section-label">🧾 Teks di Struk</p>
                <div class="mb-3">
                    <label class="form-label">Pesan Footer <small class="text-muted">(tampil di bawah total)</small></label>
                    <textarea name="footer_text" class="form-control" rows="2"
                              placeholder="Terima kasih telah berbelanja!&#10;Barang tidak dapat dikembalikan.">{{ old('footer_text', $setting->footer_text) }}</textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Label Kiri Bawah</label>
                        <input type="text" name="label_tanda_terima" class="form-control"
                               value="{{ old('label_tanda_terima', $setting->label_tanda_terima ?? 'Tanda Terima') }}"
                               placeholder="Tanda Terima">
                        <small class="text-muted">Default: Tanda Terima</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Label Kanan Bawah</label>
                        <input type="text" name="label_hormat_kami" class="form-control"
                               value="{{ old('label_hormat_kami', $setting->label_hormat_kami ?? 'Hormat Kami') }}"
                               placeholder="Hormat Kami">
                        <small class="text-muted">Default: Hormat Kami</small>
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label">Teks Peringatan Nota Kredit</label>
                    <input type="text" name="teks_kredit" class="form-control"
                           value="{{ old('teks_kredit', $setting->teks_kredit ?? 'Harap dilunasi secepatnya') }}"
                           placeholder="Harap dilunasi secepatnya">
                </div>

                <p class="section-label">⚙️ Tampilkan di Struk</p>
                <div class="toggle-group">
                    <label class="toggle-item {{ old('tampil_npwp', $setting->tampil_npwp) ? 'active' : '' }}">
                        <input type="checkbox" name="tampil_npwp" value="1"
                               {{ old('tampil_npwp', $setting->tampil_npwp) ? 'checked' : '' }}
                               onchange="this.closest('.toggle-item').classList.toggle('active', this.checked)">
                        <span>NPWP</span>
                    </label>
                    <label class="toggle-item {{ old('tampil_member', $setting->tampil_member) ? 'active' : '' }}">
                        <input type="checkbox" name="tampil_member" value="1"
                               {{ old('tampil_member', $setting->tampil_member) ? 'checked' : '' }}
                               onchange="this.closest('.toggle-item').classList.toggle('active', this.checked)">
                        <span>Info Member</span>
                    </label>
                    <label class="toggle-item {{ old('tampil_poin', $setting->tampil_poin) ? 'active' : '' }}">
                        <input type="checkbox" name="tampil_poin" value="1"
                               {{ old('tampil_poin', $setting->tampil_poin) ? 'checked' : '' }}
                               onchange="this.closest('.toggle-item').classList.toggle('active', this.checked)">
                        <span>Poin Member</span>
                    </label>
                    <label class="toggle-item {{ old('tampil_footer_ttd', $setting->tampil_footer_ttd) ? 'active' : '' }}">
                        <input type="checkbox" name="tampil_footer_ttd" value="1"
                               {{ old('tampil_footer_ttd', $setting->tampil_footer_ttd) ? 'checked' : '' }}
                               onchange="this.closest('.toggle-item').classList.toggle('active', this.checked)">
                        <span>Tanda Tangan</span>
                    </label>
                    <label class="toggle-item {{ old('tampil_footer_text', $setting->tampil_footer_text ?? true) ? 'active' : '' }}">
                        <input type="checkbox" name="tampil_footer_text" value="1"
                               {{ old('tampil_footer_text', $setting->tampil_footer_text ?? true) ? 'checked' : '' }}
                               onchange="this.closest('.toggle-item').classList.toggle('active', this.checked)">
                        <span>Pesan Footer</span>
                    </label>
                </div>

                <hr style="border-color:#f1f5f9; margin:24px 0">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-save"><i class="bi bi-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('struk.setting') }}" class="btn-cancel"><i class="bi bi-x"></i> Batal</a>
                </div>

            </form>
        </div>
    </div>
    @endif

</div>
@endsection
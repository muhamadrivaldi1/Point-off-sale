@extends('layouts.app')

@section('title','Tambah Supplier')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        Tambah Supplier
    </div>

    <div class="card-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Kode Supplier</label>
                <input type="text" name="kode_supplier"
                    class="form-control"
                    value="{{ $kode_supplier }}"
                    readonly>
            </div>

            <div class="mb-3">
                <label>Nama Supplier</label>
                <input type="text" name="nama_supplier" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>NPWP</label>
                <input type="text" name="npwp" class="form-control">
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label>Telepon 1</label>
                <input type="text" name="telepon" class="form-control">
            </div>

            <div class="mb-3">
                <label>Telepon 2</label>
                <input type="text" name="telepon2" class="form-control">
            </div>

            <div class="mb-3">
                <label>Fax</label>
                <input type="text" name="fax" class="form-control">
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="mb-3">
                <label>Bank</label>
                <input type="text" name="bank" class="form-control">
            </div>

            <div class="mb-3">
                <label>Nomor Rekening</label>
                <input type="text" name="nomor_rekening" class="form-control">
            </div>

            <div class="mb-3">
                <label>Contact Person (CP)</label>
                <input type="text" name="cp" class="form-control">
            </div>

            <div class="mb-3">
                <label>Jabatan CP</label>
                <input type="text" name="jabatan_cp" class="form-control">
            </div>

            <div class="mb-3">
                <label>Telepon CP</label>
                <input type="text" name="telepon_cp" class="form-control">
            </div>

            <div class="mb-3">
                <label>Nomor Seri Faktur Pajak</label>
                <input type="text" name="nomor_seri_fp" class="form-control">
            </div>

            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Kembali</a>

            </form>
    </div>
</div>
@endsection
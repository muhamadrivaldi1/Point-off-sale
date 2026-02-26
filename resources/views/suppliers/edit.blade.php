@extends('layouts.app')

@section('title','Edit Supplier')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        Edit Supplier
    </div>

    <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')

            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            <div class="mb-3"> <label>Kode Supplier</label>
                 <input type="text" name="kode_supplier" value="{{ $supplier->kode_supplier }}" 
                 class="form-control" readonly> </div>

            <div class="mb-3">
                <label>Nama Supplier</label>
                <input type="text" name="nama_supplier"
                    value="{{ old('nama_supplier', $supplier->nama_supplier) }}"
                    class="form-control" required>
            </div>

            <div class="mb-3">
                <label>NPWP</label>
                <input type="text" name="npwp"
                    value="{{ old('npwp', $supplier->npwp) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control">{{ old('alamat', $supplier->alamat) }}</textarea>
            </div>

            <div class="mb-3">
                <label>Telepon 1</label>
                <input type="text" name="telepon"
                    value="{{ old('telepon', $supplier->telepon) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Telepon 2</label>
                <input type="text" name="telepon2"
                    value="{{ old('telepon2', $supplier->telepon2) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Fax</label>
                <input type="text" name="fax"
                    value="{{ old('fax', $supplier->fax) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email"
                    value="{{ old('email', $supplier->email) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Bank</label>
                <input type="text" name="bank"
                    value="{{ old('bank', $supplier->bank) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Nomor Rekening</label>
                <input type="text" name="nomor_rekening"
                    value="{{ old('nomor_rekening', $supplier->nomor_rekening) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Contact Person</label>
                <input type="text" name="cp"
                    value="{{ old('cp', $supplier->cp) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Jabatan CP</label>
                <input type="text" name="jabatan_cp"
                    value="{{ old('jabatan_cp', $supplier->jabatan_cp) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Telepon CP</label>
                <input type="text" name="telepon_cp"
                    value="{{ old('telepon_cp', $supplier->telepon_cp) }}"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Nomor Seri Faktur Pajak</label>
                <input type="text" name="nomor_seri_fp"
                    value="{{ old('nomor_seri_fp', $supplier->nomor_seri_fp) }}"
                    class="form-control">
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
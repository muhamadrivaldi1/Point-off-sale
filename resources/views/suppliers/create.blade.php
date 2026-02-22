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
                <input type="text" name="kode_supplier" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Nama Supplier</label>
                <input type="text" name="nama_supplier" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label>Telepon</label>
                <input type="text" name="telepon" class="form-control">
            </div>

            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
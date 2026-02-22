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

            <div class="mb-3">
                <label>Kode Supplier</label>
                <input type="text" name="kode_supplier"
                       value="{{ $supplier->kode_supplier }}"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Nama Supplier</label>
                <input type="text" name="nama_supplier"
                       value="{{ $supplier->nama_supplier }}"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control">{{ $supplier->alamat }}</textarea>
            </div>

            <div class="mb-3">
                <label>Telepon</label>
                <input type="text" name="telepon"
                       value="{{ $supplier->telepon }}"
                       class="form-control">
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
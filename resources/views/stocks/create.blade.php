@extends('layouts.app')

@section('title','Tambah Stok')

@section('content')
<h4 class="mb-4">Tambah Stok</h4>

{{-- ALERT --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('stocks.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                {{-- Produk / Unit --}}
                <div class="col-md-6">
                    <label class="form-label">Produk</label>
                    <select name="product_unit_id" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->product->name }} ({{ $unit->unit_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lokasi --}}
                <div class="col-md-3">
                    <label class="form-label">Lokasi</label>
                    <select name="location" class="form-select" required>
                        <option value="">-- Pilih Lokasi --</option>
                        <option value="gudang">Gudang</option>
                        <option value="toko">Toko</option>
                    </select>
                </div>

                {{-- Qty --}}
                <div class="col-md-3">
                    <label class="form-label">Qty</label>
                    <input type="number" name="qty" class="form-control" min="1" required>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('stocks.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Stok
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

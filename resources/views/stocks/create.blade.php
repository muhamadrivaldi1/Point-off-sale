@extends('layouts.app')

@section('title','Tambah Stok')

@section('content')
<h4 class="mb-4">Tambah Stok</h4>

{{-- ALERT SUCCESS --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ALERT ERROR --}}
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

                {{-- PRODUK --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Produk</label>
                    <select name="product_unit_id" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->product->name }} ({{ $unit->unit_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GUDANG --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Gudang</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- QTY --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Qty</label>
                    <input 
                        type="number"
                        name="qty"
                        class="form-control"
                        min="1"
                        placeholder="Masukkan jumlah"
                        required
                    >
                </div>

            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('stocks.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Stok
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
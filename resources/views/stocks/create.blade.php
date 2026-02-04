@extends('layouts.app')

@section('title','Tambah Stok')

@section('content')
<h4 class="mb-3">Tambah Stok</h4>

<form method="POST" action="{{ route('stocks.store') }}">
@csrf

<div class="mb-3">
    <label class="form-label">Produk / Unit</label>
    <select name="product_unit_id" class="form-select" required>
        <option value="">-- pilih --</option>
        @foreach($units as $u)
            <option value="{{ $u->id }}">
                {{ $u->product->name }} - {{ $u->unit_name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Lokasi</label>
    <select name="location" class="form-select" required>
        <option value="gudang">Gudang</option>
        <option value="toko">Toko</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Qty</label>
    <input type="number" name="qty" class="form-control" min="0" required>
</div>

<button class="btn btn-primary">Simpan</button>
<a href="{{ route('stocks.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection

@extends('layouts.app')

@section('title','Edit Stok')

@section('content')
<h4 class="mb-3">Edit Stok</h4>

<form method="POST" action="{{ route('stocks.update',$stock->id) }}">
@csrf
@method('PUT')

<div class="mb-3">
    <label class="form-label">Produk / Unit</label>
    <input class="form-control"
           value="{{ $stock->unit->product->name }} - {{ $stock->unit->unit_name }}"
           disabled>
</div>

<div class="mb-3">
    <label class="form-label">Lokasi</label>
    <select name="location" class="form-select">
        <option value="gudang" {{ $stock->location=='gudang'?'selected':'' }}>
            Gudang
        </option>
        <option value="toko" {{ $stock->location=='toko'?'selected':'' }}>
            Toko
        </option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Qty</label>
    <input type="number"
           name="qty"
           value="{{ $stock->qty }}"
           class="form-control"
           min="0"
           required>
</div>

<button class="btn btn-primary">Update</button>
<a href="{{ route('stocks.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection

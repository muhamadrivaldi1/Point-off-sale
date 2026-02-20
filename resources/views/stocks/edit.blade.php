@extends('layouts.app')

@section('title','Edit Stok')

@section('content')
<h4 class="mb-3">Edit Stok</h4>

<form method="POST" action="{{ route('stocks.update',$stock->id) }}">
@csrf
@method('PUT')

{{-- Produk --}}
{{-- Produk / Unit (Tidak bisa diubah) --}}
<div class="mb-3">
    <label class="form-label">Produk / Unit</label>

    <input type="text"
           class="form-control"
           value="{{ $stock->unit->product->name }} - {{ $stock->unit->unit_name }}"
           readonly>

    {{-- Hidden supaya tetap ikut ke update --}}
    <input type="hidden"
           name="product_unit_id"
           value="{{ $stock->product_unit_id }}">
</div>

{{-- Gudang --}}
<div class="mb-3">
    <label class="form-label">Gudang</label>
    <select name="warehouse_id" class="form-select" required>
        @foreach($warehouses as $w)
            <option value="{{ $w->id }}"
                {{ $stock->warehouse_id == $w->id ? 'selected' : '' }}>
                {{ $w->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- Qty --}}
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
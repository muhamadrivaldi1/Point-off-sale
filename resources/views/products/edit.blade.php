@extends('layouts.app')

@section('title','Edit Produk')

@section('content')
<h4 class="mb-3">Edit Produk</h4>

{{-- ALERT ERROR --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('products.update',$product->id) }}">
@csrf
@method('PUT')

<div class="mb-3">
    <label class="form-label">Nama Produk</label>
    <input type="text"
           name="name"
           value="{{ old('name',$product->name) }}"
           class="form-control"
           required>
</div>

<div class="mb-3">
    <label class="form-label">SKU</label>
    <input type="text"
           name="sku"
           value="{{ old('sku',$product->sku) }}"
           class="form-control">
</div>

<div class="form-check mb-3">
    <input class="form-check-input"
           type="checkbox"
           name="is_bkp"
           value="1"
           {{ old('is_bkp',$product->is_bkp) ? 'checked' : '' }}>
    <label class="form-check-label">BKP</label>
</div>

<hr>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Unit Produk</h5>
    <button type="button"
            class="btn btn-sm btn-success"
            onclick="addUnit()">
        + Tambah Unit
    </button>
</div>

<div id="units">
@foreach($product->units as $i => $u)
<div class="row mb-2 unit-row align-items-center">
    <div class="col">
        <input name="units[{{ $i }}][name]"
               value="{{ old("units.$i.name",$u->unit_name) }}"
               class="form-control"
               placeholder="Nama Unit"
               required>
    </div>
    <div class="col">
        <input name="units[{{ $i }}][conversion]"
               value="{{ old("units.$i.conversion",$u->conversion) }}"
               class="form-control"
               placeholder="Konversi"
               required>
    </div>
    <div class="col">
        <input name="units[{{ $i }}][barcode]"
               value="{{ old("units.$i.barcode",$u->barcode) }}"
               class="form-control"
               placeholder="Barcode">
    </div>
    <div class="col">
        <input name="units[{{ $i }}][price]"
               value="{{ old("units.$i.price",$u->price) }}"
               class="form-control"
               placeholder="Harga"
               required>
    </div>
    <div class="col-1 text-center">
        <button type="button"
                class="btn btn-danger btn-sm"
                onclick="removeUnit(this)">
            ×
        </button>
    </div>
</div>
@endforeach
</div>

<button class="btn btn-primary mt-3">Update</button>
<a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">
    Kembali
</a>
</form>

<script>
let unitIndex = {{ $product->units->count() }};

function addUnit() {
    const html = `
    <div class="row mb-2 unit-row align-items-center">
        <div class="col">
            <input name="units[${unitIndex}][name]"
                   class="form-control"
                   placeholder="Nama Unit"
                   required>
        </div>
        <div class="col">
            <input name="units[${unitIndex}][conversion]"
                   class="form-control"
                   placeholder="Konversi"
                   required>
        </div>
        <div class="col">
            <input name="units[${unitIndex}][barcode]"
                   class="form-control"
                   placeholder="Barcode">
        </div>
        <div class="col">
            <input name="units[${unitIndex}][price]"
                   class="form-control"
                   placeholder="Harga"
                   required>
        </div>
        <div class="col-1 text-center">
            <button type="button"
                    class="btn btn-danger btn-sm"
                    onclick="removeUnit(this)">
                ×
            </button>
        </div>
    </div>`;
    document.getElementById('units')
        .insertAdjacentHTML('beforeend', html);
    unitIndex++;
}

function removeUnit(btn) {
    const rows = document.querySelectorAll('.unit-row');
    if (rows.length <= 1) {
        alert('Minimal harus ada 1 unit');
        return;
    }
    btn.closest('.unit-row').remove();
}
</script>
@endsection

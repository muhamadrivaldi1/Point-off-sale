@extends('layouts.app')

@section('title','Tambah Produk')

@section('content')
<h4 class="mb-3">Tambah Produk</h4>

{{-- ✅ TAMPILKAN ERROR --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('products.store') }}">
@csrf

<div class="mb-3">
    <label>Nama Produk</label>
    <input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
    <label>SKU</label>
    <input type="text" name="sku" class="form-control">
</div>

{{-- ✅ TAMBAHAN MIN STOCK (INI YANG PENTING) --}}
<div class="mb-3">
    <label>Stok Minimal</label>
    <input type="number"
           name="min_stock"
           class="form-control"
           value="0"
           min="0"
           required>
</div>

<div class="form-check mb-3">
    <input type="checkbox" name="is_bkp" value="1" class="form-check-input">
    <label class="form-check-label">BKP</label>
</div>

<hr>
<h5>Unit Produk</h5>

<div id="units">

<div class="row mb-3 unit-row">
    <div class="col">
        <label>Nama Unit</label>
        <input name="units[0][name]"
               class="form-control"
               placeholder="PCS"
               required>
    </div>

    <div class="col">
        <label>Konversi</label>
        <input type="number"
               name="units[0][conversion]"
               class="form-control"
               value="1"
               min="1"
               required>
    </div>

    <div class="col">
        <label>Barcode</label>
        <input name="units[0][barcode]"
               class="form-control">
    </div>

    <div class="col">
        <label>Harga</label>
        <input type="number"
               name="units[0][price]"
               class="form-control"
               min="0"
               required>
    </div>

    <div class="col-1 d-flex align-items-end">
        <button type="button"
                class="btn btn-danger btn-sm"
                onclick="removeUnit(this)">
            ×
        </button>
    </div>
</div>

</div>

{{-- ✅ TOMBOL TAMBAH UNIT --}}
<button type="button"
        class="btn btn-sm btn-success mb-3"
        onclick="addUnit()">
    + Tambah Unit
</button>

<br>

<button class="btn btn-success">Simpan</button>
<a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali</a>

</form>

{{-- ✅ SCRIPT DINAMIS --}}
<script>
let unitIndex = 1;

function addUnit() {
    const html = `
    <div class="row mb-3 unit-row">
        <div class="col">
            <input name="units[${unitIndex}][name]"
                   class="form-control"
                   placeholder="Nama Unit"
                   required>
        </div>

        <div class="col">
            <input type="number"
                   name="units[${unitIndex}][conversion]"
                   class="form-control"
                   placeholder="Konversi"
                   min="1"
                   required>
        </div>

        <div class="col">
            <input name="units[${unitIndex}][barcode]"
                   class="form-control"
                   placeholder="Barcode">
        </div>

        <div class="col">
            <input type="number"
                   name="units[${unitIndex}][price]"
                   class="form-control"
                   placeholder="Harga"
                   min="0"
                   required>
        </div>

        <div class="col-1 d-flex align-items-center">
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

    if (confirm('Hapus unit ini?')) {
        btn.closest('.unit-row').remove();
    }
}
</script>

@endsection
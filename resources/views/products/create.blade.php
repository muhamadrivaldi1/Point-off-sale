@extends('layouts.app')

@section('title','Tambah Produk')

@section('content')
<h4 class="mb-3">Tambah Produk</h4>

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

<div class="form-check mb-3">
    <input type="checkbox" name="is_bkp" value="1" class="form-check-input">
    <label class="form-check-label">BKP</label>
</div>

<hr>
<h5>Unit Produk</h5>

<div class="row mb-3">
    <div class="col">
        <label>Nama Unit</label>
        <input name="units[0][name]" class="form-control" placeholder="PCS">
    </div>
    <div class="col">
        <label>Konversi</label>
        <input name="units[0][conversion]" class="form-control" value="1">
    </div>
    <div class="col">
        <label>Barcode</label>
        <input name="units[0][barcode]" class="form-control">
    </div>
    <div class="col">
        <label>Harga</label>
        <input name="units[0][price]" class="form-control">
    </div>
</div>

<button class="btn btn-success">Simpan</button>
<a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection

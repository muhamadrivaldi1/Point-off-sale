@extends('layouts.app')

@section('title','Edit Transaksi')

@section('content')
<h4 class="mb-3">Edit Transaksi</h4>

{{-- REQUEST DARI KASIR --}}
@if($trx->requests->count())
<div class="alert alert-warning">
    <strong>Permintaan Perbaikan:</strong>
    <ul class="mb-0">
        @foreach($trx->requests as $r)
            <li>
                <strong>{{ $r->user->name }}</strong> :
                {{ $r->message }}
            </li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('transactions.update',$trx->id) }}">
@csrf
@method('PUT')

<div class="mb-3">
    <label>No Invoice</label>
    <input type="text"
           class="form-control"
           value="{{ $trx->invoice }}"
           disabled>
</div>

<div class="mb-3">
    <label>Total Transaksi</label>
    <input type="number"
           name="total"
           value="{{ $trx->total }}"
           class="form-control"
           required>
</div>

<hr>

<h5>Item Transaksi</h5>
<table class="table table-sm table-bordered">
    <thead class="table-light">
        <tr>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trx->items as $item)
        <tr>
            <td>{{ $item->product_name }}</td>
            <td>{{ $item->qty }}</td>
            <td>Rp {{ number_format($item->price) }}</td>
            <td>Rp {{ number_format($item->subtotal) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-3">
    <button class="btn btn-primary">Simpan Perubahan</button>
    <a href="{{ route('transactions.index') }}"
       class="btn btn-secondary">
        Kembali
    </a>
</div>
</form>
@endsection

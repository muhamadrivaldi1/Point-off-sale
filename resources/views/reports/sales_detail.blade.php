@extends('layouts.app')

@section('content')
<h4>Detail Penjualan</h4>

<div class="mb-3">
    <strong>Invoice:</strong> {{ $trx->trx_number ?? '-' }}<br> 
    <strong>Tanggal:</strong> {{ $trx->created_at->format('d/m/Y H:i') }}<br>
    <strong>Total:</strong> Rp {{ number_format($trx->total) }}
</div>

<table class="table table-bordered table-sm">
    <thead>
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
            <td>
                {{ $item->unit->product->name }}
                <small class="text-muted">
                    ({{ $item->unit->unit_name }})
                </small>
            </td>
            <td>{{ $item->qty }}</td>
            <td>Rp {{ number_format($item->price) }}</td>
            <td>Rp {{ number_format($item->subtotal) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('reports.sales') }}"
   class="btn btn-secondary">
    Kembali
</a>
@endsection

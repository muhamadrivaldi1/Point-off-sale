@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">Detail Transaksi</h5>
            <span class="badge bg-secondary">
                {{ optional($trx->member)->name ?? 'Pelanggan Umum' }}
            </span>
        </div>
        <div class="card-body">

            <p><strong>No Invoice:</strong> {{ $trx->trx_number }}</p>
            <p><strong>Tanggal:</strong> {{ $trx->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Status:</strong>
                @if($trx->status == 'partial')
                    <span class="badge bg-info">Dibayar Sebagian</span>
                @elseif($trx->status == 'unpaid')
                    <span class="badge bg-warning text-dark">Belum Bayar</span>
                @elseif($trx->status == 'kredit')
                    <span class="badge bg-secondary text-white">Kredit</span>
                @else
                    <span class="badge bg-success">Lunas</span>
                @endif
            </p>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Satuan</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trx->items as $item)
                        <tr>
                            <td>{{ $item->unit->product->name ?? '-' }}</td>
                            <td>{{ $item->unit->unit_name ?? '-' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>{{ number_format($item->price,0,',','.') }}</td>
                            <td>{{ number_format($item->discount ?? 0,0,',','.') }}</td>
                            <td>{{ number_format(($item->price - ($item->discount ?? 0)) * $item->qty,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
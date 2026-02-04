@extends('layouts.app')

@section('title','Transaksi')

@section('content')
<h4 class="mb-3">Data Transaksi</h4>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th width="50">No</th>
            <th>Tanggal</th>
            <th>No Invoice</th>
            <th>Total</th>
            <th width="120">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $trx)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $trx->trx_number ?? '-' }}</td>
            <td>Rp {{ number_format($trx->total) }}</td>
            <td class="text-center">
                @if(auth()->user()->role === 'owner')
                    <a href="{{ route('transactions.edit', $trx->id) }}"
                       class="btn btn-sm btn-warning">
                        Edit
                    </a>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted">
                Belum ada transaksi
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection

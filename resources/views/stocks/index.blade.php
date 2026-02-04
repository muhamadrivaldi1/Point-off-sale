@extends('layouts.app')

@section('title','Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Stok</h4>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-striped align-middle">
    <thead class="table-dark text-center">
        <tr>
            <th>Produk</th>
            <th>Unit</th>
            <th>Lokasi</th>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
        @forelse($stocks as $s)
        <tr>
            <td>{{ $s->unit->product->name }}</td>
            <td>{{ $s->unit->unit_name }}</td>
            <td class="text-center">
                <span class="badge bg-secondary">
                    {{ ucfirst($s->location) }}
                </span>
            </td>
            <td class="text-end">
                {{ number_format($s->qty) }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center text-muted">
                Belum ada data stok
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection

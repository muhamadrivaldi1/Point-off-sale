@extends('layouts.app')

@section('title','Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Stok</h4>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
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
                            @if($s->location === 'toko')
                                <span class="badge bg-primary">Toko</span>
                            @elseif($s->location === 'gudang')
                                <span class="badge bg-success">Gudang</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($s->location) }}</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold">
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
        </div>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $stocks->links('pagination::bootstrap-5') }}
</div>
@endsection

@extends('layouts.app')

@section('title','Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Stok</h4>

    <a href="{{ route('stocks.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Stok
    </a>
</div>

{{-- ALERT --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- FILTER --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Cari Produk</label>
                <input type="text" name="q" class="form-control"
                       placeholder="Nama produk..."
                       value="{{ request('q') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Lokasi</label>
                <select name="location" class="form-select">
                    <option value="">Semua Lokasi</option>
                    <option value="toko" {{ request('location')=='toko'?'selected':'' }}>Toko</option>
                    <option value="gudang" {{ request('location')=='gudang'?'selected':'' }}>Gudang</option>
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>

            <div class="col-md-3 text-end">
                <a href="{{ route('stocks.index') }}" class="btn btn-secondary w-100">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- TABLE --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th>Produk</th>
                        <th>Unit</th>
                        <th>Lokasi</th>
                        <th class="text-end">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $s)
                    <tr>
                        <td class="text-start fw-semibold">
                            {{ $s->unit->product->name }}
                        </td>
                        <td class="text-start">
                            {{ $s->unit->unit_name }}
                        </td>
                        <td class="text-center">
                            @if($s->location === 'toko')
                                <span class="badge bg-primary">Toko</span>
                            @elseif($s->location === 'gudang')
                                <span class="badge bg-success">Gudang</span>
                            @else
                                <span class="badge bg-secondary">
                                    {{ ucfirst($s->location) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end fw-bold">
                            {{ number_format($s->qty) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Belum ada data stok
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- PAGINATION --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $stocks->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection

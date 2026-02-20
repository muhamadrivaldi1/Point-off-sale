@extends('layouts.app')

@section('title','Stok')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Stok</h4>

    <a href="{{ route('stocks.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Stok
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- FILTER --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">

            {{-- Cari Produk --}}
            <div class="col-md-4">
                <label class="form-label">Cari Produk</label>
                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="Nama produk..."
                       value="{{ request('q') }}">
            </div>

            {{-- Filter Gudang --}}
            <div class="col-md-4">
                <label class="form-label">Gudang</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}"
                            {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Filter --}}
            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>

            {{-- Reset --}}
            <div class="col-md-2">
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
                        <th class="text-start">Produk</th>
                        <th class="text-start">Unit</th>
                        <th>Gudang</th>
                        <th class="text-end">Qty</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($stocks as $s)
                    <tr>

                        {{-- Produk --}}
                        <td class="fw-semibold">
                            {{ $s->unit->product->name }}
                        </td>

                        {{-- Unit --}}
                        <td>
                            {{ $s->unit->unit_name }}
                        </td>

                        {{-- Gudang --}}
                        <td class="text-center">
                            @if($s->warehouse)
                                <span class="badge bg-success">
                                    {{ $s->warehouse->name }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    -
                                </span>
                            @endif
                        </td>

                        {{-- Qty --}}
                        <td class="text-end fw-bold">
                            {{ number_format($s->qty) }}
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">

                            {{-- Edit --}}
                            <a href="{{ route('stocks.edit', $s->id) }}"
                               class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            {{-- Hapus --}}
                            <form action="{{ route('stocks.destroy', $s->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus stok ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>

                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
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
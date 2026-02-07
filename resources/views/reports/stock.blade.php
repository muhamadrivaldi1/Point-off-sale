@extends('layouts.app')

@section('title','Laporan Stok')

@section('content')
<h3 class="mb-4">Laporan Stok</h3>

{{-- FILTER & SEARCH --}}
<form method="GET" class="row g-2 mb-4 align-items-end">
    <div class="col-md-4">
        <label class="form-label">Cari Produk</label>
        <input type="text" name="q" class="form-control"
               placeholder="Nama produk..."
               value="{{ request('q') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Lokasi</label>
        <select name="location" class="form-select">
            <option value="">-- Semua Lokasi --</option>
            <option value="toko" {{ request('location')=='toko'?'selected':'' }}>Toko</option>
            <option value="gudang" {{ request('location')=='gudang'?'selected':'' }}>Gudang</option>
        </select>
    </div>

    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">
            Filter
        </button>
    </div>

    <div class="col-md-3 text-end">
        <a href="{{ route('reports.stock') }}" class="btn btn-secondary">
            Reset
        </a>
    </div>
</form>

{{-- TABEL DATA --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th class="text-start ps-3">Produk</th>
                        <th>Unit</th>
                        <th>Lokasi</th>
                        <th class="text-end pe-3">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td class="text-start ps-3">
                            {{ $row->unit->product->name }}
                        </td>
                        <td class="text-center">
                            {{ $row->unit->unit_name }}
                        </td>
                        <td class="text-center">
                            @if($row->location === 'toko')
                                <span class="badge bg-primary bg-opacity-75">
                                    Toko
                                </span>
                            @elseif($row->location === 'gudang')
                                <span class="badge bg-success bg-opacity-75">
                                    Gudang
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-75">
                                    {{ ucfirst($row->location) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3 fw-semibold">
                            {{ number_format($row->qty) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4 fst-italic">
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
    {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection

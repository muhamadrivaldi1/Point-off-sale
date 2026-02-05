@extends('layouts.app')

@section('title','Laporan Stok')

@section('content')
<h3 class="mb-3">Laporan Stok</h3>

{{-- FILTER & SEARCH --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" class="form-control" placeholder="Cari produk..."
               value="{{ request('q') }}">
    </div>
    <div class="col-md-3">
        <select name="location" class="form-select">
            <option value="">-- Pilih Lokasi --</option>
            <option value="toko" {{ request('location')=='toko'?'selected':'' }}>Toko</option>
            <option value="gudang" {{ request('location')=='gudang'?'selected':'' }}>Gudang</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-3 text-end">
        <a href="{{ route('reports.stock') }}" class="btn btn-secondary">Reset Filter</a>
    </div>
</form>

{{-- TABEL DATA --}}
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>Produk</th>
                <th>Unit</th>
                <th>Lokasi</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr>
                <td>{{ $row->unit->product->name }}</td>
                <td>{{ $row->unit->unit_name }}</td>
                <td class="text-center">
                    @if($row->location === 'toko')
                        <span class="badge bg-primary">Toko</span>
                    @elseif($row->location === 'gudang')
                        <span class="badge bg-success">Gudang</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($row->location) }}</span>
                    @endif
                </td>
                <td class="text-end fw-bold">{{ number_format($row->qty) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Belum ada data stok</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-3">
    {{ $data->links('pagination::bootstrap-5') }}
</div>
@endsection

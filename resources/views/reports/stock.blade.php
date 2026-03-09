@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">History Mutasi Stok</h3>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <form action="{{ route('reports.stock') }}" method="GET" class="row g-2 align-items-end">
                {{-- Dari Tanggal --}}
                <div class="col-md-3">
                    <label class="small text-muted">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from', date('Y-m-01')) }}">
                </div>

                {{-- Sampai Tanggal --}}
                <div class="col-md-3">
                    <label class="small text-muted">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to', date('Y-m-d')) }}">
                </div>

                {{-- Tipe Mutasi --}}
                <div class="col-md-3">
                    <label class="small text-muted">Tipe Mutasi</label>
                    <select name="type" class="form-select">
                        <option value="">-- Semua Tipe --</option>
                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Masuk</option>
                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>

                {{-- Tombol Filter --}}
                <div class="col-md-2 d-flex">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Produk</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-end">Masuk/Keluar</th>
                            <th class="text-end">Sisa Stok</th>
                            <th class="ps-3">Ref / Ket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                {{-- Tanggal & Jam --}}
                                <td class="ps-3">
                                    <span class="d-block">{{ $row->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted">{{ $row->created_at->format('H:i') }}</small>
                                </td>

                                {{-- Produk --}}
                                <td>
                                    <strong>{{ $row->unit->product->name ?? '-' }}</strong>
                                    <br><small class="text-muted">{{ $row->unit->unit_name ?? '-' }}</small>
                                </td>

                                {{-- Tipe Mutasi --}}
                                <td class="text-center">
                                    <span class="badge {{ $row->type == 'in' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($row->type == 'in' ? 'Masuk' : 'Keluar') }}
                                    </span>
                                </td>

                                {{-- Qty Masuk/Keluar --}}
                                <td class="text-end fw-bold {{ $row->type == 'in' ? 'text-success' : 'text-danger' }}">
                                    {{ $row->type == 'in' ? '+' : '-' }}{{ number_format($row->qty) }}
                                </td>

                                {{-- Sisa Stok --}}
                                <td class="text-end fw-bold text-dark">{{ number_format($row->stock_after) }}</td>

                                {{-- Reference & Description --}}
                                <td class="ps-3">
                                    <span class="d-block small fw-bold">{{ $row->reference ?? '-' }}</span>
                                    <small class="text-muted">{{ $row->description ?? '-' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted fst-italic">
                                    Belum ada data mutasi stok pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
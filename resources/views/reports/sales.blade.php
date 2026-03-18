@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">

                {{-- HEADER --}}
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Laporan Penjualan</h5>
                    <a href="{{ route('reports.sales.csv', request()->all()) }}" class="btn btn-success btn-sm shadow-sm">
                        <i class="fas fa-file-excel"></i> Export Excel (CSV)
                    </a>
                </div>

                <div class="card-body">

                    {{-- FILTER --}}
                    <form action="{{ route('reports.sales') }}" method="GET" class="row g-3 mb-4">

                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Dari Tanggal</label>
                            <input type="date" name="from" class="form-control form-control-sm"
                                   value="{{ request('from', $from) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Sampai Tanggal</label>
                            <input type="date" name="to" class="form-control form-control-sm"
                                   value="{{ request('to', $to) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">No. Invoice</label>
                            <input type="text" name="invoice" class="form-control form-control-sm"
                                   placeholder="TRX..." value="{{ request('invoice') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Kasir</label>
                            <select name="kasir_id" class="form-select form-select-sm">
                                <option value="">-- Semua Kasir --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('kasir_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Member</label>
                            <select name="member_id" class="form-select form-select-sm">
                                <option value="">-- Semua Member --</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}"
                                        {{ request('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 🔥 FILTER BARANG --}}
                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Barang</label>
                            <input type="text" name="product" class="form-control form-control-sm"
                                   placeholder="Nama barang..."
                                   value="{{ request('product') }}">
                        </div>

                        {{-- 🔥 FILTER SUPPLIER --}}
                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Supplier</label>
                            <input type="text" name="supplier" class="form-control form-control-sm"
                                   placeholder="Nama supplier..."
                                   value="{{ request('supplier') }}">
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                            <a href="{{ route('reports.sales') }}"
                                class="btn btn-outline-danger btn-sm shadow-sm">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </form>

                    <hr>

                    {{-- TOTAL --}}
                    <div class="text-end mb-3">
                        <div class="text-muted small">Total Omzet:</div>
                        <span class="h4 text-primary fw-bold">
                            Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Invoice</th>
                                    <th>Kasir</th>
                                    <th>Member</th>
                                    <th>Barang</th>
                                    <th>Supplier</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                                @forelse($data as $trx)
                                <tr>

                                    {{-- TANGGAL --}}
                                    <td>
                                        <div class="fw-bold">
                                            {{ $trx->created_at->format('d/m/Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $trx->created_at->format('H:i') }}
                                        </small>
                                    </td>

                                    {{-- INVOICE --}}
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $trx->trx_number }}
                                        </span>
                                    </td>

                                    {{-- KASIR --}}
                                    <td>{{ $trx->user->name ?? '-' }}</td>

                                    {{-- MEMBER --}}
                                    <td>
                                        @if($trx->member)
                                            <span class="text-primary">
                                                {{ $trx->member->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">Umum</span>
                                        @endif
                                    </td>

                                    {{-- 🔥 BARANG --}}
                                    <td style="min-width:200px;">
                                        @foreach($trx->items as $item)
                                            <div style="font-size:12px;">
                                                • {{ $item->unit->product->name ?? '-' }}
                                                <span class="text-muted">
                                                    x{{ $item->qty }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </td>

                                    {{-- 🔥 SUPPLIER --}}
                                    <td style="min-width:150px;">
                                        @foreach($trx->items as $item)
                                            <div style="font-size:12px; color:#6c757d;">
                                                • {{ $item->unit->product->supplier->nama_supplier ?? '-' }}
                                            </div>
                                        @endforeach
                                    </td>

                                    {{-- TOTAL --}}
                                    <td class="fw-bold text-end">
                                        Rp {{ number_format($trx->total, 0, ',', '.') }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">
                                        <a href="{{ route('reports.sales.detail', $trx->id) }}"
                                           class="btn btn-outline-info btn-xs">
                                            Detail
                                        </a>
                                    </td>

                                </tr>

                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Tidak ada data
                                    </td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    <div class="mt-3 d-flex justify-content-between">
                        <small class="text-muted">
                            {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }}
                            dari {{ $data->total() }}
                        </small>

                        {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-xs {
    padding: 2px 6px;
    font-size: 11px;
}
</style>
@endsection
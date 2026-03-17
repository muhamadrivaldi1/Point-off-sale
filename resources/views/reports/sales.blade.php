@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Laporan Penjualan</h5>
                    <a href="{{ route('reports.sales.csv', request()->all()) }}" class="btn btn-success btn-sm shadow-sm">
                        <i class="fas fa-file-excel"></i> Export Excel (CSV)
                    </a>
                </div>
                <div class="card-body">
                    {{-- Form Filter --}}
                    <form action="{{ route('reports.sales') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Dari Tanggal</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from', $from) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Sampai Tanggal</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to', $to) }}">
                        </div>
                        
                        {{-- Filter Invoice --}}
                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">No. Invoice</label>
                            <input type="text" name="invoice" class="form-control form-control-sm" placeholder="Contoh: TRX..." value="{{ request('invoice') }}">
                        </div>

                        {{-- Filter Kasir --}}
                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Kasir</label>
                            <select name="kasir_id" class="form-select form-select-sm">
                                <option value="">-- Semua Kasir --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('kasir_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Member --}}
                        <div class="col-md-2">
                            <label class="small fw-bold text-secondary">Member</label>
                            <select name="member_id" class="form-select form-select-sm">
                                <option value="">-- Semua Member --</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                            <a href="{{ route('reports.sales') }}" class="btn btn-outline-danger btn-sm shadow-sm" title="Hapus Semua Filter">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </form>

                    <hr class="mb-4">

                    <div class="row mb-3">
                        <div class="col-md-12 text-end">
                            <div class="text-muted small">Total Omzet Terfilter:</div>
                            <span class="h4 text-primary fw-bold">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Invoice</th>
                                    <th>Kasir</th>
                                    <th>Member</th>
                                    <th class="text-end">Total Bayar</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $trx)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $trx->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $trx->created_at->format('H:i') }} WIB</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border fw-normal">{{ $trx->trx_number }}</span></td>
                                    <td>{{ $trx->user->name ?? '-' }}</td>
                                    <td>
                                        @if($trx->member)
                                            <span class="text-primary"><i class="fas fa-user-tag small"></i> {{ $trx->member->name }}</span>
                                        @else
                                            <span class="text-muted">Umum</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-end">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('reports.sales.detail', $trx->id) }}" class="btn btn-outline-info btn-xs">
                                            <i class="fas fa-search-plus"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open d-block mb-2 fa-2x"></i>
                                        Data tidak ditemukan untuk filter ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Menampilkan {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }} dari {{ $data->total() }} data
                        </div>
                        <div>
                            {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
</style>
@endsection
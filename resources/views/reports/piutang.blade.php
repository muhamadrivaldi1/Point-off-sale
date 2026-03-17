@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="bi bi-person-lines-fill me-2"></i>Laporan Piutang Pelanggan
            </h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger fs-6 px-3 py-2 shadow-sm">
                    Total Sisa Piutang : Rp {{ number_format($totalSisaPiutang, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="card-body">
            {{-- FILTER FORM --}}
            <form method="GET" action="{{ route('reports.piutang') }}" class="row mb-4 g-3">
                
                {{-- Cari Invoice atau Nama Pelanggan --}}
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Cari Invoice / Nama Pelanggan</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Masukkan No. Invoice atau Nama..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filter Status --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Status Pembayaran</label>
                    <select name="status" class="form-select form-select-sm shadow-none" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="cicilan" {{ request('status') == 'cicilan' ? 'selected' : '' }}>Cicilan (Parsial)</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>

                {{-- Range Tanggal --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>

                {{-- Action Buttons --}}
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.piutang.export', request()->query()) }}" class="btn btn-success btn-sm" title="Download Excel/CSV">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </a>
                    <a href="{{ route('reports.piutang') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>

            <hr class="mb-4 opacity-10">

            {{-- TABEL DATA --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th class="py-3 ps-3" style="width: 120px;">Tanggal</th>
                            <th>No Invoice</th>
                            <th>Pelanggan</th>
                            <th class="text-end">Total TRX</th>
                            <th class="text-end text-primary">Sudah Dibayar</th>
                            <th class="text-end text-danger">Sisa Hutang</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($data as $row)
                            @php
                                $dibayar = $row->total_terbayar ?? 0;
                                $sisa = $row->total - $dibayar;
                            @endphp
                            <tr>
                                <td class="ps-3 text-muted">{{ $row->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $row->trx_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ optional($row->member)->name ?? 'Pelanggan Umum' }}</div>
                                    @if($row->member)
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $row->member->phone }}</div>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                <td class="text-end text-primary fw-medium">
                                    Rp {{ number_format($dibayar, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    Rp {{ number_format($sisa, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($sisa <= 0)
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success px-3">LUNAS</span>
                                    @elseif($dibayar > 0)
                                        <span class="badge rounded-pill bg-info-subtle text-info border border-info px-3">CICILAN</span>
                                    @else
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning px-3">BELUM BAYAR</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('kredit.detail', $row->id) }}" class="btn btn-sm btn-outline-primary px-3 shadow-sm" style="font-size: 0.7rem;">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-search fs-1 opacity-25"></i></div>
                                    <div>Data piutang tidak ditemukan. Coba ubah kata kunci atau filter status.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan <strong>{{ $data->firstItem() ?? 0 }}</strong> sampai <strong>{{ $data->lastItem() ?? 0 }}</strong> dari <strong>{{ $data->total() }}</strong> transaksi
                </div>
                <div>
                    {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
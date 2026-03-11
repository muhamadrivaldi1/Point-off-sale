@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="bi bi-person-lines-fill me-2"></i>Laporan Piutang Pelanggan
            </h5>
            <span class="badge bg-danger fs-6 px-3 py-2 shadow-sm">
                Total Sisa Piutang : Rp {{ number_format($totalSisaPiutang, 0, ',', '.') }}
            </span>
        </div>

        <div class="card-body">
            {{-- FILTER FORM --}}
            <form method="GET" action="{{ route('reports.piutang') }}" class="row mb-4 g-3">
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Cari Pelanggan / Invoice</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nama atau No. TRX..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status Pembayaran</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="cicilan" {{ request('status') == 'cicilan' ? 'selected' : '' }}>Cicilan</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.piutang.export', request()->query()) }}" class="btn btn-success btn-sm shadow-sm">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export
                    </a>
                    <a href="{{ route('reports.piutang') }}" class="btn btn-outline-secondary btn-sm shadow-sm" title="Reset">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>

            {{-- TABEL DATA --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr class="text-secondary small">
                            <th class="py-3">TANGGAL</th>
                            <th>NO INVOICE</th>
                            <th>PELANGGAN</th>
                            <th class="text-end">TOTAL TRX</th>
                            <th class="text-end text-primary">DIBAYAR</th>
                            <th class="text-end text-danger">SISA HUTANG</th>
                            <th class="text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($data as $row)
                            @php
                                $dibayar = $row->total_terbayar ?? 0;
                                $sisa = $row->total - $dibayar;
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $row->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-normal">
                                        {{ $row->trx_number }}
                                    </span>
                                </td>
                                <td class="fw-bold">{{ optional($row->member)->name ?? 'Pelanggan Umum' }}</td>
                                <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                <td class="text-end text-primary fw-bold">
                                    Rp {{ number_format($dibayar, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    Rp {{ number_format($sisa, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($dibayar >= $row->total && $row->total > 0)
                                        <span class="badge bg-success shadow-sm" style="font-size: 0.7rem;">LUNAS</span>
                                    @elseif($dibayar > 0)
                                        <span class="badge bg-info text-dark shadow-sm" style="font-size: 0.7rem;">CICILAN</span>
                                    @else
                                        <span class="badge bg-warning text-dark shadow-sm" style="font-size: 0.7rem;">BELUM BAYAR</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x d-block fs-2 mb-2"></i>
                                    Data tidak ditemukan untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4">
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
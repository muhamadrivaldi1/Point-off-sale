@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="bi bi-truck me-2"></i>Laporan Hutang Supplier
            </h5>
            <span class="badge bg-danger fs-6 px-3 py-2">
                Total Sisa Hutang : Rp {{ number_format($totalHutang, 0, ',', '.') }}
            </span>
        </div>

        <div class="card-body">
            {{-- FILTER --}}
            <form method="GET" action="{{ route('reports.hutang') }}" class="row mb-4 g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Cari Supplier / No. PO</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nama atau No. PO..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Lunas (Received)</option>
                        <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Belum Bayar (Draft)</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Dari</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Sampai</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 shadow-sm">
                        <i class="bi bi-filter me-1"></i> Cari
                    </button>
                    
                    {{-- TOMBOL EXPORT --}}
                    <a href="{{ route('reports.hutang.export', request()->all()) }}" class="btn btn-success shadow-sm">
                        <i class="bi bi-file-earmark-excel"></i> Export
                    </a>

                    <a href="{{ route('reports.hutang') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>

            {{-- TABEL DATA --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr class="text-secondary small">
                            <th>TANGGAL</th>
                            <th>NO. PO</th>
                            <th>SUPPLIER</th>
                            <th class="text-end">TOTAL TRX</th>
                            <th class="text-end text-primary">DIBAYAR</th>
                            <th class="text-end text-danger">SISA HUTANG</th>
                            <th class="text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        @php
                            $currentStatus = strtolower($row->status);
                            $isPaid = in_array($currentStatus, ['received', 'paid']);
                            
                            $dibayar = $isPaid ? $row->total : 0;
                            $sisaHutang = $isPaid ? 0 : $row->total;
                        @endphp
                        <tr>
                            <td class="small text-muted">
                                {{ $row->tanggal ? $row->tanggal->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-normal">
                                    {{ $row->po_number }}
                                </span>
                            </td>
                            <td class="fw-bold text-uppercase">
                                {{ $row->supplier->nama_supplier ?? 'Supplier Umum' }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($row->total, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-primary">
                                Rp {{ number_format($dibayar, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-danger fw-bold">
                                Rp {{ number_format($sisaHutang, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($isPaid)
                                    <span class="badge bg-success" style="font-size: 10px;">LUNAS</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size: 10px;">{{ strtoupper($row->status ?? 'BELUM BAYAR') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                                Tidak ada catatan hutang ditemukan
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
@endsection
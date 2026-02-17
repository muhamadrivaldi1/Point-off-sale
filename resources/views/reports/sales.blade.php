@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>📊 Laporan Penjualan</h3>
    </div>

    {{-- FORM FILTER --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="from" class="form-label">Dari Tanggal</label>
                    <input type="date" name="from" id="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label">Sampai Tanggal</label>
                    <input type="date" name="to" id="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">🔍 Filter</button>
                </div>
                <div class="col-md-2 align-self-end">
                    <a href="{{ route('reports.sales') }}" class="btn btn-secondary w-100">🔄 Reset</a>
                </div>
                <div class="col-md-2 align-self-end">
                    <a href="{{ route('reports.sales.csv', request()->query()) }}" class="btn btn-success w-100">📥 Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    {{-- INFO RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">📅 Periode</h6>
                    <h5 class="card-title">{{ date('d/m/Y', strtotime($from)) }} - {{ date('d/m/Y', strtotime($to)) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">🧾 Total Transaksi</h6>
                    <h5 class="card-title">{{ $data->total() }} transaksi</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">💰 Total Omzet</h6>
                    <h5 class="card-title">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL LAPORAN --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr class="table-primary">
                            <th width="50">No</th>
                            <th width="150">Tanggal</th>
                            <th width="200">Invoice</th>
                            <th width="120">Kasir</th>
                            <th width="150">Member</th>
                            <th width="120" class="text-end">Total</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $trx)
                        <tr>
                            <td>{{ $data->firstItem() + $loop->index }}</td>
                            <td>{{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                            <td><strong>{{ $trx->trx_number ?? '-' }}</strong></td>
                            <td>{{ $trx->user->name ?? '-' }}</td>
                            <td>
                                @if($trx->member)
                                    <span class="badge bg-primary">{{ $trx->member->name }}</span>
                                @else
                                    <span class="badge bg-secondary">Non-Member</span>
                                @endif
                            </td>
                            <td class="text-end"><strong>Rp {{ number_format($trx->total, 0, ',', '.') }}</strong></td>
                            <td class="text-center">
                                <a href="{{ route('reports.sales.detail', $trx->id) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                    📄 Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div style="font-size: 48px;">📭</div>
                                <p class="mt-2 mb-0">Tidak ada data transaksi pada periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                    <tfoot class="table-secondary">
                        <tr class="table-success">
                            <th colspan="5" class="text-end">TOTAL HALAMAN INI:</th>
                            <th class="text-end">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if($data->hasPages())
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="text-muted">
            Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} transaksi
        </div>
        <div>
            {{ $data->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif

    {{-- TIPS --}}
    <div class="alert alert-info mt-3">
        <strong>💡 Tips:</strong>
        <ul class="mb-0 mt-2">
            <li>Gunakan <strong>filter tanggal</strong> untuk melihat transaksi pada periode tertentu</li>
            <li>Klik <strong>Export CSV</strong> untuk download laporan lengkap yang bisa dibuka di Excel</li>
            <li>Klik <strong>Detail</strong> untuk melihat item yang dibeli per transaksi</li>
            <li>Laporan CSV mencakup: ringkasan transaksi, detail item, dan rekap produk terlaris</li>
        </ul>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table thead th {
        font-weight: 600;
        vertical-align: middle;
    }
    .table tbody td {
        vertical-align: middle;
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endpush
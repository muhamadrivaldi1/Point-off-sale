@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Laporan Penjualan</h5>
                    <a href="{{ route('reports.sales.csv', request()->all()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel (CSV)
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.sales') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="small mb-1">Dari Tanggal</label>
                            <input type="date" name="from" class="form-control" value="{{ $from }}">
                        </div>
                        <div class="col-md-3">
                            <label class="small mb-1">Sampai Tanggal</label>
                            <input type="date" name="to" class="form-control" value="{{ $to }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Cari</button>
                        </div>
                        <div class="col-md-4 d-flex align-items-end justify-content-end">
                            <div class="text-end text-muted small">
                                Total Omzet:<br>
                                <span class="h4 text-dark font-weight-bold">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Invoice</th>
                                    <th>Kasir</th>
                                    <th>Member</th>
                                    <th>Total Bayar</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $trx)
                                <tr>
                                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $trx->trx_number }}</span></td>
                                    <td>{{ $trx->user->name ?? '-' }}</td>
                                    <td>{{ $trx->member->name ?? 'Umum' }}</td>
                                    <td class="fw-bold">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('reports.sales.detail', $trx->id) }}" class="btn btn-outline-info btn-sm">
                                            🔍 Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Data tidak ditemukan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
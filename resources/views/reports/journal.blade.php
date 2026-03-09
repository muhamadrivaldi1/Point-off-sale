@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">
                Laporan Journal
            </h5>

            <span class="badge bg-info fs-6">
                Total Transaksi: Rp {{ number_format($data->sum('total'), 0, ',', '.') }}
            </span>
        </div>

        <div class="card-body">

            {{-- FILTER TANGGAL --}}
            <form method="GET" action="{{ route('reports.journal') }}" class="row mb-4">

                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Cari</button>
                </div>

            </form>

            {{-- TABEL DATA JOURNAL --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="120">Tanggal</th>
                            <th width="170">No. Transaksi</th>
                            <th>Kasir</th>
                            <th>Pelanggan</th>
                            <th width="180">Total</th>
                            <th width="150">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td>{{ $row->created_at ? $row->created_at->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $row->trx_number }}
                                </span>
                            </td>
                            <td>{{ optional($row->user)->name ?? '-' }}</td>
                            <td>{{ optional($row->member)->name ?? 'Umum' }}</td>
                            <td class="fw-bold text-danger">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                            <td>
                                @if($row->status == 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                @elseif($row->status == 'partial')
                                    <span class="badge bg-info">Dibayar Sebagian</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum Bayar</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Tidak ada transaksi pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-3">
                {{ $data->links() }}
            </div>

        </div>

    </div>

</div>
@endsection
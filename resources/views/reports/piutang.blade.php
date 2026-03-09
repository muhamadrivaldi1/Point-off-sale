@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">
                Laporan Piutang Pelanggan
            </h5>

            <span class="badge bg-danger fs-6">
                Total Piutang :
                Rp {{ number_format($data->sum('total'),0,',','.') }}
            </span>
        </div>

        <div class="card-body">

            {{-- FILTER TANGGAL --}}
            <form method="GET" action="{{ route('reports.piutang') }}" class="row mb-4">

                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input
                        type="date"
                        name="from"
                        class="form-control"
                        value="{{ $from }}"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input
                        type="date"
                        name="to"
                        class="form-control"
                        value="{{ $to }}"
                    >
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        Cari
                    </button>
                </div>

            </form>

            {{-- TABEL --}}
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="120">Tanggal</th>
                            <th width="170">No Invoice</th>
                            <th>Nama Pelanggan</th>
                            <th width="180">Total Transaksi</th>
                            <th width="150">Status</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($data as $row)

                        <tr>

                            <td>
                                {{ $row->created_at ? $row->created_at->format('d/m/Y') : '-' }}
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $row->trx_number }}
                                </span>
                            </td>

                            <td>
                                {{ optional($row->member)->name ?? 'Pelanggan Umum' }}
                            </td>

                            <td class="fw-bold text-danger">
                                Rp {{ number_format($row->total,0,',','.') }}
                            </td>

                            <td>

                                @if($row->status == 'partial')

                                    <span class="badge bg-info">
                                        Dibayar Sebagian
                                    </span>

                                @elseif($row->status == 'unpaid')

                                    <span class="badge bg-warning text-dark">
                                        Belum Bayar
                                    </span>

                                @else

                                    <span class="badge bg-success">
                                        Lunas
                                    </span>

                                @endif

                            </td>

                            <td class="text-center">

                                <a
                                    href="{{ route('reports.sales.detail',$row->id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Cek Nota
                                </a>

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Tidak ada data piutang pada periode ini
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
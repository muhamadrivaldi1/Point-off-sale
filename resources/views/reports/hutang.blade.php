@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">
                Laporan Hutang Supplier
            </h5>

            <span class="badge bg-warning fs-6">
                Total Hutang :
                Rp {{ number_format($data->sum('total'),0,',','.') }}
            </span>
        </div>

        <div class="card-body">

            {{-- FILTER TANGGAL --}}
            <form method="GET" action="{{ route('reports.hutang') }}" class="row mb-4">

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

            {{-- TABEL DATA HUTANG --}}
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="130">Tanggal Beli</th>
                            <th width="180">No. Purchase</th>
                            <th>Supplier</th>
                            <th width="130">Jatuh Tempo</th>
                            <th width="180">Total Tagihan</th>
                            {{-- <th width="120" class="text-center">Aksi</th> --}}
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($data as $row)

                        <tr>

                            <td>
                                {{ $row->tanggal ? $row->tanggal->format('d/m/Y') : '-' }}
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $row->po_number }}
                                </span>
                            </td>

                            <td>
                                {{ optional($row->supplier)->nama_supplier ?? 'Supplier Umum' }}
                            </td>

                            <td>
                                {{ $row->tanggal_jatuh_tempo ? $row->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}
                            </td>

                            <td class="fw-bold text-danger">
                                Rp {{ number_format($row->total,0,',','.') }}
                            </td>

                            {{-- <td class="text-center"> --}}
                                {{-- Tombol Bayar Hutang --}}
                                {{-- <a
                                    href="{{ route('reports.hutang.pay', $row->id) }}"
                                    class="btn btn-sm btn-outline-success"
                                >
                                    Bayar Hutang
                                </a>
                            </td> --}}

                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Tidak ada catatan hutang pada periode ini
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
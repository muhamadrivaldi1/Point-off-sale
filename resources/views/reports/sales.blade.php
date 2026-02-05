@extends('layouts.app')

@section('content')
<h3>Laporan Penjualan</h3>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <label for="from" class="form-label">Dari Tanggal</label>
        <input type="date" name="from" id="from"
               value="{{ request('from') ?? '' }}"
               class="form-control">
    </div>

    <div class="col-md-3">
        <label for="to" class="form-label">Sampai Tanggal</label>
        <input type="date" name="to" id="to"
               value="{{ request('to') ?? '' }}"
               class="form-control">
    </div>

    <div class="col-md-2 align-self-end">
        <button type="submit" class="btn btn-primary w-100">
            Filter
        </button>
    </div>

   <div class="col-md-4 align-self-end text-end">
    <a href="{{ route('reports.sales.csv', request()->query()) }}"
       class="btn btn-success btn-sm">
        Export CSV
    </a>
</div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $trx)
            <tr>
                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $trx->trx_number ?? '-' }}</td>
                <td>Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                <td>
                    <a href="{{ route('reports.sales.detail', $trx->id) }}"
                       class="btn btn-sm btn-info">
                        Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">
                    Tidak ada data
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- 🔥 PAGINATION --}}
<div class="mt-3">
    {{ $data->links('pagination::bootstrap-5') }}
</div>
@endsection

@extends('layouts.app')

@section('title','Transaksi')

@section('content')
<h4 class="mb-3">Data Transaksi</h4>

{{-- FILTER --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" class="form-control"
               placeholder="Cari Invoice..."
               value="{{ request('q') }}">
    </div>
    <div class="col-md-3">
        <input type="date" name="date" class="form-control"
               value="{{ request('date') }}">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-3 text-end">
        <a href="{{ route('transactions.index') }}"
           class="btn btn-secondary">Reset</a>
    </div>
</form>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
<thead class="table-light text-center">
<tr>
    <th width="50">No</th>
    <th class="text-start">Tanggal</th>
    <th class="text-start">Invoice</th>
    <th class="text-start">Total</th>
    <th width="220">Aksi</th>
</tr>
</thead>

<tbody>
@forelse($data as $trx)
@php
    $pendingRequest = $trx->requests->where('status','pending')->first();
    $approvedRequest = $trx->requests->where('status','approved')->first();
@endphp
<tr>
    <td class="text-center">{{ $loop->iteration }}</td>

    <td class="text-start">
        {{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
    </td>

    <td class="text-start">
        {{ $trx->trx_number }}

        @if($pendingRequest)
            <span class="badge bg-warning text-dark ms-1">
                Request Edit
            </span>
        @elseif($approvedRequest)
            <span class="badge bg-success text-white ms-1">
                Sudah diperbaiki
            </span>
        @endif
    </td>

    <td class="text-start fw-bold">
        Rp {{ number_format($trx->total, 0, ',', '.') }}
    </td>

    <td class="text-center">

        {{-- OWNER --}}
        @if(auth()->user()->role === 'owner')
            @if($pendingRequest)
                {{-- Review dan Approve --}}
                <a href="{{ route('transactions.edit', $trx->id) }}"
                   class="btn btn-warning btn-sm">
                    Review Edit
                </a>
            @elseif($approvedRequest)
                {{-- Tombol Approve --}}
                <form action="{{ route('transactions.approve', $trx->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success btn-sm">
                        Approve
                    </button>
                </form>
            @else
                <span class="text-muted">-</span>
            @endif

        {{-- KASIR --}}
        @elseif(auth()->user()->role === 'kasir')
            @if(!$pendingRequest && !$approvedRequest)
                {{-- Request perbaikan --}}
                <button class="btn btn-outline-danger btn-sm"
                        onclick="requestEdit({{ $trx->id }})">
                    Request Perbaikan
                </button>
            @elseif($approvedRequest)
                <span class="badge bg-success text-white">
                    Sudah di-approve
                </span>
            @else
                <span class="text-muted">Sudah request</span>
            @endif
        @endif

    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center text-muted">
        Belum ada transaksi
    </td>
</tr>
@endforelse
</tbody>
</table>
</div>

{{-- PAGINATION --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $data->links('pagination::bootstrap-5') }}
</div>

{{-- SCRIPT --}}
<script>
function requestEdit(trxId) {
    const message = prompt('Alasan perbaikan transaksi? (min 10 karakter)');
    if (!message || message.length < 10) {
        alert('Alasan minimal 10 karakter');
        return;
    }

    fetch(`/transactions/${trxId}/request-edit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message })
    })
    .then(res => res.json())
    .then(res => {
        alert(res.message);
        location.reload();
    })
    .catch(() => alert('Gagal mengirim request'));
}
</script>
@endsection

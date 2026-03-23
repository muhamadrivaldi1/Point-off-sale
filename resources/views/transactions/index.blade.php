@extends('layouts.app')

@section('title','Transaksi')

@section('content')
<h4 class="mb-3">Data Transaksi</h4>

{{-- ================= FILTER ================= --}}
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

{{-- ================= TABLE ================= --}}
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light text-center">
            <tr>
                <th width="50">No</th>
                <th class="text-start">Tanggal</th>
                <th class="text-start">Invoice</th>
                <th class="text-start">Pelanggan</th> {{-- Kolom Baru --}}
                <th class="text-start">Total</th>
                <th>Status Bayar</th>
                <th width="320">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($data as $trx)
            @php
                $pendingRequest  = $trx->requests->where('status','pending')->first();
                $approvedRequest = $trx->requests->where('status','approved')->first();
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>

                <td class="text-start">
                    {{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                </td>

                <td class="text-start">
                    <span class="fw-bold">{{ $trx->trx_number }}</span>

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

                {{-- KOLOM PELANGGAN/MEMBER --}}
                <td class="text-start">
                    @if($trx->member)
                        <span class="badge bg-info text-dark">MEMBER</span><br>
                        {{ $trx->member->name }}
                    @else
                        {{ $trx->buyer_name ?? 'Umum' }}
                    @endif
                </td>

                <td class="text-start fw-bold text-primary">
                    Rp {{ number_format($trx->total, 0, ',', '.') }}
                </td>

                {{-- STATUS BAYAR --}}
                <td class="text-center">
                    @if($trx->status === 'paid')
                        <span class="badge bg-success">PAID</span>
                    @else
                        <span class="badge bg-warning text-dark">PENDING</span>
                    @endif
                </td>

                {{-- ================= AKSI ================= --}}
                <td class="text-center">
                    
                    {{-- ================= TRANSAKSI PENDING ================= --}}
                    @if($trx->status === 'pending')
                        {{-- LANJUTKAN POS --}}
                        <a href="{{ route('pos', ['trx_id' => $trx->id]) }}" 
                           class="btn btn-primary btn-sm mb-1">
                            Bayar / Lanjutkan
                        </a>
                        {{-- HAPUS --}}
                        <button class="btn btn-danger btn-sm mb-1" 
                                onclick="deleteTransaction({{ $trx->id }})">
                            Hapus
                        </button>

                    {{-- ================= TRANSAKSI PAID ================= --}}
                    @else
                        {{-- CETAK STRUK --}}
                        <a href="{{ route('transactions.struk', $trx->id) }}" 
                           target="_blank" 
                           class="btn btn-outline-dark btn-sm mb-1">
                            Cetak Struk
                        </a>

                        {{-- OWNER --}}
                        @if(auth()->user()->role === 'owner')
                            @if($pendingRequest)
                                <a href="{{ route('transactions.edit', $trx->id) }}" 
                                   class="btn btn-warning btn-sm mb-1">
                                    Review Edit
                                </a>
                            @elseif($approvedRequest)
                                <form action="{{ route('transactions.approve', $trx->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" 
                                            class="btn btn-success btn-sm mb-1">
                                        Approve
                                    </button>
                                </form>
                            @endif

                        {{-- KASIR --}}
                        @elseif(auth()->user()->role === 'kasir')
                            @if(!$pendingRequest && !$approvedRequest)
                                <button class="btn btn-outline-danger btn-sm mb-1" 
                                        onclick="requestEdit({{ $trx->id }})">
                                    Request Perbaikan
                                </button>
                            @elseif($approvedRequest)
                                <span class="badge bg-success">
                                    Sudah di-approve
                                </span>
                            @endif
                        @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Belum ada transaksi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ================= PAGINATION ================= --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $data->links('pagination::bootstrap-5') }}
</div>

{{-- ================= SCRIPT ================= --}}
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
        alert(res.message || 'Berhasil');
        location.reload();
    })
    .catch(() => alert('Gagal mengirim request'));
}

function deleteTransaction(trxId) {
    if (!confirm('Yakin ingin menghapus transaksi ini?')) return;

    fetch(`/transactions/${trxId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ _method: 'DELETE' })
    })
    .then(() => location.reload())
    .catch(() => alert('Gagal menghapus transaksi'));
}
</script>
@endsection
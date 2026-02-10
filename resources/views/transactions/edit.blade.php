@extends('layouts.app')

@section('title','Edit Transaksi')

@section('content')
<h4 class="mb-3">Edit Transaksi</h4>

{{-- REQUEST DARI KASIR --}}
@if($trx->requests->count())
<div class="alert alert-warning">
    <strong>Permintaan Perbaikan:</strong>
    <ul class="mb-0">
        @foreach($trx->requests as $r)
            <li>
                <strong>{{ $r->user->name }}</strong> :
                {{ $r->message }}
            </li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('transactions.update', $trx->id) }}">
    @csrf
    @method('PUT')

    {{-- No Invoice (Read-only) --}}
    <div class="mb-3">
        <label>No Invoice</label>
        <p class="form-control-plaintext">{{ $trx->trx_number ?? '-' }}</p>
    </div>

    {{-- Total Transaksi --}}
    <div class="mb-3">
        <label>Total Transaksi</label>
        <input type="number"
               name="total"
               value="{{ $trx->total }}"
               class="form-control"
               required
               min="0"
               step="0.01">
    </div>

    <hr>

    {{-- Item Transaksi --}}
    <h5>Item Transaksi</h5>
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th>Produk</th>
                <th width="100">Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trx->items as $item)
            <tr>
                <td>
                    {{ optional(optional($item->unit)->product)->name ?? '-' }}
                    <br>
                    <small class="text-muted">
                        {{ optional($item->unit)->unit_name ?? '' }}
                    </small>
                </td>
                <td>
                    <input type="number" name="qty[{{ $item->id }}]"
                           value="{{ $item->qty }}"
                           class="form-control form-control-sm"
                           min="1"
                           required>
                </td>
                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-light">
                <th colspan="3" class="text-end">Total</th>
                <th>Rp {{ number_format($trx->items->sum('subtotal'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    {{-- Action Buttons --}}
    <div class="mt-3 d-flex gap-2 flex-wrap">

        {{-- Simpan Perubahan --}}
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

        {{-- Kembali --}}
        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Kembali</a>

        {{-- Approve (Owner) --}}
        @if(auth()->user()->role === 'owner' && $trx->requests->where('status','pending')->count())
        <form method="POST" action="{{ route('transactions.approve', $trx->id) }}">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-success"
                    onclick="return confirm('Yakin approve transaksi ini?')">
                Approve
            </button>
        </form>
        @endif

        {{-- Cancel / Hapus (Owner atau Kasir jika pending) --}}
        @if($trx->status === 'pending' && in_array(auth()->user()->role, ['owner','kasir']))
        <button type="button" class="btn btn-danger" onclick="cancelTransaction({{ $trx->id }})">
            Cancel
        </button>
        @endif
    </div>
</form>

{{-- ================= SCRIPT ================= --}}
<script>
function cancelTransaction(trxId) {
    if(!confirm('Yakin ingin membatalkan transaksi ini?')) return;

    fetch(`/transactions/${trxId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            alert(res.message);
            window.location.href = "{{ route('transactions.index') }}";
        } else {
            alert(res.message || 'Gagal membatalkan transaksi');
        }
    })
    .catch(()=>alert('Gagal membatalkan transaksi'));
}
</script>
@endsection

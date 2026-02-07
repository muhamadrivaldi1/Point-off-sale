@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Buka Sesi Kasir</h4>
    <p>Silakan isi saldo awal kasir untuk memulai transaksi.</p>

    <form action="{{ route('cashier.open') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Saldo Awal (Rp)</label>
            <input
                type="number"
                name="opening_balance"
                class="form-control"
                required
                min="0">
        </div>

        <button type="submit" class="btn btn-primary">
            Buka Sesi
        </button>
    </form>
</div>
@endsection

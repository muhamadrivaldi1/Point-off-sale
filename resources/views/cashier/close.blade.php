@extends('layouts.app')

@section('title','Tutup Sesi Kasir')

@section('content')
<div class="container mt-4">
    <h4>Tutup Sesi Kasir</h4>

    <form method="POST" action="{{ route('cashier.close') }}">
        @csrf
        <div class="mb-3">
            <label>Saldo Akhir</label>
            <input type="number" name="closing_balance" class="form-control" required>
        </div>

        <button class="btn btn-danger">
            <i class="bi bi-door-closed"></i> Tutup Sesi
        </button>
    </form>
</div>
@endsection

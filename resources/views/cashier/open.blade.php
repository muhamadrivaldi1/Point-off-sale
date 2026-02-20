@extends('layouts.app')

@section('title','Buka Sesi Kasir')

@section('content')
<div class="container mt-4">
    <h4>Buka Sesi Kasir</h4>

    <form method="POST" action="{{ route('cashier.open') }}">
        @csrf

        <div class="mb-3">
            <label>Saldo Awal</label>
            <input type="text" class="form-control" value="500000" readonly>
            <input type="hidden" name="opening_balance" value="500000">
        </div>

        <button class="btn btn-primary">
            <i class="bi bi-door-open"></i> Buka Sesi
        </button>
    </form>
</div>
@endsection
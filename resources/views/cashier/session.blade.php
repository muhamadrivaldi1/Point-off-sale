@extends('layouts.app')

@section('title','Sesi Kasir')

@section('content')
<div class="container mt-4">

    <h4>Sesi Kasir</h4>

    @if($session)
        <div class="alert alert-success">
            <strong>Sesi aktif</strong><br>
            Saldo awal: Rp {{ number_format($session->opening_balance) }}
        </div>

        <form method="POST" action="{{ route('cashier.close') }}">
            @csrf
            <button class="btn btn-danger">
                Tutup Sesi Kasir
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('cashier.open') }}">
            @csrf
            <div class="mb-3">
                <label>Saldo Awal</label>
                <input type="number" name="opening_balance" class="form-control" required>
            </div>
            <button class="btn btn-success">
                Buka Sesi Kasir
            </button>
        </form>
    @endif

</div>
@endsection

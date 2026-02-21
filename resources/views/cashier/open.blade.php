@extends('layouts.app')

@section('title','Buka Sesi Kasir')

@section('content')
<div class="container mt-4">
    <h4>Buka Sesi Kasir</h4>

    <form method="POST" action="{{ route('cashier.open') }}">
        @csrf

        <div class="mb-3">
            <label>Saldo Awal</label>

            {{-- Jika user owner, bisa ubah --}}
            @if(auth()->user()->role === 'owner')
                <input type="number" class="form-control" name="opening_balance" 
                       value="{{ $openingBalance }}" required>
            @else
                {{-- Jika user kasir, read-only --}}
                <input type="text" class="form-control" 
                       value="Rp {{ number_format($openingBalance,0,',','.') }}" readonly>
                <input type="hidden" name="opening_balance" value="{{ $openingBalance }}">
            @endif
        </div>

        <button class="btn btn-primary">
            <i class="bi bi-door-open"></i> Buka Sesi
        </button>
    </form>
</div>
@endsection
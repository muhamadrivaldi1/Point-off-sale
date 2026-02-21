@extends('layouts.app')

@section('title','Sesi Kasir')

@section('content')
<div class="container">
    <h4 class="mb-3">Riwayat Sesi Kasir</h4>

    {{-- ALERT PEMBERITAHUAN --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- FORM SALDO AWAL UNTUK OWNER --}}
    @if(Auth::user()->role === 'owner')
        <form method="POST" action="{{ route('cashier.updateOpeningBalance') }}" class="mb-3">
            @csrf
            <div class="input-group">
                <span class="input-group-text">Saldo Awal</span>
                <input type="number" name="opening_balance" class="form-control" 
                       value="{{ $openingBalance }}" required>
                <button class="btn btn-primary" type="submit">Update</button>
            </div>
        </form>
    @endif

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        @if(Auth::user()->role === 'owner')
                            <th>Saldo Awal</th>
                        @endif
                        <th>Cash</th>
                        <th>Transfer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Dibuka</th>
                        <th>Ditutup</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sessions as $session)
                        <tr class="text-center">
                            <td>{{ $sessions->firstItem() + $loop->index }}</td>

                            @if(Auth::user()->role === 'owner')
                                <td>Rp {{ number_format($session->opening_balance,0,',','.') }}</td>
                            @endif

                            <td class="text-success fw-bold">Rp {{ number_format($session->cash_total,0,',','.') }}</td>
                            <td class="text-primary fw-bold">Rp {{ number_format($session->transfer_total,0,',','.') }}</td>
                            <td class="fw-bold">Rp {{ number_format($session->grand_total,0,',','.') }}</td>
                            <td>
                                @if($session->status == 'open')
                                    <span class="badge bg-success">OPEN</span>
                                @else
                                    <span class="badge bg-secondary">CLOSED</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($session->opened_at)->format('d M Y H:i') }}</td>
                            <td>{{ $session->closed_at ? \Carbon\Carbon::parse($session->closed_at)->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role === 'owner' ? 8 : 7 }}" class="text-center">
                                Belum ada sesi kasir
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- PAGINATION --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $sessions->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
@endsection
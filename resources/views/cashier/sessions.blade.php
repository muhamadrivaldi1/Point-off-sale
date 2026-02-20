@extends('layouts.app')

@section('title','Sesi Kasir')

@section('content')
<div class="container">
    <h4 class="mb-3">Riwayat Sesi Kasir</h4>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Saldo Awal</th>
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

                            <td>
                                Rp {{ number_format($session->opening_balance,0,',','.') }}
                            </td>

                            <td class="text-success fw-bold">
                                Rp {{ number_format($session->cash_total,0,',','.') }}
                            </td>

                            <td class="text-primary fw-bold">
                                Rp {{ number_format($session->transfer_total,0,',','.') }}
                            </td>

                            <td class="fw-bold">
                                Rp {{ number_format($session->grand_total,0,',','.') }}
                            </td>

                            <td>
                                @if($session->status == 'open')
                                    <span class="badge bg-success">OPEN</span>
                                @else
                                    <span class="badge bg-secondary">CLOSED</span>
                                @endif
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($session->opened_at)->format('d M Y H:i') }}
                            </td>

                            <td>
                                {{ $session->closed_at 
                                    ? \Carbon\Carbon::parse($session->closed_at)->format('d M Y H:i') 
                                    : '-' }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
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
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">Bayar Hutang Supplier</h5>
        </div>
        <div class="card-body">
            <p><strong>No. Purchase:</strong> {{ $po->po_number }}</p>
            <p><strong>Supplier:</strong> {{ optional($po->supplier)->nama_supplier ?? 'Supplier Umum' }}</p>
            <p><strong>Total Hutang:</strong> Rp {{ number_format($po->total,0,',','.') }}</p>
            <p><strong>Jatuh Tempo:</strong> {{ $po->tanggal_jatuh_tempo ? $po->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}</p>

            <a href="{{ route('reports.hutang') }}" class="btn btn-secondary">Kembali</a>

            {{-- Tambahkan form pembayaran di sini --}}
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')
@section('title','Dashboard')

@section('content')
<h4 class="mb-4">Dashboard</h4>

<div class="row g-3">
    {{-- Penjualan Hari Ini --}}
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow">
            <div class="card-body">
                <small>Penjualan Hari Ini</small>
                <h4>Rp {{ number_format($todaySales) }}</h4>
                <small>{{ now()->format('d M Y') }}</small>
            </div>
        </div>
    </div>

    {{-- Transaksi Hari Ini --}}
    <div class="col-md-3">
        <div class="card text-white bg-success shadow">
            <div class="card-body">
                <small>Transaksi Hari Ini</small>
                <h4>{{ $todayTransactions }}</h4>
                <small>Transaksi</small>
            </div>
        </div>
    </div>

    {{-- Penjualan Bulan Ini --}}
    <div class="col-md-3">
        <div class="card text-white bg-info shadow">
            <div class="card-body">
                <small>Penjualan Bulan Ini</small>
                <h4>Rp {{ number_format($monthSales) }}</h4>
                <small>{{ now()->format('F Y') }}</small>
            </div>
        </div>
    </div>

    {{-- Stok Menipis --}}
    <div class="col-md-3">
        <div class="card text-white bg-warning shadow">
            <div class="card-body">
                <small>Stok Menipis</small>
                <h4>{{ $lowStock }}</h4>
                <small>Produk</small>
            </div>
        </div>
    </div>
</div>

{{-- Produk Terlaris --}}
<div class="card mt-4 shadow-sm">
    <div class="card-header">
        <strong>📦 Produk Terlaris Hari Ini</strong>
    </div>
    <div class="card-body">
        @forelse($bestProducts as $item)
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>{{ $item->unit->product->name }}</span>
                <span>{{ $item->total_qty }} pcs</span>
            </div>
        @empty
            <p class="text-muted text-center">Belum ada transaksi hari ini</p>
        @endforelse
    </div>
</div>

{{-- Stok Menipis Detail --}}
<div class="card mt-4 shadow-sm">
    <div class="card-header">
        <strong>⚠️ Produk Stok Menipis</strong>
    </div>
    <div class="card-body">
        @if($lowStockProducts->isEmpty())
            <p class="text-muted text-center">Semua stok aman</p>
        @else
            <ul class="list-group">
                @foreach($lowStockProducts as $unit)
                    @php
                        $stokToko = $unit->stock->where('location','toko')->first()->qty ?? 0;
                    @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $unit->product->name }} 
                        @if($unit->name) ({{ $unit->name }}) @endif
                        <span class="badge bg-danger">{{ $stokToko }} pcs</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- Aksi Cepat --}}
<div class="card mt-4 shadow-sm">
    <div class="card-header">
        <strong>⚡ Aksi Cepat</strong>
    </div>
    <div class="card-body d-flex gap-2 flex-wrap">
        <a href="{{ route('pos') }}" class="btn btn-primary">🛒 Buka POS</a>
        <a href="{{ route('transactions.index') }}" class="btn btn-info text-white">📄 Lihat Transaksi</a>
        <a href="{{ route('products.index') }}" class="btn btn-success">📦 Kelola Produk</a>
        <a href="/reports/sales" class="btn btn-warning">📊 Laporan</a>
    </div>
</div>
@endsection

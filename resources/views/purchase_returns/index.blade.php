@extends('layouts.app')

@section('title', 'Riwayat Retur Pembelian')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-clock-history"></i> Riwayat Retur Pembelian
            </h5>
            <a href="{{ route('po.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Buat Retur Baru
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tanggal</th>
                            <th>No. PO</th>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th>Alasan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr>
                            <td>{{ $return->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="fw-bold text-primary">
                                    {{ $return->purchase->po_number ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ $return->productUnit->product->name ?? '-' }}</td>
                            <td class="text-center fw-bold text-danger">
                                {{ number_format($return->qty, 0) }}
                            </td>
                            <td>{{ $return->reason }}</td>
                            <td>
                                <span class="badge bg-success">Stok Terpotong</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data retur.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
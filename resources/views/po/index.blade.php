@extends('layouts.app')

@section('title', 'Daftar Purchase Order')

@section('content')
<div class="container-fluid">
    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bi bi-cart-plus me-2"></i>Daftar Purchase Order
            </h5>
            <a href="{{ route('po.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Buat Transaksi Baru
            </a>
        </div>
        
        <div class="card-body bg-light border-bottom">
            {{-- FILTER FORM --}}
            <form method="GET" action="{{ route('po.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">No. PO</label>
                    <input type="text" name="cari" class="form-control form-control-sm" 
                           value="{{ request('cari') }}" placeholder="PO-...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Supplier</label>
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-secondary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('po.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>No. PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Pembayaran</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $index => $po)
                    <tr>
                        <td class="ps-3 text-muted">{{ ($pos->currentPage() - 1) * $pos->perPage() + $index + 1 }}</td>
                        <td class="fw-bold text-primary">{{ $po->po_number }}</td>
                        <td>{{ $po->tanggal ? \Carbon\Carbon::parse($po->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $po->jenis_pembayaran ?? '-' }}</span></td>
                        <td>
                            @if($po->tanggal_jatuh_tempo)
                                @php $jt = \Carbon\Carbon::parse($po->tanggal_jatuh_tempo); @endphp
                                <span class="{{ $jt->isPast() && $po->status !== 'received' ? 'text-danger fw-bold' : '' }}">
                                    {{ $jt->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($po->total, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @php
                                $badgeColor = match($po->status) {
                                    'approved' => 'bg-primary',
                                    'received' => 'bg-success',
                                    'canceled' => 'bg-danger',
                                    default    => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeColor }}">{{ ucfirst($po->status) }}</span>
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group">
                                {{-- Edit/Lihat --}}
                                <a href="{{ route('po.edit', $po->id) }}" class="btn btn-sm btn-outline-dark" title="Detail/Edit">
                                    <i class="bi bi-{{ $po->status === 'draft' ? 'pencil' : 'eye' }}"></i>
                                </a>

                                {{-- Approve --}}
                                @if($po->status === 'draft')
                                <form action="{{ route('po.approve', $po->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary" onclick="return confirm('Approve PO ini?')" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Receive --}}
                                @if($po->status === 'approved')
                                <form action="{{ route('po.receive', $po->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" onclick="return confirm('Terima barang ini?')" title="Terima Barang">
                                        <i class="bi bi-box-seam"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Delete --}}
                                <form action="{{ route('po.destroy', $po->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus permanen PO ini?')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                            Belum ada data Purchase Order
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $pos->firstItem() ?? 0 }} sampai {{ $pos->lastItem() ?? 0 }} dari {{ $pos->total() }} transaksi
                </div>
                <div>
                    {{ $pos->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title','Manajemen Stok')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Stok</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('stocks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Stok
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- FILTER --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small">Cari Produk</label>
                <input type="text" name="q" class="form-control" placeholder="Nama produk..." value="{{ request('q') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small">Gudang</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>

            <div class="col-md-2">
                <a href="{{ route('stocks.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- TABLE --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Produk</th>
                        <th>Unit</th>
                        <th>Gudang</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center" width="280">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $s)
                    <tr>
                        <td class="ps-3">
                            <span class="fw-bold text-dark d-block">{{ $s->unit->product->name ?? 'Produk Tidak Ditemukan' }}</span>
                            <small class="text-muted">ID: #{{ $s->id }}</small>
                        </td>
                        <td>{{ $s->unit->unit_name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-house-door"></i> {{ $s->warehouse->name ?? 'Tanpa Gudang' }}
                            </span>
                        </td>
                        <td class="text-center fw-bold fs-6">
                            {{ number_format($s->qty) }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                {{-- Tombol Mutasi --}}
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" data-bs-target="#modalTransfer{{ $s->id }}" title="Mutasi Barang">
                                    <i class="bi bi-arrow-left-right"></i> Mutasi
                                </button>

                                {{-- Tombol Opname --}}
                                <button type="button" class="btn btn-sm btn-outline-dark" 
                                        data-bs-toggle="modal" data-bs-target="#modalOpname{{ $s->id }}" title="Stok Opname">
                                    <i class="bi bi-clipboard-check"></i> Opname
                                </button>

                                {{-- Edit --}}
                                <a href="{{ route('stocks.edit', $s->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Hapus --}}
                                <form action="{{ route('stocks.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data stok ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- MODAL MUTASI (TRANSFER) --}}
                            <div class="modal fade" id="modalTransfer{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('stocks.transfer') }}" method="POST" class="modal-content text-start">
                                        @csrf
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Mutasi / Pindah Barang</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="product_unit_id" value="{{ $s->product_unit_id }}">
                                            <input type="hidden" name="from_warehouse" value="{{ $s->warehouse_id }}">

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Produk</label>
                                                <input type="text" class="form-control bg-light" value="{{ $s->unit->product->name ?? 'N/A' }}" readonly>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-bold">Dari Gudang</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $s->warehouse->name ?? 'Tanpa Gudang' }}" readonly>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-bold text-primary">Ke Gudang</label>
                                                    <select name="to_warehouse" class="form-select border-primary" required>
                                                        @foreach($warehouses as $w)
                                                            @if($w->id != $s->warehouse_id)
                                                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Jumlah (Maks: {{ $s->qty }})</label>
                                                <input type="number" name="qty" class="form-control" max="{{ $s->qty }}" min="1" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Proses Pindah</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- MODAL STOK OPNAME --}}
                            <div class="modal fade" id="modalOpname{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('stocks.opname') }}" method="POST" class="modal-content text-start">
                                        @csrf
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title">Stok Opname (Penyesuaian)</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="stock_id" value="{{ $s->id }}">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nama Produk</label>
                                                <p class="mb-0">{{ $s->unit->product->name ?? 'N/A' }} ({{ $s->warehouse->name ?? 'Gudang Tidak Ditemukan' }})</p>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-bold">Stok Sistem</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $s->qty }}" readonly>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-bold text-danger">Stok Fisik (Asli)</label>
                                                    <input type="number" name="physical_qty" class="form-control border-danger" required min="0">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Catatan / Alasan</label>
                                                <textarea name="note" class="form-control" rows="2" placeholder="Contoh: Barang rusak, hilang, atau salah hitung"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-dark">Simpan Penyesuaian</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada data stok yang ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- PAGINATION --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $stocks->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>

@endsection
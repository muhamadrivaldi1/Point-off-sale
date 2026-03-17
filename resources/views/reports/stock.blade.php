@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header Halaman --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold text-primary">History Mutasi Stok</h4>
            <p class="text-muted small mb-0">Lacak setiap detail perubahan stok barang masuk dan keluar.</p>
        </div>
        <div class="text-end">
            {{-- Tombol Export (Pastikan Route sudah ada) --}}
            <a href="{{ route('reports.stock.csv', request()->all()) }}" class="btn btn-success btn-sm shadow-sm">
                <i class="fas fa-file-excel me-1"></i> Export CSV
            </a>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            {{-- Form Filter --}}
            <form action="{{ route('reports.stock') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from', date('Y-m-01')) }}">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to', date('Y-m-d')) }}">
                </div>

                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Produk / SKU</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/SKU..." value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Supplier</label>
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">-- Semua Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Status Mutasi</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="pembelian" {{ request('status') == 'pembelian' ? 'selected' : '' }}>Pembelian</option>
                        <option value="penjualan" {{ request('status') == 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                        <option value="opname" {{ request('status') == 'opname' ? 'selected' : '' }}>Stok Opname</option>
                        <option value="retur_pembelian" {{ request('status') == 'retur_pembelian' ? 'selected' : '' }}>Retur Pembelian</option>
                        <option value="retur_penjualan" {{ request('status') == 'retur_penjualan' ? 'selected' : '' }}>Retur Penjualan</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">
                        <i class="fas fa-search me-1"></i> Cari
                    </button>
                    
                    <a href="{{ route('reports.stock') }}" class="btn btn-outline-danger btn-sm px-2 shadow-sm" title="Reset Filter">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-3 py-3 text-secondary small" style="width: 130px;">WAKTU</th>
                            <th class="text-secondary small">PRODUK & SUPPLIER</th>
                            <th class="text-center text-secondary small">STATUS</th>
                            <th class="text-end text-secondary small">MASUK/KELUAR</th>
                            <th class="text-end text-secondary small">SALDO STOK</th>
                            <th class="ps-3 text-secondary small">NO. REFERENSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ $row->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $row->created_at->format('H:i') }} WIB</small>
                                </td>

                                <td>
                                    <div class="fw-bold text-primary">{{ $row->unit->product->name ?? 'Produk Terhapus' }}</div>
                                    <div class="small text-muted">
                                        <i class="fas fa-truck me-1"></i> {{ $row->unit->product->supplier->nama_supplier ?? 'Tanpa Supplier' }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    @php
                                        $statusRaw = $row->status ?? ($row->type == 'in' ? 'masuk' : 'keluar');
                                        $badges = [
                                            'pembelian' => 'bg-info text-dark',
                                            'penjualan' => 'bg-success',
                                            'mutasi'    => 'bg-warning text-dark',
                                            'opname'    => 'bg-secondary',
                                            'retur_pembelian' => 'bg-danger',
                                            'retur_penjualan' => 'bg-primary',
                                        ];
                                        $class = $badges[$statusRaw] ?? ($row->type == 'in' ? 'bg-success' : 'bg-danger');
                                        $label = str_replace('_', ' ', $statusRaw);
                                    @endphp
                                    <span class="badge {{ $class }} text-uppercase" style="font-size: 0.65rem;">
                                        {{ $label == 'penjualan' ? 'TERJUAL' : $label }}
                                    </span>
                                </td>

                                <td class="text-end fw-bold {{ $row->type == 'in' ? 'text-success' : 'text-danger' }}">
                                    @if($row->type == 'in')
                                        <span class="me-1">+{{ number_format($row->qty) }}</span><i class="fas fa-arrow-down small"></i>
                                    @else
                                        <span class="me-1">-{{ number_format($row->qty) }}</span><i class="fas fa-arrow-up small"></i>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <span class="fw-bold text-dark" style="font-family: 'Monaco', 'Consolas', monospace;">
                                        {{ number_format($row->stock_after) }}
                                    </span>
                                </td>

                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border fw-normal mb-1">{{ $row->reference ?? '-' }}</span>
                                    <div class="small text-muted" style="font-size: 0.7rem;">
                                        <i class="fas fa-info-circle me-1"></i> {{ $row->description }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>
                                    Data mutasi tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="small text-muted">
            Menampilkan {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }} dari {{ $data->total() }} mutasi
        </div>
        <div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<style>
    .table th { font-weight: 700; letter-spacing: 0.5px; }
    .badge { padding: 0.4em 0.7em; border-radius: 4px; }
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,.02); }
</style>
@endsection
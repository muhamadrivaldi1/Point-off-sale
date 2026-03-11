@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold">History Mutasi Stok Detail</h3>
            <p class="text-muted small mb-0">Lacak setiap perubahan stok barang masuk dan keluar.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary px-3 py-2 shadow-sm">
                <i class="bi bi-calendar3"></i> Periode: {{ \Carbon\Carbon::parse(request('from', date('Y-m-01')))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('to', date('Y-m-d')))->format('d/m/Y') }}
            </span>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            {{-- Form Filter --}}
            <form action="{{ route('reports.stock') }}" method="GET" class="row g-3">
                {{-- Filter Tanggal --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from', date('Y-m-01')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to', date('Y-m-d')) }}">
                </div>

                {{-- Filter Nama Barang --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Nama Barang / SKU</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filter Supplier --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Supplier</label>
                    <select name="supplier_id" class="form-select form-control-sm">
                        <option value="">-- Semua Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Keterangan Status</label>
                    <select name="status" class="form-select form-control-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="pembelian" {{ request('status') == 'pembelian' ? 'selected' : '' }}>Pembelian</option>
                        <option value="penjualan" {{ request('status') == 'penjualan' ? 'selected' : '' }}>Penjualan (Terjual)</option>
                        <option value="mutasi" {{ request('status') == 'mutasi' ? 'selected' : '' }}>Mutasi Barang</option>
                        <option value="opname" {{ request('status') == 'opname' ? 'selected' : '' }}>Stok Opname</option>
                        <option value="retur_pembelian" {{ request('status') == 'retur_pembelian' ? 'selected' : '' }}>Retur Pembelian</option>
                        <option value="retur_penjualan" {{ request('status') == 'retur_penjualan' ? 'selected' : '' }}>Retur Penjualan</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('reports.stock') }}" class="btn btn-light btn-sm border px-3">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                        <i class="bi bi-funnel"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-3 py-3" style="width: 150px;">WAKTU</th>
                            <th>PRODUK & SUPPLIER</th>
                            <th class="text-center">STATUS</th>
                            <th class="text-end">MASUK/KELUAR</th>
                            <th class="text-end">SALDO STOK</th>
                            <th class="ps-3">NO. REFERENSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td class="ps-3">
                                    <span class="d-block fw-bold text-dark">{{ $row->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted">{{ $row->created_at->format('H:i') }} WIB</small>
                                </td>

                                <td>
                                    <div class="fw-bold text-primary">{{ $row->unit->product->name ?? 'Produk Terhapus' }}</div>
                                    <div class="small text-muted">
                                        {{-- Perbaikan: Memastikan memanggil nama_supplier sesuai DB --}}
                                        <i class="bi bi-truck me-1"></i> {{ $row->unit->product->supplier->nama_supplier ?? 'N/A' }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    @php
                                        $statusRaw = $row->status ?? ($row->type == 'in' ? 'masuk' : 'keluar');
                                        $statusClass = [
                                            'pembelian' => 'bg-info text-dark',
                                            'penjualan' => 'bg-success',
                                            'mutasi'    => 'bg-warning text-dark',
                                            'opname'    => 'bg-secondary',
                                            'retur_pembelian' => 'bg-danger',
                                            'retur_penjualan' => 'bg-primary',
                                        ][$statusRaw] ?? ($row->type == 'in' ? 'bg-success' : 'bg-danger');

                                        $statusText = [
                                            'penjualan' => 'Terjual',
                                            'pembelian' => 'Pembelian',
                                            'opname'    => 'Opname',
                                        ][$statusRaw] ?? str_replace('_', ' ', $statusRaw);
                                    @endphp

                                    <span class="badge {{ $statusClass }} text-uppercase shadow-xs" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                        {{ $statusText }}
                                    </span>
                                </td>

                                <td class="text-end fw-bold {{ $row->type == 'in' ? 'text-success' : 'text-danger' }}">
                                    @if($row->type == 'in')
                                        <span class="me-1">+</span>{{ number_format($row->qty) }}
                                        <i class="bi bi-arrow-down-left small"></i>
                                    @else
                                        <span class="me-1">-</span>{{ number_format($row->qty) }}
                                        <i class="bi bi-arrow-up-right small"></i>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <span class="fw-bold text-dark" style="font-family: 'Courier New', Courier, monospace;">
                                        {{ number_format($row->stock_after) }}
                                    </span>
                                </td>

                                <td class="ps-3">
                                    <code class="d-block text-dark fw-bold mb-1">{{ $row->reference ?? '-' }}</code>
                                    <div class="small text-muted" style="font-style: italic; font-size: 0.75rem;">
                                        <i class="bi bi-info-circle me-1"></i> {{ $row->description }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-box-seam text-light-emphasis display-1 d-block mb-3"></i>
                                        <h5 class="text-muted">Data mutasi tidak ditemukan</h5>
                                        <p class="small text-secondary">Coba ubah filter atau kata kunci pencarian Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
            Menampilkan {{ $data->firstItem() }} sampai {{ $data->lastItem() }} dari {{ $data->total() }} mutasi
        </div>
        <div>
            {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<style>
    .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }
    .table th { font-size: 0.75rem; letter-spacing: 1px; }
    .badge { font-weight: 600; padding: 0.4em 0.8em; }
</style>
@endsection
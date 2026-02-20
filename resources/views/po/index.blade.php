@extends('layouts.app')

@section('title', 'Daftar Purchase Order')

@push('styles')
<style>
    .po-index-wrapper { font-size: 0.83rem; }

    .po-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 14px;
        margin-bottom: 10px;
    }
    .po-toolbar .title { font-weight: 700; font-size: 0.9rem; color: #212529; }
    .po-toolbar .btn   { font-size: 0.78rem; padding: 4px 14px; border-radius: 2px; }

    .filter-bar {
        background: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 14px;
        margin-bottom: 10px;
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }
    .filter-bar label { font-size: 0.75rem; color: #6c757d; margin-bottom: 2px; display: block; }
    .filter-bar .form-control,
    .filter-bar .form-select { font-size: 0.78rem; padding: 3px 8px; height: 28px; border-radius: 2px; border-color: #adb5bd; }
    .filter-bar .btn { font-size: 0.78rem; padding: 3px 14px; border-radius: 2px; height: 28px; }

    .po-table-wrap {
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        overflow: hidden;
    }
    .po-table-wrap table { font-size: 0.8rem; margin: 0; }
    .po-table-wrap thead th {
        background: #343a40;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 7px 10px;
        border: none;
        white-space: nowrap;
    }
    .po-table-wrap tbody td { padding: 6px 10px; vertical-align: middle; border-color: #f1f3f5; }
    .po-table-wrap tbody tr:hover { background: #e8f4fd; cursor: pointer; }

    .badge-draft    { background: #6c757d; color: #fff; }
    .badge-approved { background: #0d6efd; color: #fff; }
    .badge-received { background: #198754; color: #fff; }
    .badge-canceled { background: #dc3545; color: #fff; }
    .status-badge   { font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; font-weight: 500; }

    .btn-aksi { font-size: 0.72rem; padding: 2px 8px; border-radius: 2px; line-height: 1.4; }

    .po-table-footer {
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 6px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')

<div class="po-index-wrapper">

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-1 mb-2" style="font-size:0.82rem">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-1 mb-2" style="font-size:0.82rem">
        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- TOOLBAR --}}
    <div class="po-toolbar">
        <span class="title"><i class="bi bi-cart-plus me-2 text-primary"></i>Daftar Purchase Order</span>
        <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Buat PO Baru
        </a>
    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('po.index') }}" class="filter-bar">
        <div>
            <label>Cari No. PO</label>
            <input type="text" name="cari" class="form-control" style="width:150px"
                   value="{{ request('cari') }}" placeholder="PO-...">
        </div>
        <div>
            <label>Supplier</label>
            <select name="supplier_id" class="form-select" style="width:160px">
                <option value="">-- Semua --</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->nama_supplier }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Status</label>
            <select name="status" class="form-select" style="width:120px">
                <option value="">-- Semua --</option>
                <option value="draft"    {{ request('status') == 'draft'    ? 'selected' : '' }}>Draft</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </div>
        <div>
            <label>Dari Tanggal</label>
            <input type="date" name="dari" class="form-control" style="width:130px" value="{{ request('dari') }}">
        </div>
        <div>
            <label>Sampai Tanggal</label>
            <input type="date" name="sampai" class="form-control" style="width:130px" value="{{ request('sampai') }}">
        </div>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-secondary">
                <i class="bi bi-search"></i> Cari
            </button>
            <a href="{{ route('po.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>

    {{-- TABEL --}}
    <div class="po-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="30">#</th>
                    <th>No. PO</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>No. Faktur</th>
                    <th>Pembayaran</th>
                    <th>Jatuh Tempo</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pos as $index => $po)
                <tr>
                    <td class="text-muted">{{ ($pos->currentPage() - 1) * $pos->perPage() + $index + 1 }}</td>

                    <td class="fw-semibold text-primary">{{ $po->po_number }}</td>

                    <td>{{ $po->tanggal ? \Carbon\Carbon::parse($po->tanggal)->format('d/m/Y') : '-' }}</td>

                    <td>
                        @if($po->supplier)
                            {{ $po->supplier->nama_supplier }}
                        @else
                            <span class="text-muted fst-italic">-</span>
                        @endif
                    </td>

                    <td class="text-muted">{{ $po->nomor_faktur ?? '-' }}</td>
                    <td>{{ $po->jenis_pembayaran ?? '-' }}</td>

                    <td>
                        @if($po->tanggal_jatuh_tempo)
                            @php $jt = \Carbon\Carbon::parse($po->tanggal_jatuh_tempo); @endphp
                            <span class="{{ $jt->startOfDay()->isPast() && !$jt->isToday() && $po->status !== 'received' ? 'text-danger fw-semibold' : '' }}">
                                {{ $jt->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-end fw-semibold">
                        {{ $po->total ? 'Rp ' . number_format($po->total, 0, ',', '.') : '-' }}
                    </td>

                    <td class="text-center">
                        @php
                            $badgeClass = match($po->status) {
                                'approved' => 'badge-approved',
                                'received' => 'badge-received',
                                'canceled' => 'badge-canceled',
                                default    => 'badge-draft',
                            };
                        @endphp
                        <span class="status-badge {{ $badgeClass }}">{{ ucfirst($po->status) }}</span>
                    </td>

                    <td class="text-center" onclick="event.stopPropagation()">
                        <div class="d-flex gap-1 justify-content-center">

                            {{-- Lihat / Edit --}}
                            <a href="{{ route('po.edit', $po->id) }}"
                               class="btn btn-sm btn-outline-secondary btn-aksi"
                               title="{{ $po->status === 'draft' ? 'Edit' : 'Lihat Detail' }}">
                                <i class="bi bi-{{ $po->status === 'draft' ? 'pencil' : 'eye' }}"></i>
                            </a>

                            {{-- Approve (hanya draft) --}}
                            @if($po->status === 'draft')
                            <form method="POST" action="{{ route('po.approve', $po->id) }}"
                                  onsubmit="return confirm('Approve PO {{ $po->po_number }}?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary btn-aksi" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            @endif

                            {{-- Terima Barang (hanya approved) --}}
                            @if($po->status === 'approved')
                            <form method="POST" action="{{ route('po.receive', $po->id) }}"
                                  onsubmit="return confirm('Tandai barang sudah diterima?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-success btn-aksi" title="Terima Barang">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </button>
                            </form>
                            @endif

                            {{-- Batalkan (hanya draft) --}}
                            @if($po->status === 'draft')
                            <form method="POST" action="{{ route('po.cancel', $po->id) }}"
                                  onsubmit="return confirm('Batalkan PO ini?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning btn-aksi" title="Batalkan">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            </form>
                            @endif

                            {{-- HAPUS — tampil untuk SEMUA status --}}
                            <form method="POST" action="{{ route('po.destroy', $po->id) }}"
                                  onsubmit="return confirm('Hapus permanen PO {{ $po->po_number }}?\nData yang sudah dihapus tidak bisa dikembalikan.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-aksi" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                        Belum ada data Purchase Order
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="po-table-footer">
            <span>Total: <strong>{{ $pos->total() }}</strong> transaksi</span>
            <div>{{ $pos->withQueryString()->links() }}</div>
        </div>
    </div>

</div>

@endsection
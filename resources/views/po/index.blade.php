@extends('layouts.app')

@section('title', 'Daftar Purchase Order')

@section('content')
<div class="container-fluid">

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3 d-flex justify-content-between">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bi bi-cart-plus me-2"></i>Daftar Purchase Order
            </h5>

            <a href="{{ route('po.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Buat Transaksi Baru
            </a>
        </div>


        {{-- FILTER --}}
        <div class="card-body bg-light border-bottom">

            <form method="GET" action="{{ route('po.index') }}" class="row g-3">

                <div class="col-md-2">
                    <label class="form-label small fw-bold">No. PO</label>
                    <input type="text" name="cari" class="form-control form-control-sm"
                        value="{{ request('cari') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Supplier</label>

                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>

                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}"
                            {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->nama_supplier }}
                        </option>
                        @endforeach

                    </select>
                </div>


                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>

                    <select name="status" class="form-select form-select-sm">

                        <option value="">-- Semua --</option>

                        <option value="draft"
                            {{ request('status') == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>

                        <option value="approved"
                            {{ request('status') == 'approved' ? 'selected' : '' }}>
                            Approved
                        </option>

                        <option value="received"
                            {{ request('status') == 'received' ? 'selected' : '' }}>
                            Received
                        </option>

                        <option value="canceled"
                            {{ request('status') == 'canceled' ? 'selected' : '' }}>
                            Canceled
                        </option>

                    </select>
                </div>


                <div class="col-md-2">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" name="dari"
                        class="form-control form-control-sm"
                        value="{{ request('dari') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" name="sampai"
                        class="form-control form-control-sm"
                        value="{{ request('sampai') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-1">

                    <button type="submit"
                        class="btn btn-sm btn-secondary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>

                    <a href="{{ route('po.index') }}"
                        class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>

                </div>

            </form>

        </div>


        {{-- TABLE --}}
        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>No PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Pembayaran</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($pos as $index => $po)

                    <tr>

                        <td>
                            {{ ($pos->currentPage() - 1) * $pos->perPage() + $index + 1 }}
                        </td>

                        <td class="fw-bold text-primary">
                            {{ $po->po_number }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($po->created_at)->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ $po->supplier->nama_supplier ?? '-' }}
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $po->jenis_pembayaran }}
                            </span>
                        </td>

                        <td>

                            @if($po->tanggal_jatuh_tempo)

                            {{ \Carbon\Carbon::parse($po->tanggal_jatuh_tempo)->format('d/m/Y') }}

                            @else

                            -

                            @endif

                        </td>

                        <td class="text-end fw-bold">

                            Rp {{ number_format($po->total,0,',','.') }}

                        </td>


                        <td class="text-center">

                            @php

                            $badgeColor = match($po->status) {
                            'approved' => 'bg-primary',
                            'received' => 'bg-success',
                            'canceled' => 'bg-danger',
                            default => 'bg-secondary',
                            };

                            @endphp

                            <span class="badge {{ $badgeColor }}">
                                {{ ucfirst($po->status) }}
                            </span>

                        </td>


                        <td class="text-center">

                            <div class="btn-group">


                                {{-- Edit --}}
                                <a href="{{ route('po.edit',$po->id) }}"
                                    class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-eye"></i>
                                </a>


                                {{-- Approve --}}
                                @if($po->status=='draft')

                                <form action="{{ route('po.approve',$po->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>

                                @endif


                                {{-- Receive --}}
                                @if($po->status=='approved')

                                <form action="{{ route('po.receive',$po->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-box-seam"></i>
                                    </button>
                                </form>

                                @endif


                                {{-- Retur Pembelian --}}
                                @if($po->status=='received')

                               {{-- Ganti purchase-return menjadi purchase_returns --}}
                                <a href="{{ route('purchase_returns.create', $po->id) }}" 
                                class="btn btn-sm btn-warning" 
                                title="Retur Pembelian">
                                <i class="bi bi-arrow-return-left"></i>
                                </a>

                                @endif


                                {{-- Delete --}}
                                <form action="{{ route('po.destroy',$po->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </form>

                            </div>

                        </td>


                    </tr>

                    @empty

                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            Belum ada data Purchase Order
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="card-footer">

            <div class="d-flex justify-content-between">

                <div class="small text-muted">

                    Menampilkan
                    {{ $pos->firstItem() ?? 0 }}
                    -
                    {{ $pos->lastItem() ?? 0 }}
                    dari
                    {{ $pos->total() }}

                </div>

                {{ $pos->withQueryString()->links() }}

            </div>

        </div>

    </div>

</div>
@endsection
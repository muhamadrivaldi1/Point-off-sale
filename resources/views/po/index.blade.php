@extends('layouts.app')
@section('title','Daftar Purchase Order')

@section('content')
<h3 class="mb-4">Purchase Order</h3>

<!-- Tombol Buat PO -->
<div class="mb-3 d-flex justify-content-start">
    <a href="{{ route('po.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Buat Draft PO
    </a>
</div>

<!-- Alert -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- FILTER & SEARCH --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="text" name="q" class="form-control" placeholder="Cari Nomor PO atau Supplier..."
               value="{{ request('q') }}">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">-- Pilih Status --</option>
            <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
            <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
            <option value="received" {{ request('status')=='received'?'selected':'' }}>Received</option>
            <option value="canceled" {{ request('status')=='canceled'?'selected':'' }}>Canceled</option>
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Dari">
    </div>
    <div class="col-md-2">
        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="Sampai">
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
        <a href="{{ route('po.index') }}" class="btn btn-secondary w-100">Reset</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th class="text-start">Nomor PO</th>
                        <th>Status</th>
                        <th class="text-start">Tanggal</th>
                        <th style="width:220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $i => $po)
                    <tr>
                        <td>{{ $pos->firstItem() + $i }}</td>
                        <td class="text-start">{{ $po->po_number }}</td>
                        <td>
                            @if($po->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($po->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($po->status === 'received')
                                <span class="badge bg-info text-dark">Received</span>
                            @elseif($po->status === 'canceled')
                                <span class="badge bg-danger">Canceled</span>
                            @endif
                        </td>
                        <td class="text-start">{{ $po->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <!-- Edit -->
                                <a href="{{ route('po.edit', $po->id) }}"
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <!-- Hapus hanya jika draft -->
                                @if($po->status === 'draft')
                                <form action="{{ route('po.destroy', $po->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus PO ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Belum ada Purchase Order
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination Bootstrap 5 --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $pos->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection

@extends('layouts.app')
@section('title','Edit Purchase Order')

@section('content')
<h3 class="mb-4">Purchase Order</h3>

{{-- ALERT --}}
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

{{-- INFO PO --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-6">
                <strong>Nomor PO:</strong> {{ $po->po_number }}
            </div>
            <div class="col-md-6">
                <strong>Status:</strong>
                @if($po->status === 'draft')
                    <span class="badge bg-secondary">DRAFT</span>
                @elseif($po->status === 'approved')
                    <span class="badge bg-success">APPROVED</span>
                @elseif($po->status === 'received')
                    <span class="badge bg-info text-dark">RECEIVED</span>
                @elseif($po->status === 'canceled')
                    <span class="badge bg-danger">CANCELED</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- FORM TAMBAH ITEM (Hanya Draft) --}}
@if($po->status === 'draft')
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h5 class="mb-3">Tambah Item PO</h5>
        <form action="{{ route('po.addItem', $po->id) }}" method="POST">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Produk</label>
                    <select name="product_unit_id" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->product->name }} ({{ $unit->unit_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input type="number" name="qty" class="form-control" min="1" value="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="price" class="form-control" min="0" required>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- LIST ITEM --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h5 class="mb-3">Daftar Item PO</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th width="80">Qty</th>
                        <th width="150">Harga</th>
                        <th width="150">Subtotal</th>
                        @if($po->status === 'draft')
                            <th width="100">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @forelse($po->items as $item)
                        @php
                            $subtotal = $item->qty * $item->price;
                            $total += $subtotal;
                        @endphp
                        <tr>
                            <td class="text-start">{{ $item->unit->product->name }} ({{ $item->unit->unit_name }})</td>
                            <td>{{ $item->qty }}</td>
                            <td class="text-end">Rp {{ number_format($item->price) }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal) }}</td>

                            {{-- Tombol hapus hanya draft --}}
                            @if($po->status === 'draft')
                            <td>
                                <form action="{{ route('po.deleteItem', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus item ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger w-100">Hapus</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $po->status === 'draft' ? 5 : 4 }}" class="text-center text-muted">
                                Belum ada item
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th colspan="{{ $po->status === 'draft' ? 2 : 1 }}" class="text-end">
                            Rp {{ number_format($total) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- TOMBOL DRAFT: CANCEL & APPROVE --}}
@if($po->status === 'draft' && $po->items->count() > 0)
<div class="d-flex justify-content-end gap-2 mb-3">
    {{-- Cancel --}}
    <form action="{{ route('po.cancel', $po->id) }}" method="POST" onsubmit="return confirm('Batalkan PO ini?')">
        @csrf
        <button class="btn btn-danger">
            <i class="bi bi-x-circle"></i> Cancel PO
        </button>
    </form>

    {{-- Approve --}}
    <form action="{{ route('po.approve', $po->id) }}" method="POST" onsubmit="return confirm('Approve PO ini?')">
        @csrf
        <button class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Approve PO
        </button>
    </form>
</div>
@endif

{{-- TOMBOL APPROVED: RECEIVE --}}
@if($po->status === 'approved')
<div class="d-flex justify-content-end gap-2 mb-3">
    <form action="{{ route('po.receive', $po->id) }}" method="POST" onsubmit="return confirm('Tandai PO ini sebagai diterima?')">
        @csrf
        <button class="btn btn-info">
            <i class="bi bi-box-seam"></i> Terima PO
        </button>
    </form>
</div>
@endif

{{-- BACK --}}
<div class="mt-3">
    <a href="{{ route('po.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar PO
    </a>
</div>
@endsection

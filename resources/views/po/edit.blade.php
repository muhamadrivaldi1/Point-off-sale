@extends('layouts.app')
@section('title','Edit Purchase Order')

@section('content')
<h3 class="mb-3">Purchase Order</h3>

{{-- ALERT --}}
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- INFO PO --}}
<div class="card mb-3">
    <div class="card-body">
        <strong>Nomor PO:</strong> {{ $po->po_number }} <br>
        <strong>Status:</strong>

        @if($po->status === 'draft')
            <span class="badge bg-secondary">DRAFT</span>
        @elseif($po->status === 'approved')
            <span class="badge bg-success">APPROVED</span>
        @elseif($po->status === 'received')
            <span class="badge bg-info text-dark">RECEIVED</span>
        @endif
    </div>
</div>

{{-- FORM TAMBAH ITEM --}}
@if($po->status === 'draft')
<div class="card mb-3">
    <div class="card-body">
        <h5>Tambah Item PO</h5>

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

                <div class="col-md-2">
                    <button class="btn btn-success w-100">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- LIST ITEM --}}
<div class="card mb-3">
    <div class="card-body">
        <h5>Daftar Item PO</h5>

        <table class="table table-bordered">
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
                        <td>
                            {{ $item->unit->product->name }}
                            ({{ $item->unit->unit_name }})
                        </td>
                        <td>{{ $item->qty }}</td>
                        <td>Rp {{ number_format($item->price) }}</td>
                        <td>Rp {{ number_format($subtotal) }}</td>

                        @if($po->status === 'draft')
                        <td>
                            <form action="{{ route('po.deleteItem', $item->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus item ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Belum ada item
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th colspan="2">
                        Rp {{ number_format($total) }}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- TOMBOL APPROVE --}}
@if($po->status === 'draft' && $po->items->count() > 0)
<div class="text-end">
    <form action="{{ route('po.approve', $po->id) }}"
          method="POST"
          onsubmit="return confirm('Approve PO ini?')">
        @csrf
        <button class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Approve PO
        </button>
    </form>
</div>
@endif

{{-- BACK --}}
<div class="mt-3">
    <a href="{{ route('po.index') }}" class="btn btn-secondary">
        Kembali ke Daftar PO
    </a>
</div>

@endsection

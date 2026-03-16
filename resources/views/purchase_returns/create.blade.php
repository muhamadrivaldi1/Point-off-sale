@extends('layouts.app')

@section('title', 'Form Retur Pembelian')

@section('content')
<div class="container-fluid">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold text-warning">
                <i class="bi bi-arrow-return-left"></i> Form Retur Pembelian
            </h5>
        </div>

        <div class="card-body">
            <div class="row mb-4 pb-3 border-bottom">
                <div class="col-md-4">
                    <label class="text-muted d-block">No PO</label>
                    <span class="fw-bold">{{ $po->po_number }}</span>
                </div>
                <div class="col-md-4">
                    <label class="text-muted d-block">Supplier</label>
                    <span class="fw-bold">{{ $po->supplier->nama_supplier ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <label class="text-muted d-block">Tanggal Pembelian</label>
                    <span class="fw-bold">{{ \Carbon\Carbon::parse($po->created_at)->format('d/m/Y') }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('purchase_returns.store') }}">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $po->id }}">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th width="30%">Produk</th>
                                <th>Total Dibeli</th>
                                <th>Sudah Diretur</th>
                                <th>Sisa Bisa Retur</th>
                                <th width="150">Qty Retur</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Kelompokkan items berdasarkan product_unit_id agar tidak muncul double di form
                                $groupedItems = $po->items->groupBy('product_unit_id');
                            @endphp

                            @foreach($groupedItems as $unitId => $items)
                                @php
                                    $item = $items->first(); // Ambil data produk
                                    $totalDibeli = $items->sum('qty'); // Total qty produk ini di PO (jika ada baris ganda)
                                    
                                    // Hitung total yang sudah disetujui retur sebelumnya
                                    $totalRetur = \App\Models\PurchaseReturn::where('purchase_id', $po->id)
                                        ->where('product_unit_id', $unitId)
                                        ->where('status', 'approved')
                                        ->sum('qty');
                                    
                                    $sisaRetur = $totalDibeli - $totalRetur;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $item->unit->product->name ?? '-' }}</span>
                                        <input type="hidden" name="items[{{ $loop->index }}][product_unit_id]" value="{{ $unitId }}">
                                    </td>
                                    <td class="text-center">{{ number_format($totalDibeli, 0) }}</td>
                                    <td class="text-center text-danger">{{ number_format($totalRetur, 0) }}</td>
                                    <td class="text-center text-success fw-bold">{{ number_format($sisaRetur, 0) }}</td>
                                    <td>
                                        @if($sisaRetur > 0)
                                            <input type="number" 
                                                   name="items[{{ $loop->index }}][qty]" 
                                                   class="form-control" 
                                                   min="0" 
                                                   max="{{ $sisaRetur }}" 
                                                   value="0">
                                        @else
                                            <span class="badge bg-secondary d-block p-2">Selesai</span>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="items[{{ $loop->index }}][reason]" 
                                               class="form-control" 
                                               placeholder="Contoh: Barang Rusak">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('po.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" 
                            class="btn btn-warning fw-bold shadow-sm" 
                            onclick="return confirm('Apakah Anda yakin? Stok akan langsung dipotong otomatis.')">
                        <i class="bi bi-send-check"></i> Ajukan & Potong Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
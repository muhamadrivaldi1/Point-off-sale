@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">
                Detail Hutang PO: {{ $po->po_number }}
            </h5>
            <span class="badge bg-warning text-dark">
                Status: {{ ucfirst($po->status) }}
            </span>
        </div>

        <div class="card-body">

            <div class="mb-3">
                <strong>Supplier:</strong> {{ optional($po->supplier)->name ?? '-' }} <br>
                <strong>Tanggal PO:</strong> {{ $po->tanggal ? $po->tanggal->format('d/m/Y') : '-' }} <br>
                <strong>Total PO:</strong> Rp {{ number_format($po->total,0,',','.') }}
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Barcode</th>
                            <th>Satuan</th>
                            <th>Qty</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($po->items as $item)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $item->unit->product->name ?? '-' }}</td>
                            <td>{{ $item->unit->barcode ?? '-' }}</td>
                            <td>{{ $item->unit->unit_name ?? '-' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>Rp {{ number_format($item->price,0,',','.') }}</td>
                            <td>Rp {{ number_format($item->qty * $item->price,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">TOTAL</th>
                            <th>Rp {{ number_format($po->items->sum(fn($i) => $i->qty * $i->price),0,',','.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <a href="{{ route('reports.hutang') }}" class="btn btn-secondary mt-3">Kembali ke Laporan Hutang</a>
        </div>

    </div>

</div>
@endsection
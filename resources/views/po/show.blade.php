@extends('layouts.app')

@section('content')
<div class="container-fluid">

<div class="card shadow-sm border-0">

<div class="card-header bg-white">
<h5 class="mb-0 text-primary">
Detail Purchase Order
</h5>
</div>

<div class="card-body">

<div class="row mb-4">

<div class="col-md-4">
<strong>No PO</strong><br>
<span class="badge bg-light text-dark border">
{{ $po->po_number }}
</span>
</div>

<div class="col-md-4">
<strong>Supplier</strong><br>
{{ optional($po->supplier)->nama_supplier ?? '-' }}
</div>
<div class="col-md-4">
<strong>Status</strong><br>

@if($po->status == 'received')
<span class="badge bg-success">Barang Diterima</span>

@elseif($po->status == 'approved')
<span class="badge bg-info">Approved</span>

@elseif($po->status == 'draft')
<span class="badge bg-secondary">Draft</span>

@else
<span class="badge bg-danger">Canceled</span>
@endif

</div>

</div>


<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-light">
<tr>
<th width="50">No</th>
<th>Produk</th>
<th class="text-center">Qty</th>
<th class="text-end">Harga</th>
<th class="text-end">Subtotal</th>
</tr>
</thead>

<tbody>

@php
$total = 0;
@endphp

@forelse($po->items as $item)

@php
$subtotal = $item->qty * ($item->price ?? 0);
$total += $subtotal;
@endphp

<tr>
<td>{{ $loop->iteration }}</td>

<td>
{{ $item->unit->product->name ?? '-' }}
</td>

<td class="text-center">
{{ $item->qty }}
</td>

<td class="text-end">
Rp {{ number_format($item->price ?? 0,0,',','.') }}
</td>

<td class="text-end">
Rp {{ number_format($subtotal,0,',','.') }}
</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center text-muted py-4">
Tidak ada item barang
</td>
</tr>

@endforelse

</tbody>

<tfoot>

<tr>
<th colspan="4" class="text-end">
Total
</th>

<th class="text-end text-primary">
Rp {{ number_format($total,0,',','.') }}
</th>

</tr>

</tfoot>

</table>

</div>


<div class="mt-3">

<a href="{{ route('po.index') }}" class="btn btn-secondary">
Kembali
</a>

</div>

</div>
</div>

</div>
@endsection
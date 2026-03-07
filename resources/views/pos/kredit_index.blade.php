@extends('layouts.app')

@section('content')

<div class="container">

<h3 class="mb-4">Daftar Kredit</h3>

<table class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
<th>No</th>
<th>No Transaksi</th>
<th>Member</th>
<th>Total</th>
<th>Terbayar</th>
<th>Sisa</th>
<th>Bayar Terakhir</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>

@forelse($kredits as $k)

@php
$paid = $k->payments->sum('amount');
$sisa = $k->total - $paid;
$lastPayment = $k->payments->sortByDesc('paid_at')->first();
@endphp

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $k->trx_number }}</td>

<td>{{ $k->member->name ?? '-' }}</td>

<td>Rp {{ number_format($k->total) }}</td>

<td class="text-success">
Rp {{ number_format($paid) }}
</td>

<td class="text-danger">
Rp {{ number_format($sisa) }}
</td>

<td>

@if($lastPayment)

{{ \Carbon\Carbon::parse($lastPayment->paid_at)->locale('id')->translatedFormat('l, d M Y H:i') }}

@else

<span class="text-muted">Belum ada</span>

@endif

</td>

<td>

<a href="{{ route('pos.kredit.show',$k->id) }}" 
class="btn btn-sm btn-primary">

<i class="bi bi-eye"></i> Detail

</a>

@if($sisa <= 0)

<a href="{{ route('pos.kredit.print',$k->id) }}" 
class="btn btn-sm btn-success">

<i class="bi bi-printer"></i> Cetak Struk

</a>

@endif

</td>

</tr>

@empty

<tr>
<td colspan="8" class="text-center text-muted">
Tidak ada transaksi kredit
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

@endsection
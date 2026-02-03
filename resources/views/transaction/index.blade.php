@extends('layouts.app')
@section('title','Transaksi')
@section('content')
<h3>Riwayat Transaksi</h3>
<table class="table">
@foreach($data as $trx)
<tr>
<td>{{ $trx->trx_number }}</td>
<td>{{ $trx->total }}</td>
<td>{{ $trx->status }}</td>
<td><a href="/transactions/{{ $trx->id }}/edit">Detail</a></td>
</tr>
@endforeach
</table>
@endsection
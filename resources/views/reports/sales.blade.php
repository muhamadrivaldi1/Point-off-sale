@extends('layouts.app')
@section('content')
<h3>Laporan Penjualan</h3>
<table class="table">
@foreach($data as $row)
<tr><td>{{ $row->date }}</td><td>{{ $row->total }}</td></tr>
@endforeach
</table>
@endsection
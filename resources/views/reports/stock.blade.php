@extends('layouts.app')

@section('content')
<h3>Laporan Stok</h3>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Unit</th>
            <th>Lokasi</th>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ $row->unit->product->name }}</td>
            <td>{{ $row->unit->unit_name }}</td>
            <td>{{ $row->location }}</td>
            <td>{{ $row->qty }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

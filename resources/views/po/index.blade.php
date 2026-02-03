@extends('layouts.app')
@section('content')
<h3>Purchase Order</h3>
<a href="/po/create" class="btn btn-primary">Buat PO</a>
<table class="table mt-3">
@foreach($pos as $po)
<tr>
<td>{{ $po->po_number }}</td>
<td>{{ $po->status }}</td>
<td><a href="/po/{{ $po->id }}/edit">Edit</a></td>
</tr>
@endforeach
</table>
@endsection
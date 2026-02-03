@extends('layouts.app')
@section('content')
<h3>Member</h3>
<a href="/members/create" class="btn btn-primary">Tambah</a>
<table class="table mt-3">
@foreach($members as $m)
<tr><td>{{ $m->name }}</td><td>{{ $m->phone }}</td></tr>
@endforeach
</table>
@endsection
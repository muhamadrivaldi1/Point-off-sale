@extends('layouts.app')
@section('content')
<h3>Closing Kasir</h3>
<form method="POST">@csrf
<button class="btn btn-danger">Tutup Kasir</button>
</form>
@endsection
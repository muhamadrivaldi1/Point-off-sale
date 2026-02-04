@extends('layouts.app')
@section('title','Daftar Member')

@section('content')
<h3>Member</h3>

<a href="{{ route('members.create') }}" class="btn btn-primary mb-3">Tambah Member</a>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Telepon</th>
            <th>Poin</th>
            <th width="150">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($members as $i => $m)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $m->name }}</td>
            <td>{{ $m->phone }}</td>
            <td>{{ $m->points }}</td>
            <td>
                <a href="{{ route('members.edit', $m->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('members.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus member ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted">Belum ada member</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Member</h1>

    <a href="{{ route('members.create') }}" class="btn btn-primary mb-3">Tambah Member</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Level</th>
                <th>Diskon (%)</th>
                <th>Total Spent</th>
                <th>Poin</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = ($members->currentPage() - 1) * $members->perPage();
            @endphp
            @foreach($members as $member)
            @php $no++; @endphp
            <tr>
                <td>{{ $no }}</td>
                <td>{{ $member->name }}</td>
                <td>{{ $member->phone }}</td>
                <td>{{ $member->address }}</td>
                <td>{{ $member->level }}</td>
                <td>{{ $member->discount }}</td>
                <td>{{ number_format($member->total_spent, 0, ',', '.') }}</td>
                <td>{{ $member->points }}</td>
                <td>{{ ucfirst($member->status) }}</td>
                <td>
                    <a href="{{ route('members.edit', $member->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('members.destroy', $member->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus member?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $members->links() }}
</div>
@endsection

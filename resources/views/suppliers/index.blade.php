@extends('layouts.app')

@section('title','Master Supplier')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <span>Master Supplier</span>
        <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-light">
            + Tambah Supplier
        </a>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $s)
                <tr>
                    <td>{{ $s->kode_supplier }}</td>
                    <td>{{ $s->nama_supplier }}</td>
                    <td>{{ $s->alamat }}</td>
                    <td>{{ $s->telepon }}</td>
                    <td>
                        <a href="{{ route('suppliers.edit', $s->id) }}"
                           class="btn btn-sm btn-warning">Edit</a>

                        <form action="{{ route('suppliers.destroy', $s->id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus supplier?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection

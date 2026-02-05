@extends('layouts.app')
@section('title','Daftar Member')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Daftar Member</h3>
    <a href="{{ route('members.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Member
    </a>
</div>

{{-- Alert sukses --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Filter / Search --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" class="form-control" placeholder="Cari nama atau telepon..."
               value="{{ request('q') }}">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2 text-end">
        <a href="{{ route('members.index') }}" class="btn btn-secondary w-100">Reset</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th style="width:80px;">Poin</th>
                        <th style="width:180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $i => $m)
                    <tr>
                        {{-- Nomor urut --}}
                        <td>
                            {{ $members instanceof \Illuminate\Pagination\LengthAwarePaginator ? $members->firstItem() + $i : $loop->iteration }}
                        </td>
                        <td>{{ $m->name }}</td>
                        <td>{{ $m->phone }}</td>
                        <td class="fw-bold">{{ $m->points }}</td>
                        <td>
                            <a href="{{ route('members.edit', $m->id) }}" class="btn btn-sm btn-warning mb-1">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <form action="{{ route('members.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus member ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger mb-1">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
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
        </div>
    </div>
</div>

{{-- Pagination --}}
@if($members instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="mt-3 d-flex justify-content-center">
    {{ $members->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endif
@endsection

@extends('layouts.app')

@section('title','Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Produk</h4>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        + Tambah Produk
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- SEARCH --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" name="q" class="form-control" placeholder="Cari produk..."
               value="{{ request('q') }}">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Cari</button>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Reset</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-light text-center">
            <tr>
                <th>Nama</th>
                <th>SKU</th>
                <th>Unit</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td>{{ $p->sku ?? '-' }}</td>
                <td>
                    @foreach($p->units as $u)
                        <span class="badge bg-info text-dark mb-1">{{ $u->unit_name }}</span><br>
                    @endforeach
                </td>
                <td>
                    @foreach($p->units as $u)
                        Rp {{ number_format($u->price,0,',','.') }}<br>
                    @endforeach
                </td>
                <td class="text-center">
                    <a href="{{ route('products.edit',$p->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>
                    <form action="{{ route('products.destroy',$p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Belum ada produk</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection

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
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">
    <thead class="table-dark text-center">
        <tr>
            <th width="20%">Nama</th>
            <th width="15%">SKU</th>
            <th width="25%">Unit</th>
            <th width="20%">Harga</th>
            <th width="20%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $p)
        <tr>
            <td>{{ $p->name }}</td>
            <td>{{ $p->sku ?? '-' }}</td>
            <td>
                @foreach($p->units as $u)
                    <span class="badge bg-secondary mb-1">
                        {{ $u->unit_name }}
                    </span><br>
                @endforeach
            </td>
            <td>
                @foreach($p->units as $u)
                    Rp {{ number_format($u->price,0,',','.') }}<br>
                @endforeach
            </td>
            <td class="text-center">
                <a href="{{ route('products.edit',$p->id) }}"
                   class="btn btn-sm btn-warning">
                    Edit
                </a>

                <form action="{{ route('products.destroy',$p->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus produk ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">
                        Hapus
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted">
                Belum ada produk
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $products->links('pagination::bootstrap-5') }}
</div>
@endsection

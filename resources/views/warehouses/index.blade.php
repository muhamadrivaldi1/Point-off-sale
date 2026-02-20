@extends('layouts.app')
@section('title','Data Gudang')

@section('content')
<div class="container">

    <h4 class="mb-3">Manajemen Gudang</h4>

    {{-- TAMBAH --}}
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('warehouses.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" name="name" class="form-control" placeholder="Nama Gudang" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="code" class="form-control" placeholder="Kode Gudang" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            + Tambah
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- LIST --}}
    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Status POS</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($warehouses as $w)
                    <tr class="text-center">
                        <td>{{ $warehouses->firstItem() + $loop->index }}</td>
                        <td>{{ $w->name }}</td>
                        <td>{{ $w->code }}</td>
                        <td>
                            @if($w->is_active)
                                <span class="badge bg-success">AKTIF</span>
                            @else
                                <span class="badge bg-secondary">NON AKTIF</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('warehouses.setActive',$w->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-warning">
                                    Gunakan di POS
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-center">
                {{ $warehouses->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

</div>
@endsection
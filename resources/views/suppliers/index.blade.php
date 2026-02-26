@extends('layouts.app')

@section('title','Master Supplier')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

    <span>Master Supplier</span>

    <div class="d-flex gap-2">

        <form method="GET" action="{{ route('suppliers.index') }}" class="d-flex">

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control form-control-sm me-2"
                   placeholder="Cari..."
                   style="width:180px">

            <button class="btn btn-sm btn-light me-1">🔍</button>

            @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-secondary">
                    Reset
                </a>
            @endif

        </form>

        <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-primary">
            + Tambah Supplier
        </a>

    </div>

</div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Supplier</th>
                        <th>NPWP</th>
                        <th>Alamat</th>
                        <th>Telp 1</th>
                        <th>Telp 2</th>
                        <th>Fax</th>
                        <th>Email</th>
                        <th>Bank</th>
                        <th>No Rek</th>
                        <th>CP</th>
                        <th>Jabatan</th>
                        <th>Telp CP</th>
                        <th>No Seri FP</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($suppliers as $s)
                    <tr>
                        {{-- Nomor urut otomatis --}}
                        <td class="text-center">
                            {{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $loop->iteration }}
                        </td>

                        <td>{{ $s->nama_supplier }}</td>
                        <td>{{ $s->npwp }}</td>
                        <td>{{ $s->alamat }}</td>
                        <td>{{ $s->telepon }}</td>
                        <td>{{ $s->telepon2 }}</td>
                        <td>{{ $s->fax }}</td>
                        <td>{{ $s->email }}</td>
                        <td>{{ $s->bank }}</td>
                        <td>{{ $s->nomor_rekening }}</td>
                        <td>{{ $s->cp }}</td>
                        <td>{{ $s->jabatan_cp }}</td>
                        <td>{{ $s->telepon_cp }}</td>
                        <td>{{ $s->nomor_seri_fp }}</td>

                        <td class="text-center">
                            <a href="{{ route('suppliers.edit', $s->id) }}"
                               class="btn btn-sm btn-warning">
                               Edit
                            </a>

                            <form action="{{ route('suppliers.destroy', $s->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus supplier?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $suppliers->links() }}
        </div>

    </div>
</div>
@endsection
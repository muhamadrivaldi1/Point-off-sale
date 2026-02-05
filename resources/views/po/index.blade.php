@extends('layouts.app')
@section('title','Daftar Purchase Order')

@section('content')
<h3 class="mb-4">Purchase Order</h3>

<!-- Tombol Buat PO -->
<a href="{{ route('po.create') }}" class="btn btn-primary mb-3">
    <i class="bi bi-plus-lg"></i> Buat Draft PO
</a>

<!-- Alert -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nomor PO</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $i => $po)
                    <tr>
                        {{-- Nomor urut otomatis sesuai halaman --}}
                        <td>{{ $pos->firstItem() + $i }}</td>

                        <td>{{ $po->po_number }}</td>
                        <td>
                            @if($po->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($po->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($po->status === 'received')
                                <span class="badge bg-info text-dark">Received</span>
                            @endif
                        </td>
                        <td>{{ $po->created_at->format('d M Y') }}</td>
                        <td>
                            <!-- Edit -->
                            <a href="{{ route('po.edit', $po->id) }}"
                               class="btn btn-sm btn-warning mb-1">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>

                            <!-- Hapus hanya jika draft -->
                            @if($po->status === 'draft')
                            <form action="{{ route('po.destroy', $po->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus PO ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger mb-1">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Belum ada Purchase Order
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination Bootstrap 5 --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $pos->links('pagination::bootstrap-5') }}
</div>
@endsection

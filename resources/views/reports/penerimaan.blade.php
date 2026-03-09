@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">
                        Laporan Penerimaan Barang (Stok Masuk)
                    </h5>
                </div>

                <div class="card-body">

                    <!-- Filter -->
                    <form action="{{ route('reports.penerimaan') }}" method="GET" class="row g-3 mb-4">

                        <div class="col-md-3">
                            <label class="small mb-1">Supplier</label>
                            <select name="supplier_id" class="form-control">
                                <option value="all">-- Semua --</option>
                                @foreach(\App\Models\Supplier::orderBy('nama_supplier')->get() as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                        {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="small mb-1">Status</label>
                            <select name="status" class="form-control">
                                <option value="all">-- Semua --</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="small mb-1">Dari Tanggal</label>
                            <input type="date" name="from" class="form-control" value="{{ $from }}">
                        </div>

                        <div class="col-md-3">
                            <label class="small mb-1">Sampai Tanggal</label>
                            <input type="date" name="to" class="form-control" value="{{ $to }}">
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Cari</button>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>No. PO</th>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th>Pembayaran</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $index => $row)
                                    <tr>
                                        <td>{{ $data->firstItem() + $index }}</td>
                                        <td>{{ $row->po_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ optional($row->supplier)->nama_supplier ?? '-' }}</td>
                                        <td>{{ $row->jenis_pembayaran }}</td>
                                        <td>-</td>
                                        <td>Rp {{ number_format($row->total,0,',','.') }}</td>
                                        <td>
                                            @if($row->status == 'received')
                                                <span class="badge bg-success">Barang Diterima</span>
                                            @elseif($row->status == 'approved')
                                                <span class="badge bg-info">Approved</span>
                                            @elseif($row->status == 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @else
                                                <span class="badge bg-danger">Canceled</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('po.show', $row->id) }}" class="btn btn-sm btn-info">Detail</a>

                                            <form action="{{ route('po.destroy', $row->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin ingin menghapus PO ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            Tidak ada data penerimaan barang pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $data->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-box-arrow-in-down me-2"></i>Laporan Penerimaan Barang (Stok Masuk)
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('reports.penerimaan') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Supplier</label>
                            <select name="supplier_id" class="form-select form-select-sm">
                                <option value="all">-- Semua Supplier --</option>
                                @foreach(\App\Models\Supplier::orderBy('nama_supplier')->get() as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                        {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all">-- Semua Status --</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Dari Tanggal</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Sampai Tanggal</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="bi bi-search me-1"></i> Cari
                            </button>
                            <a href="{{ route('reports.penerimaan.export', request()->query()) }}" class="btn btn-success btn-sm px-3">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th class="py-3">#</th>
                                    <th>NO. PO</th>
                                    <th>TANGGAL</th>
                                    <th>SUPPLIER</th>
                                    <th>PEMBAYARAN</th>
                                    <th>TOTAL</th>
                                    <th>STATUS</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($data as $index => $row)
                                    <tr>
                                        <td>{{ $data->firstItem() + $index }}</td>
                                        <td class="fw-bold text-dark">{{ $row->po_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ optional($row->supplier)->nama_supplier ?? '-' }}</td>
                                        <td><span class="text-uppercase small">{{ $row->jenis_pembayaran }}</span></td>
                                        <td class="fw-bold text-dark">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                        <td>
                                            @php
                                                $badgeClass = [
                                                    'received' => 'bg-success',
                                                    'approved' => 'bg-info text-dark',
                                                    'draft'    => 'bg-secondary',
                                                    'canceled' => 'bg-danger'
                                                ][$row->status] ?? 'bg-dark';
                                                
                                                $statusLabel = [
                                                    'received' => 'Diterima',
                                                    'approved' => 'Approved',
                                                    'draft'    => 'Draft',
                                                    'canceled' => 'Dibatalkan'
                                                ][$row->status] ?? $row->status;
                                            @endphp
                                            <span class="badge {{ $badgeClass }} shadow-sm" style="font-size: 0.7rem;">
                                                {{ strtoupper($statusLabel) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('po.show', $row->id) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <form action="{{ route('po.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus PO ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm border-start-0">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            Tidak ada data penerimaan barang pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
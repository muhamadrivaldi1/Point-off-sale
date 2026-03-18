@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">

                {{-- HEADER --}}
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-box-arrow-in-down me-2"></i>
                        Laporan Penerimaan Barang (Stok Masuk)
                    </h5>
                </div>

                <div class="card-body">

                    {{-- FILTER --}}
                    <form action="{{ route('reports.penerimaan') }}" method="GET" class="row g-3 mb-4">

                        {{-- SUPPLIER --}}
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

                        {{-- STATUS --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all">-- Semua Status --</option>
                                <option value="draft"    {{ request('status') == 'draft'    ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                        </div>

                        {{-- TANGGAL DARI --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Dari</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>

                        {{-- TANGGAL SAMPAI --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Sampai</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                        </div>

                        {{-- FILTER BARANG --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Nama Barang</label>
                            <input type="text" name="product" class="form-control form-control-sm"
                                   placeholder="Cari barang..."
                                   value="{{ request('product') }}">
                        </div>

                        {{-- BUTTON --}}
                        <div class="col-md-12 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-3">
                                <i class="bi bi-search me-1"></i> Cari
                            </button>

                            <a href="{{ route('reports.penerimaan') }}"
                               class="btn btn-outline-secondary btn-sm shadow-sm px-3"
                               title="Reset Filter">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </a>

                            <a href="{{ route('reports.penerimaan.export', request()->query()) }}"
                               class="btn btn-success btn-sm shadow-sm px-3"
                               title="Export Excel">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                            </a>
                        </div>

                    </form>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light small">
                                <tr>
                                    <th>#</th>
                                    <th>NO. PO</th>
                                    <th>TANGGAL</th>
                                    <th>SUPPLIER</th>
                                    <th>BARANG</th>
                                    <th>QTY</th>
                                    <th>PEMBAYARAN</th>
                                    <th>TOTAL</th>
                                    <th>STATUS</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>

                            <tbody class="small">
                                @forelse($data as $index => $row)
                                    @php
                                        $keyword       = request('product');
                                        $filteredItems = $keyword
                                            ? $row->items->filter(function ($item) use ($keyword) {
                                                $productName = $item->productUnit->product->name ?? '';
                                                return stripos($productName, $keyword) !== false;
                                              })
                                            : $row->items;

                                        // Cast ke int agar tidak ada desimal
                                        $totalQty = (int) $filteredItems->sum('qty');
                                    @endphp
                                    <tr>
                                        <td>{{ $data->firstItem() + $index }}</td>

                                        <td class="fw-bold">{{ $row->po_number }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($row->created_at)->format('H:i') }}
                                            </small>
                                        </td>

                                        <td>{{ optional($row->supplier)->nama_supplier ?? '-' }}</td>

                                        {{-- BARANG: hanya tampilkan item yang cocok dengan filter --}}
                                        <td style="min-width:200px;">
                                            @forelse($filteredItems as $item)
                                                <div>
                                                    • {{ $item->productUnit->product->name ?? '-' }}
                                                    {{-- (int) cast: 1.00 → 1, 10.00 → 10 --}}
                                                    <span class="text-muted">x{{ (int) $item->qty }}</span>
                                                </div>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>

                                        {{-- TOTAL QTY --}}
                                        <td class="text-center fw-bold">
                                            {{ $totalQty }}
                                        </td>

                                        <td class="text-uppercase small">
                                            {{ $row->jenis_pembayaran }}
                                        </td>

                                        <td class="fw-bold">
                                            Rp {{ number_format($row->total, 0, ',', '.') }}
                                        </td>

                                        {{-- STATUS --}}
                                        <td>
                                            @php
                                                $badgeClass = [
                                                    'received' => 'bg-success',
                                                    'approved' => 'bg-info text-dark',
                                                    'draft'    => 'bg-secondary',
                                                    'canceled' => 'bg-danger',
                                                ][$row->status] ?? 'bg-dark';
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ strtoupper($row->status) }}
                                            </span>
                                        </td>

                                        {{-- AKSI --}}
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('po.show', $row->id) }}"
                                                   class="btn btn-outline-info btn-sm"
                                                   title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <form action="{{ route('po.destroy', $row->id) }}" method="POST"
                                                      style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            onclick="return confirm('Yakin hapus data ini?')"
                                                            class="btn btn-outline-danger btn-sm"
                                                            title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Tidak ada data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Menampilkan {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }}
                            dari {{ $data->total() }} data
                        </small>

                        {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
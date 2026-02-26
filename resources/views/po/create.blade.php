@extends('layouts.app')

@section('title', isset($po) && $po->id ? 'Edit PO' : 'Buat PO Baru')

@push('styles')
<style>
    .po-wrapper {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 12px;
        height: calc(100vh - 110px);
        font-size: 0.82rem;
    }

    /* PANEL KIRI */
    .panel-left {
        display: flex;
        flex-direction: column;
        gap: 0;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        overflow: hidden;
    }

    .panel-left-header {
        background: #343a40;
        color: #fff;
        padding: 7px 12px;
        font-weight: 600;
        font-size: 0.82rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .po-form-body {
        padding: 10px 12px;
        overflow-y: auto;
        flex: 1;
    }

    .field-row {
        display: grid;
        grid-template-columns: 110px 1fr;
        align-items: center;
        gap: 4px;
        margin-bottom: 5px;
    }

    .field-row label {
        font-size: 0.78rem;
        color: #495057;
        margin: 0;
        white-space: nowrap;
    }

    .field-row .form-control,
    .field-row .form-select {
        font-size: 0.78rem;
        padding: 3px 7px;
        height: 26px;
        border-radius: 2px;
        border-color: #adb5bd;
    }

    .field-row .form-control:focus,
    .field-row .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13,110,253,.15);
    }

    /* Badge nomor berubah */
    .nomor-badge {
        display: inline-block;
        font-size: 0.7rem;
        padding: 1px 6px;
        border-radius: 3px;
        font-weight: 600;
        margin-left: 4px;
        vertical-align: middle;
    }
    .nomor-badge.pr { background: #d1ecf1; color: #0c5460; }
    .nomor-badge.po { background: #fff3cd; color: #856404; }

    /* PANEL KANAN */
    .panel-right {
        display: flex;
        flex-direction: column;
        gap: 0;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        overflow: hidden;
    }

    .preview-area {
        background: #dee2e6;
        flex: 0 0 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #868e96;
        font-size: 0.8rem;
        border-bottom: 1px solid #ced4da;
    }

    .item-form-area {
        padding: 8px 12px;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    .item-form-area label {
        font-size: 0.72rem;
        color: #6c757d;
        margin-bottom: 2px;
        display: block;
    }

    .item-form-area .form-control,
    .item-form-area .form-select {
        font-size: 0.78rem;
        padding: 3px 6px;
        height: 26px;
        border-radius: 2px;
        border-color: #adb5bd;
    }

    /* Tabel detail item */
    .detail-items-wrap {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .detail-items-wrap table {
        font-size: 0.75rem;
        margin: 0;
    }

    .detail-items-wrap table thead th {
        background: #343a40;
        color: #fff;
        padding: 5px 8px;
        font-size: 0.72rem;
        font-weight: 500;
        border: none;
        white-space: nowrap;
    }

    .detail-items-wrap table tbody {
        display: block;
        overflow-y: auto;
        max-height: 180px;
    }

    .detail-items-wrap table thead,
    .detail-items-wrap table tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .detail-items-wrap table tbody td {
        padding: 4px 8px;
        border-color: #f1f3f5;
        vertical-align: middle;
    }

    .detail-items-wrap table tbody tr:hover {
        background: #e8f4fd;
    }

    /* Summary footer */
    .summary-footer {
        border-top: 1px solid #dee2e6;
        padding: 8px 12px;
        background: #f8f9fa;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px;
    }

    .summary-group { display: flex; flex-direction: column; gap: 4px; }

    .summary-row {
        display: grid;
        grid-template-columns: 105px 1fr;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
    }

    .summary-row label { color: #495057; margin: 0; }

    .summary-row .form-control {
        font-size: 0.75rem;
        padding: 2px 6px;
        height: 24px;
        border-radius: 2px;
        border-color: #adb5bd;
        background: #e9ecef;
        color: #495057;
    }

    .summary-row .form-control.total-highlight {
        background: #fff3cd;
        color: #856404;
        font-weight: 700;
        border-color: #ffc107;
    }

    .action-bar {
        border-top: 1px solid #dee2e6;
        padding: 6px 12px;
        background: #fff;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .action-bar .btn {
        font-size: 0.82rem;
        padding: 4px 24px;
        border-radius: 2px;
    }

    input.ro { background: #e9ecef !important; }

    .bonus-badge {
        background: #d4edda;
        color: #155724;
        padding: 1px 6px;
        border-radius: 3px;
        font-size: 0.72rem;
    }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-1 mb-2" style="font-size:0.82rem">
    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show py-1 mb-2" style="font-size:0.82rem">
    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="po-wrapper">

    {{-- ===== PANEL KIRI ===== --}}
    <div class="panel-left">

        <div class="panel-left-header">
            <span>
                <i class="bi bi-cart-plus me-1"></i>
                @if($po->status === 'draft' && !$po->supplier_id)
                    Buat PO Baru
                @else
                    Edit PO
                @endif
            </span>
            {{-- Badge status --}}
            <span class="badge
                @if($po->status === 'draft') bg-secondary
                @elseif($po->status === 'approved') bg-success
                @elseif($po->status === 'received') bg-primary
                @else bg-danger
                @endif">
                {{ ucfirst($po->status) }}
            </span>
        </div>

        <div class="po-form-body">

            {{-- Form header: simpan ke store (baru) atau updateHeader (edit) --}}
            @if($po->supplier_id)
                {{-- PO sudah punya supplier = sudah pernah disimpan → updateHeader --}}
                <form method="POST" action="{{ route('po.updateHeader', $po->id) }}" id="form-header">
                @csrf
            @else
                {{-- PO baru (draft kosong) → store --}}
                <form method="POST" action="{{ route('po.store') }}" id="form-header">
                @csrf
            @endif

            {{-- JENIS TRANSAKSI --}}
            <div class="field-row">
                <label>Jenis Transaksi</label>
                <select name="jenis_transaksi" id="jenis_transaksi" class="form-select"
                    {{ $po->status !== 'draft' ? 'disabled' : '' }} required>
                    <option value="Pembelian" {{ ($po->jenis_transaksi ?? 'Pembelian') === 'Pembelian' ? 'selected' : '' }}>
                        PR — Pembelian Reguler
                    </option>
                    <option value="PO" {{ ($po->jenis_transaksi ?? '') === 'PO' ? 'selected' : '' }}>
                        PO — Private Order
                    </option>
                </select>
            </div>

            {{-- NOMOR TRANSAKSI (auto-update prefix via JS) --}}
            <div class="field-row">
                <label>
                    Nomor Transaksi
                    <span id="nomor-badge" class="nomor-badge {{ ($po->jenis_transaksi ?? 'Pembelian') === 'PO' ? 'po' : 'pr' }}">
                        {{ ($po->jenis_transaksi ?? 'Pembelian') === 'PO' ? 'PO' : 'PR' }}
                    </span>
                </label>
                <input type="text" name="po_number" id="po_number"
                       class="form-control ro"
                       value="{{ $po->po_number }}" readonly>
            </div>

            <div class="field-row">
                <label>Tanggal Transaksi</label>
                <input type="date" name="tanggal" class="form-control"
                       value="{{ $po->tanggal?->format('Y-m-d') ?? date('Y-m-d') }}"
                       {{ $po->status !== 'draft' ? 'readonly' : '' }} required>
            </div>

            <div class="field-row">
                <label>Gudang</label>
                <input type="text" name="gudang" class="form-control"
                       value="{{ $po->gudang ?? 'Gudang Utama' }}"
                       {{ $po->status !== 'draft' ? 'readonly' : '' }}>
            </div>

            <div class="field-row">
                <label>Nama Supplier</label>
                <select name="supplier_id" class="form-select"
                    {{ $po->status !== 'draft' ? 'disabled' : '' }} required>
                    <option value="">-- Pilih --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}"
                            {{ ($po->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->nama_supplier }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Field-field yang disabled saat jenis = PO --}}
            <div class="field-row">
                <label>Nomor Faktur</label>
                <input type="text" name="nomor_faktur" id="field-nomor-faktur"
                       class="form-control"
                       value="{{ $po->nomor_faktur }}"
                       {{ $po->status !== 'draft' ? 'readonly' : '' }}>
            </div>

            <div class="field-row">
                <label>Tanggal Faktur</label>
                <input type="date" name="tanggal_faktur" id="field-tanggal-faktur"
                       class="form-control"
                       value="{{ $po->tanggal_faktur?->format('Y-m-d') }}"
                       {{ $po->status !== 'draft' ? 'readonly' : '' }}>
            </div>

            <div class="field-row">
                <label>Jenis Pembayaran</label>
                <select name="jenis_pembayaran" id="field-jenis-pembayaran"
                    class="form-select" {{ $po->status !== 'draft' ? 'disabled' : '' }}>
                    <option value="Cash"     {{ ($po->jenis_pembayaran ?? 'Cash') === 'Cash'     ? 'selected' : '' }}>Cash</option>
                    <option value="Kredit"   {{ ($po->jenis_pembayaran ?? '')     === 'Kredit'   ? 'selected' : '' }}>Kredit</option>
                    <option value="Transfer" {{ ($po->jenis_pembayaran ?? '')     === 'Transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </div>

            <div class="field-row">
                <label>Jk. Waktu (hari)</label>
                <input type="number" name="jk_waktu" id="field-jk-waktu"
                       class="form-control"
                       value="{{ $po->jk_waktu ?? 0 }}" min="0"
                       {{ $po->status !== 'draft' ? 'readonly' : '' }}>
            </div>

            <div class="field-row">
                <label>Jatuh Tempo</label>
                <input type="date" name="tanggal_jatuh_tempo" id="field-tanggal-jatuh-tempo"
                       class="form-control"
                       value="{{ $po->tanggal_jatuh_tempo?->format('Y-m-d') }}"
                       {{ $po->status !== 'draft' ? 'readonly' : '' }}>
            </div>

            <div class="field-row">
                <label>PPN</label>
                <select name="ppn" id="field-ppn"
                    class="form-select" {{ $po->status !== 'draft' ? 'disabled' : '' }}>
                    <option value="0"   {{ ($po->ppn ?? 0) == 0   ? 'selected' : '' }}>0% (Non PPN)</option>
                    <option value="11"  {{ ($po->ppn ?? 0) == 11  ? 'selected' : '' }}>11% (PPN)</option>
                    <option value="1.1" {{ ($po->ppn ?? 0) == 1.1 ? 'selected' : '' }}>1.1% (Final)</option>
                </select>
            </div>

            <div class="field-row">
                <label>Bulan Lapor</label>
                <input type="month" name="bulan_lapor" id="field-bulan-lapor"
                       class="form-control"
                       value="{{ $po->bulan_lapor }}"
                       {{ $po->status !== 'draft' ? 'readonly' : '' }}>
            </div>

            </form>
        </div>

        <div class="action-bar">
            @if($po->status === 'draft')
                <button type="submit" form="form-header" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i> Simpan Header
                </button>
            @endif
            <a href="{{ route('po.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-list me-1"></i> Daftar PO
            </a>
        </div>

    </div>{{-- end panel-left --}}

    {{-- ===== PANEL KANAN ===== --}}
    <div class="panel-right">
{{-- 
        <div class="preview-area">
            <div class="text-center">
                <i class="bi bi-image fs-1 opacity-25 d-block mb-1"></i>
                <span class="opacity-50">Preview Barang</span>
            </div>
        </div> --}}

        {{-- Form tambah item --}}
        @if($po->status === 'draft')
        <div class="item-form-area">
            <form method="POST" action="{{ route('po.addItem', $po->id) }}" id="form-item">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col" style="min-width:180px">
                    <label>Nama Barang</label>
                    <select name="product_unit_id" id="product_unit_id"
                            class="form-select" onchange="onProdukChange(this)" required>
                        <option value="">-- Pencarian Item --</option>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}"
                                data-satuan="{{ $unit->unit_name ?? 'pcs' }}"
                                data-harga="{{ $unit->price ?? 0 }}">
                            {{ $unit->product->name ?? '-' }}
                            @if($unit->unit_name) ({{ $unit->unit_name }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto" style="width:80px">
                    <label>Jumlah</label>
                    <input type="number" name="qty" id="input_qty"
                           class="form-control" value="1" min="1" step="1"
                           oninput="hitungSubtotal()" required>
                </div>
                <div class="col-auto" style="width:75px">
                    <label>Satuan</label>
                    <input type="text" id="preview_satuan" name="satuan"
                           class="form-control ro" readonly placeholder="-">
                </div>
                <div class="col-auto" style="width:115px">
                    <label>Harga Satuan</label>
                    <input type="number" name="price" id="input_price"
                           class="form-control" value="0" min="0" step="1"
                           oninput="hitungSubtotal()" required>
                </div>
                <div class="col-auto" style="width:115px">
                    <label>Subtotal</label>
                    <input type="text" id="preview_subtotal"
                           class="form-control ro text-end fw-semibold" readonly value="0">
                </div>
                <div class="col-auto d-flex align-items-end" style="padding-bottom:5px">
                    <div style="width:1px;height:20px;background:#dee2e6"></div>
                </div>
                <div class="col" style="min-width:140px">
                    <label>
                        <i class="bi bi-gift me-1 text-success"></i>
                        Bonus <span class="text-muted" style="font-size:0.65rem">(opsional)</span>
                    </label>
                    <input type="text" name="bonus_nama" class="form-control"
                           placeholder="cth: Piring, Gelas..." maxlength="100">
                </div>
                <div class="col-auto" style="width:85px">
                    <label>Jml Bonus</label>
                    <input type="number" name="bonus_qty"
                           class="form-control" value="0" min="0" step="1">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary px-3" style="font-size:0.78rem">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </div>
            </div>
            </form>
        </div>
        @endif

        {{-- Tabel detail item --}}
        <div class="detail-items-wrap">
            <div style="overflow-y:auto;flex:1">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="25">#</th>
                            <th>Nama Barang</th>
                            <th class="text-end" width="55">Qty</th>
                            <th width="60">Satuan</th>
                            <th class="text-end" width="100">Harga</th>
                            <th class="text-end" width="110">Subtotal</th>
                            <th>Bonus</th>
                            <th class="text-end" width="70">Jml Bonus</th>
                            @if($po->status === 'draft')
                            <th width="30"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($po->items as $i => $item)
                        @php $subtotal = $item->qty * $item->price; @endphp
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>{{ $item->unit->product->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td>{{ $item->unit->unit_name ?? $item->unit->unit ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td>
                                @if(!empty($item->bonus_nama) && ($item->bonus_qty ?? 0) > 0)
                                    <span class="bonus-badge">
                                        <i class="bi bi-gift"></i> {{ $item->bonus_nama }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:0.72rem">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(($item->bonus_qty ?? 0) > 0)
                                    <strong class="text-success">{{ number_format($item->bonus_qty, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @if($po->status === 'draft')
                            <td class="text-center">
                                <form method="POST" action="{{ route('po.deleteItem', $item->id) }}"
                                      onsubmit="return confirm('Hapus item ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link text-danger p-0" style="font-size:0.8rem">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">
                                <i class="bi bi-inbox me-1"></i> Belum ada item
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary Footer --}}
        @php
            $totalItems = $po->items->count();
            $totalQty   = $po->items->sum('qty');
            $totalBonus = $po->items->sum('bonus_qty');
            $itemBonus  = $po->items->filter(fn($i) => ($i->bonus_qty ?? 0) > 0)->count();
            $grandTotal = $po->items->sum(fn($i) => $i->qty * $i->price);
            $ppnRp      = $grandTotal * ($po->ppn ?? 0) / 100;
            $totalAkhir = $grandTotal + $ppnRp;
        @endphp

        <div class="summary-footer">
            <div class="summary-group">
                <div class="summary-row">
                    <label>Jumlah Item</label>
                    <input type="text" class="form-control" value="{{ $totalItems }} item" readonly>
                </div>
                <div class="summary-row">
                    <label>Total Qty</label>
                    <input type="text" class="form-control" value="{{ number_format($totalQty, 0, ',', '.') }}" readonly>
                </div>
                <div class="summary-row">
                    <label>Item Ada Bonus</label>
                    <input type="text" class="form-control"
                           value="{{ $itemBonus }} item ({{ number_format($totalBonus,0,',','.') }} pcs)" readonly>
                </div>
            </div>

            <div class="summary-group">
                <div class="summary-row">
                    <label>Subtotal</label>
                    <input type="text" class="form-control"
                           value="Rp {{ number_format($grandTotal, 0, ',', '.') }}" readonly>
                </div>
                <div class="summary-row">
                    <label>PPN ({{ $po->ppn ?? 0 }}%)</label>
                    <input type="text" class="form-control"
                           value="Rp {{ number_format($ppnRp, 0, ',', '.') }}" readonly>
                </div>
                <div class="summary-row">
                    <label>Jenis Bayar</label>
                    <input type="text" class="form-control"
                           value="{{ $po->jenis_pembayaran ?? '-' }}" readonly>
                </div>
            </div>

            <div class="summary-group">
                <div class="summary-row">
                    <label><strong>TOTAL</strong></label>
                    <input type="text" class="form-control total-highlight"
                           value="Rp {{ number_format($totalAkhir, 0, ',', '.') }}" readonly>
                </div>
                <div class="summary-row">
                    <label>Jatuh Tempo</label>
                    <input type="text" class="form-control"
                           value="{{ $po->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}" readonly>
                </div>
                <div class="summary-row">
                    <label>Status</label>
                    <input type="text" class="form-control"
                           value="{{ ucfirst($po->status) }}" readonly>
                </div>
            </div>
        </div>

        <div class="action-bar">
            @if($po->status === 'draft')
                <button type="submit" form="form-header" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
                <form method="POST" action="{{ route('po.approve', $po->id) }}" class="d-inline"
                      onsubmit="return confirm('Approve & kunci PO ini?')">
                    @csrf
                    <button class="btn btn-success btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                </form>
                <form method="POST" action="{{ route('po.cancel', $po->id) }}" class="d-inline"
                      onsubmit="return confirm('Batalkan PO ini?')">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-lg me-1"></i> Batal
                    </button>
                </form>
            @elseif($po->status === 'approved')
                <form method="POST" action="{{ route('po.receive', $po->id) }}"
                      onsubmit="return confirm('Tandai barang sudah diterima?')">
                    @csrf
                    <button class="btn btn-success btn-sm">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Terima Barang
                    </button>
                </form>
            @endif
            <a href="{{ route('po.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-list me-1"></i> Daftar PO
            </a>
        </div>

    </div>{{-- end panel-right --}}

</div>{{-- end po-wrapper --}}

@endsection

@push('scripts')
<script>
// ============================================================
// GANTI PREFIX NOMOR saat jenis_transaksi berubah
// PR-YmdHis = Pembelian Reguler
// PO-YmdHis = Private Order
// ============================================================
const jenisSelect  = document.getElementById('jenis_transaksi');
const poNumberInput = document.getElementById('po_number');
const nomorBadge   = document.getElementById('nomor-badge');

// Field yang di-disable saat jenis = PO
const fieldsDisabledWhenPO = [
    'field-nomor-faktur',
    'field-tanggal-faktur',
    'field-jenis-pembayaran',
    'field-jk-waktu',
    'field-tanggal-jatuh-tempo',
    'field-ppn',
    'field-bulan-lapor',
];

function updateNomor(jenis) {
    const currentVal = poNumberInput.value;       // e.g. "PR-20240601120000"
    const parts      = currentVal.split('-');      // ["PR", "20240601120000"]
    const angka      = parts.slice(1).join('-');   // bagian setelah prefix

    if (jenis === 'PO') {
        poNumberInput.value = 'PO-' + angka;
        nomorBadge.textContent = 'PO';
        nomorBadge.className   = 'nomor-badge po';
    } else {
        poNumberInput.value = 'PR-' + angka;
        nomorBadge.textContent = 'PR';
        nomorBadge.className   = 'nomor-badge pr';
    }
}

function toggleFields(jenis) {
    const isPO = jenis === 'PO';
    fieldsDisabledWhenPO.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.disabled = isPO;
            el.style.opacity = isPO ? '0.45' : '1';
        }
    });
}

// Inisialisasi saat load
jenisSelect.addEventListener('change', function () {
    updateNomor(this.value);
    toggleFields(this.value);
});

// Jalankan sekali saat halaman load
toggleFields(jenisSelect.value);

// ============================================================
// HITUNG SUBTOTAL
// ============================================================
function hitungSubtotal() {
    const qty      = parseFloat(document.getElementById('input_qty').value)   || 0;
    const price    = parseFloat(document.getElementById('input_price').value) || 0;
    const subtotal = qty * price;
    document.getElementById('preview_subtotal').value =
        subtotal.toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

function onProdukChange(selectEl) {
    const option = selectEl.selectedOptions[0];
    document.getElementById('preview_satuan').value = option.dataset.satuan || '-';
    document.getElementById('input_price').value    = parseFloat(option.dataset.harga) || 0;
    hitungSubtotal();
}

// ============================================================
// Auto-hitung jatuh tempo dari jk_waktu
// ============================================================
const jkWaktuInput    = document.getElementById('field-jk-waktu');
const jatuhTempoInput = document.getElementById('field-tanggal-jatuh-tempo');

if (jkWaktuInput && jatuhTempoInput) {
    jkWaktuInput.addEventListener('input', function () {
        const hari = parseInt(this.value);
        if (!isNaN(hari) && hari >= 0) {
            const tanggal = new Date();
            tanggal.setDate(tanggal.getDate() + hari);
            jatuhTempoInput.value = tanggal.toISOString().split('T')[0];
        }
    });
}
</script>
@endpush
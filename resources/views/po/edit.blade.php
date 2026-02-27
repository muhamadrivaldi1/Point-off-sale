@extends('layouts.app')

@section('title', 'Pembelian - ' . $po->po_number)

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

    /* Badge prefix nomor */
    .nomor-badge {
        display: inline-block;
        font-size: 0.68rem;
        padding: 1px 5px;
        border-radius: 3px;
        font-weight: 700;
        margin-left: 3px;
        vertical-align: middle;
    }
    .nomor-badge.pr { background: #d1ecf1; color: #0c5460; }
    .nomor-badge.po { background: #fff3cd; color: #856404; }

    /* Highlight kredit */
    .kredit-field {
        border-color: #fd7e14 !important;
        background: #fffbeb !important;
        box-shadow: 0 0 0 2px rgba(253,126,20,.15) !important;
    }

    /* Tabel transaksi bawah kiri */
    .table-transaksi-wrap {
        border-top: 1px solid #dee2e6;
        flex: 0 0 180px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .table-transaksi-header {
        background: #495057;
        color: #fff;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .table-transaksi-wrap .table {
        font-size: 0.75rem;
        margin: 0;
    }

    .table-transaksi-wrap .table thead th {
        background: #e9ecef;
        font-size: 0.72rem;
        padding: 4px 8px;
        border-bottom: 1px solid #ced4da;
        position: sticky;
        top: 0;
    }

    .table-transaksi-wrap .table td {
        padding: 3px 8px;
        border-color: #f1f3f5;
    }

    .table-transaksi-wrap .table tbody {
        overflow-y: auto;
        display: block;
        max-height: 130px;
    }

    .table-transaksi-wrap .table thead,
    .table-transaksi-wrap .table tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

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

    .status-pill {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 500;
    }

    input.ro { background: #e9ecef !important; }

    .bonus-badge {
        font-size: 0.68rem;
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
        border-radius: 10px;
        padding: 2px 8px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 3px;
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

@php
    $nomorParts = explode('-', $po->po_number, 2);
    $nomorAngka = $nomorParts[1] ?? now()->format('YmdHis');
    $jenisAwal  = $po->jenis_transaksi ?? 'Pembelian';
    $isDraft    = $po->status === 'draft';
    $jenisBayar = old('jenis_pembayaran', $po->jenis_pembayaran ?? 'Cash');
@endphp

<div class="po-wrapper">

    {{-- ===== PANEL KIRI ===== --}}
    <div class="panel-left">

        <div class="panel-left-header">
            <span><i class="bi bi-cart-plus me-1"></i> Pembelian</span>
            @php
                $badge = match($po->status) {
                    'draft'    => 'secondary',
                    'approved' => 'primary',
                    'received' => 'success',
                    'canceled' => 'danger',
                    default    => 'secondary'
                };
            @endphp
            <span class="status-pill bg-{{ $badge }} text-white">{{ ucfirst($po->status) }}</span>
        </div>

        <div class="po-form-body">

            @if($isDraft)
            <form method="POST" action="{{ route('po.updateHeader', $po->id) }}" id="form-header">
            @csrf
            @endif

                {{-- JENIS TRANSAKSI --}}
                <div class="field-row">
                    <label>Jenis Transaksi</label>
                    <select name="jenis_transaksi" id="jenis_transaksi"
                            class="form-select"
                            {{ !$isDraft ? 'disabled' : '' }}>
                        <option value="Pembelian" {{ $jenisAwal === 'Pembelian' ? 'selected' : '' }}>PR — Pembelian Reguler</option>
                        <option value="PO"        {{ $jenisAwal === 'PO'        ? 'selected' : '' }}>PO — Private Order</option>
                    </select>
                </div>

                {{-- NOMOR TRANSAKSI --}}
                <div class="field-row">
                    <label>
                        Nomor Transaksi
                        @if($isDraft)
                            <span id="nomor-badge" class="nomor-badge {{ $jenisAwal === 'PO' ? 'po' : 'pr' }}">
                                {{ $jenisAwal === 'PO' ? 'PO' : 'PR' }}
                            </span>
                        @endif
                    </label>
                    <input type="text" name="po_number" id="po_number"
                           class="form-control ro"
                           value="{{ $po->po_number }}" readonly>
                </div>

                <div class="field-row">
                    <label>Tanggal Transaksi</label>
                    <input type="date" name="tanggal" id="tanggal_transaksi"
                           class="form-control {{ !$isDraft ? 'ro' : '' }}"
                           value="{{ old('tanggal', $po->tanggal?->format('Y-m-d') ?? date('Y-m-d')) }}"
                           {{ !$isDraft ? 'readonly' : '' }} required>
                </div>

                <div class="field-row">
                    <label>Gudang</label>
                    <input type="text" name="gudang"
                           class="form-control {{ !$isDraft ? 'ro' : '' }}"
                           value="{{ old('gudang', $po->gudang ?? 'Gudang Utama') }}"
                           {{ !$isDraft ? 'readonly' : '' }}>
                </div>

                <div class="field-row">
                    <label>Nama Supplier</label>
                    <select name="supplier_id" class="form-select"
                            {{ !$isDraft ? 'disabled' : '' }} required>
                        <option value="">-- Pilih --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}"
                                {{ old('supplier_id', $po->supplier_id) == $sup->id ? 'selected' : '' }}>
                                {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-row">
                    <label>Nomor Faktur</label>
                    <input type="text" name="nomor_faktur" id="field-nomor-faktur"
                           class="form-control {{ !$isDraft ? 'ro' : '' }}"
                           value="{{ old('nomor_faktur', $po->nomor_faktur) }}"
                           {{ !$isDraft ? 'readonly' : '' }}>
                </div>

                <div class="field-row">
                    <label>Tanggal Faktur</label>
                    <input type="date" name="tanggal_faktur" id="field-tanggal-faktur"
                           class="form-control {{ !$isDraft ? 'ro' : '' }}"
                           value="{{ old('tanggal_faktur', $po->tanggal_faktur?->format('Y-m-d')) }}"
                           {{ !$isDraft ? 'readonly' : '' }}>
                </div>

                {{-- JENIS PEMBAYARAN --}}
                <div class="field-row">
                    <label>Jenis Pembayaran</label>
                    <select name="jenis_pembayaran" id="jenis_pembayaran"
                            class="form-select" {{ !$isDraft ? 'disabled' : '' }}>
                        <option value="Cash"     {{ $jenisBayar === 'Cash'     ? 'selected' : '' }}>Cash</option>
                        <option value="Kredit"   {{ $jenisBayar === 'Kredit'   ? 'selected' : '' }}>Kredit</option>
                        <option value="Transfer" {{ $jenisBayar === 'Transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>

                {{-- JK WAKTU + JATUH TEMPO — hanya tampil saat Kredit --}}
                <div class="field-row" id="row-jk"
                     style="{{ $jenisBayar === 'Kredit' ? '' : 'display:none' }}">
                    <label>
                        Jk. Waktu
                        <span style="font-size:0.65rem;background:#fd7e14;color:#fff;padding:1px 5px;border-radius:3px;margin-left:2px;">Kredit</span>
                    </label>
                    <div class="d-flex gap-1 align-items-center">
                        {{-- Input jk_waktu: disabled saat tersembunyi agar tidak ikut submit --}}
                        <input type="number" name="jk_waktu" id="jk_waktu"
                               class="form-control text-center kredit-field" style="width:55px"
                               value="{{ old('jk_waktu', $po->jk_waktu ?? 30) }}"
                               min="1" placeholder="30"
                               {{ !$isDraft ? 'readonly' : '' }}
                               {{ $jenisBayar !== 'Kredit' ? 'disabled' : '' }}>
                        <span style="font-size:0.72rem;color:#6c757d;white-space:nowrap">hari</span>
                        {{-- Jatuh tempo: readonly, dihitung otomatis --}}
                        <input type="date" name="tanggal_jatuh_tempo" id="tgl_jatuh_tempo"
                               class="form-control kredit-field" style="flex:1"
                               value="{{ old('tanggal_jatuh_tempo', $po->tanggal_jatuh_tempo?->format('Y-m-d')) }}"
                               {{ !$isDraft ? 'readonly' : '' }}
                               {{ $jenisBayar !== 'Kredit' ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="field-row">
                    <label>Faktur Pajak</label>
                    <select name="ppn" id="field-ppn"
                            class="form-select" {{ !$isDraft ? 'disabled' : '' }}>
                        <option value="0"   {{ old('ppn', $po->ppn) == 0   ? 'selected' : '' }}>0% (Non PPN)</option>
                        <option value="11"  {{ old('ppn', $po->ppn) == 11  ? 'selected' : '' }}>11% (PPN)</option>
                        <option value="1.1" {{ old('ppn', $po->ppn) == 1.1 ? 'selected' : '' }}>1.1% (Final)</option>
                    </select>
                </div>

                <div class="field-row">
                    <label>Bulan Lapor</label>
                    <input type="month" name="bulan_lapor" id="field-bulan-lapor"
                           class="form-control {{ !$isDraft ? 'ro' : '' }}"
                           value="{{ old('bulan_lapor', $po->bulan_lapor ?? '') }}"
                           {{ !$isDraft ? 'readonly' : '' }}>
                </div>

                <div class="field-row">
                    <label>Status</label>
                    <input type="text" class="form-control ro"
                           value="{{ ucfirst($po->status) }}" readonly>
                </div>

            @if($isDraft)
            </form>
            @endif

        </div>

        {{-- Riwayat transaksi --}}
        <div class="table-transaksi-wrap">
            <div class="table-transaksi-header">
                <i class="bi bi-table me-1"></i> Daftar Transaksi
            </div>
            <div style="overflow-y:auto; flex:1">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Supplier</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $riwayat = \App\Models\PurchaseOrder::with('supplier')->latest()->limit(20)->get();
                        @endphp
                        @forelse($riwayat as $r)
                        <tr class="{{ $r->id == $po->id ? 'table-primary' : '' }}"
                            style="cursor:pointer"
                            onclick="window.location='{{ route('po.edit', $r->id) }}'">
                            <td>{{ $r->po_number }}</td>
                            <td>{{ $r->supplier->nama_supplier ?? '-' }}</td>
                            <td>
                                @if(($r->jenis_transaksi ?? 'Pembelian') === 'PO')
                                    <span class="nomor-badge po">PO</span>
                                @else
                                    <span class="nomor-badge pr">PR</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-2">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-2 py-1 border-top" style="background:#f8f9fa">
                <a href="{{ route('po.create') }}" class="btn btn-sm btn-outline-secondary w-100"
                   style="font-size:0.75rem;padding:2px">
                    <i class="bi bi-plus"></i> Buat PO Baru
                </a>
            </div>
        </div>

    </div>{{-- end panel-left --}}


    {{-- ===== PANEL KANAN ===== --}}
    <div class="panel-right">

        <div class="preview-area">
            <div class="text-center">
                <i class="bi bi-image fs-1 opacity-25 d-block mb-1"></i>
                <span class="opacity-50">Preview Barang</span>
            </div>
        </div>

        {{-- Form tambah item --}}
        @if($isDraft)
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
                    <input type="text" name="bonus_nama"
                           class="form-control"
                           placeholder="cth: Piring, Gelas..."
                           maxlength="100">
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
                            @if($isDraft)
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
                            @if($isDraft)
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
                            <td colspan="{{ $isDraft ? 9 : 8 }}" class="text-center text-muted py-3">
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
                    <input type="text" class="form-control"
                           value="{{ number_format($totalQty, 0, ',', '.') }}" readonly>
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

        {{-- Action Bar --}}
        <div class="action-bar">
            @if($isDraft)
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
// CONFIG
// ============================================================
const nomorAngka = @json($nomorAngka);
const isDraft    = @json($isDraft);

// ============================================================
// ELEMEN
// ============================================================
const jenisSelect    = document.getElementById('jenis_transaksi');
const poNumberInput  = document.getElementById('po_number');
const nomorBadge     = document.getElementById('nomor-badge');
const jenisBayarSel  = document.getElementById('jenis_pembayaran');
const rowJk          = document.getElementById('row-jk');
const jkWaktuInput   = document.getElementById('jk_waktu');
const tglJatuhTempo  = document.getElementById('tgl_jatuh_tempo');
const tglTransaksi   = document.getElementById('tanggal_transaksi');

// Field yang di-nonaktifkan saat jenis = PO
const fieldsDisabledWhenPO = [
    'field-nomor-faktur',
    'field-tanggal-faktur',
    'jenis_pembayaran',
    'field-ppn',
    'field-bulan-lapor',
];

// ============================================================
// GANTI PREFIX NOMOR
// ============================================================
function updateNomor(jenis) {
    if (!poNumberInput || !isDraft) return;
    const parts = poNumberInput.value.split('-');
    const angka = parts.slice(1).join('-') || nomorAngka;
    const prefix = jenis === 'PO' ? 'PO' : 'PR';
    poNumberInput.value = prefix + '-' + angka;
    if (nomorBadge) {
        nomorBadge.textContent = prefix;
        nomorBadge.className   = 'nomor-badge ' + prefix.toLowerCase();
    }
}

// ============================================================
// TOGGLE FIELDS SAAT JENIS = PO
// ============================================================
function toggleFieldsForJenis(jenis) {
    if (!isDraft) return;
    const isPO = jenis === 'PO';
    fieldsDisabledWhenPO.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.disabled      = isPO;
        el.style.opacity = isPO ? '0.45' : '1';
    });
    // Jika PO, paksa sembunyikan row kredit
    if (isPO && rowJk) {
        rowJk.style.display = 'none';
        setKreditInputsDisabled(true);
    }
}

if (jenisSelect && isDraft) {
    jenisSelect.addEventListener('change', function () {
        updateNomor(this.value);
        toggleFieldsForJenis(this.value);
        // Reset jenis pembayaran ke Cash saat ganti ke PO
        if (this.value === 'PO' && jenisBayarSel) {
            jenisBayarSel.value = 'Cash';
        }
    });
    toggleFieldsForJenis(jenisSelect.value);
}

// ============================================================
// ENABLE / DISABLE INPUT KREDIT
// Penting: input disabled tidak ikut submit form
// ============================================================
function setKreditInputsDisabled(disabled) {
    if (jkWaktuInput)  { jkWaktuInput.disabled  = disabled || !isDraft; }
    if (tglJatuhTempo) { tglJatuhTempo.disabled = disabled; }
}

// ============================================================
// TAMPIL / SEMBUNYIKAN ROW JK WAKTU
// ============================================================
function toggleRowKredit(isKredit) {
    if (!rowJk) return;
    rowJk.style.display = isKredit ? '' : 'none';
    setKreditInputsDisabled(!isKredit);

    if (isKredit && isDraft) {
        // Hitung jatuh tempo jika jk_waktu sudah ada nilainya
        hitungJatuhTempo();
        // Auto-fokus ke jk_waktu
        setTimeout(() => {
            if (jkWaktuInput) { jkWaktuInput.focus(); jkWaktuInput.select(); }
        }, 50);
    }
}

if (jenisBayarSel && isDraft) {
    jenisBayarSel.addEventListener('change', function () {
        toggleRowKredit(this.value === 'Kredit');
    });
}

// ============================================================
// HITUNG JATUH TEMPO otomatis
// Basis: tanggal transaksi + jk_waktu (hari)
// ============================================================
function hitungJatuhTempo() {
    if (!jkWaktuInput || !tglJatuhTempo) return;
    const hari = parseInt(jkWaktuInput.value) || 0;
    const tgl  = tglTransaksi?.value;
    if (!tgl || hari <= 0) return;

    const d = new Date(tgl);
    d.setDate(d.getDate() + hari);
    const yy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    tglJatuhTempo.value = `${yy}-${mm}-${dd}`;
}

// Hitung ulang saat jk_waktu diketik
jkWaktuInput?.addEventListener('input', hitungJatuhTempo);
jkWaktuInput?.addEventListener('change', hitungJatuhTempo);

// Hitung ulang saat tanggal transaksi berubah (jika mode kredit)
tglTransaksi?.addEventListener('change', function () {
    if (jenisBayarSel?.value === 'Kredit') hitungJatuhTempo();
});

// ============================================================
// INISIALISASI saat halaman load
// ============================================================
(function init() {
    const isKredit = jenisBayarSel?.value === 'Kredit';

    // Pastikan row-jk dan disabled state sesuai kondisi awal
    toggleRowKredit(isKredit);

    // Jika kredit dan jatuh tempo belum terisi, hitung otomatis
    if (isKredit && tglJatuhTempo && !tglJatuhTempo.value) {
        hitungJatuhTempo();
    }
})();

// ============================================================
// HITUNG SUBTOTAL real-time
// ============================================================
function hitungSubtotal() {
    const qty   = parseFloat(document.getElementById('input_qty')?.value)   || 0;
    const price = parseFloat(document.getElementById('input_price')?.value) || 0;
    const el    = document.getElementById('preview_subtotal');
    if (el) el.value = (qty * price).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

function onProdukChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const satEl = document.getElementById('preview_satuan');
    const prEl  = document.getElementById('input_price');
    if (satEl) satEl.value = opt.dataset.satuan || '';
    if (prEl)  prEl.value  = opt.dataset.harga  || 0;
    hitungSubtotal();
}
</script>
@endpush
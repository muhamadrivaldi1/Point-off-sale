@extends('layouts.app')
@section('title', 'Edit Pembelian - ' . $po->po_number)

@push('styles')
<style>
* { box-sizing: border-box; }
.po-page { padding: 6px 10px; height: calc(100vh - 60px); display: flex; flex-direction: column; gap: 6px; font-size: 0.80rem; }
.po-titlebar { background: linear-gradient(135deg,#2c5f2e,#4a9c4e); color:#fff; padding:6px 14px; border-radius:4px 4px 0 0; display:flex; align-items:center; gap:10px; font-size:1rem; font-weight:700; flex-shrink:0; }
.po-body { display:grid; grid-template-columns:285px 1fr; gap:8px; flex:1; min-height:0; }

/* PANEL KIRI */
.panel-left { display:flex; flex-direction:column; border:1px solid #adb5bd; border-radius:3px; overflow:hidden; background:#fff; }
.panel-hdr { background:#495057; color:#fff; padding:5px 10px; font-size:0.78rem; font-weight:600; flex-shrink:0; }
.panel-form { padding:8px 10px; overflow-y:auto; flex:1; }
.frow { display:grid; grid-template-columns:112px 1fr; align-items:center; gap:3px; margin-bottom:4px; }
.frow label { font-size:0.75rem; color:#343a40; margin:0; font-weight:500; }
.frow .fc { font-size:0.75rem; padding:2px 6px; height:24px; border-radius:2px; border:1px solid #adb5bd; width:100%; background:#fff; }
.frow .fc:focus { border-color:#0d6efd; outline:none; box-shadow:0 0 0 2px rgba(13,110,253,.15); }
.frow .fc.ro,.frow .fc[readonly] { background:#e9ecef; color:#495057; }
.jk-wrap { display:flex; gap:4px; align-items:center; }
.jk-wrap .fc-num { width:52px; text-align:center; font-size:0.75rem; padding:2px 4px; height:24px; border-radius:2px; border:1px solid #ffc107; background:#fffbeb; }
.jk-wrap span { font-size:0.70rem; color:#6c757d; white-space:nowrap; }
.jk-wrap .fc-date { flex:1; font-size:0.75rem; padding:2px 5px; height:24px; border-radius:2px; border:1px solid #adb5bd; background:#e9ecef; }
.nomor-badge { font-size:0.65rem; padding:1px 5px; border-radius:3px; font-weight:700; margin-left:3px; vertical-align:middle; }
.nomor-badge.pr { background:#d1ecf1; color:#0c5460; }
.nomor-badge.po { background:#fff3cd; color:#856404; }
.panel-riwayat { border-top:1px solid #dee2e6; flex:0 0 165px; display:flex; flex-direction:column; overflow:hidden; }
.panel-riwayat-hdr { background:#6c757d; color:#fff; padding:4px 8px; font-size:0.72rem; font-weight:600; flex-shrink:0; }
.riwayat-scroll { overflow-y:auto; flex:1; }
.riwayat-scroll table { font-size:0.71rem; margin:0; }
.riwayat-scroll table th { background:#e9ecef; padding:3px 6px; font-size:0.68rem; position:sticky; top:0; border-bottom:1px solid #ced4da; }
.riwayat-scroll table td { padding:3px 6px; border-color:#f1f3f5; }
.riwayat-scroll table tr:hover { background:#e8f4fd; cursor:pointer; }
.panel-actions { border-top:1px solid #dee2e6; padding:5px 8px; background:#f8f9fa; display:flex; gap:4px; flex-shrink:0; }
.panel-actions .btn { font-size:0.74rem; padding:3px 10px; border-radius:2px; }

/* PANEL KANAN */
.panel-right { display:flex; flex-direction:column; border:1px solid #adb5bd; border-radius:3px; overflow:hidden; background:#fff; }
.panel-right-hdr { background:#2c5f2e; color:#fff; padding:5px 12px; font-size:0.78rem; font-weight:600; flex-shrink:0; display:flex; justify-content:space-between; align-items:center; }
.items-table-wrap { flex:1; overflow-y:auto; min-height:0; }
.items-table-wrap table { font-size:0.73rem; margin:0; width:100%; border-collapse:collapse; }
.items-table-wrap table thead th { background:#343a40; color:#fff; padding:4px 7px; font-size:0.69rem; font-weight:500; white-space:nowrap; position:sticky; top:0; z-index:1; border-right:1px solid #555; }
.items-table-wrap table tbody td { padding:3px 7px; border-bottom:1px solid #f1f3f5; border-right:1px solid #f1f3f5; vertical-align:middle; }
.items-table-wrap table tbody tr:hover { background:#e8f4fd; }

/* BOTTOM */
.bottom-area { border-top:2px solid #dee2e6; display:grid; grid-template-columns:1fr 255px; flex-shrink:0; }
.item-input-area { padding:8px 10px; border-right:1px solid #dee2e6; background:#f8f9fa; }
.form-section-title { font-size:0.70rem; font-weight:700; color:#495057; text-transform:uppercase; letter-spacing:.3px; padding-bottom:3px; border-bottom:1px solid #dee2e6; margin-bottom:5px; }
.irow { display:grid; grid-template-columns:105px 1fr; align-items:center; gap:3px; margin-bottom:3px; }
.irow label { font-size:0.73rem; color:#495057; margin:0; font-weight:500; }
.irow .ic { font-size:0.75rem; padding:2px 6px; height:24px; border-radius:2px; border:1px solid #adb5bd; width:100%; }
.irow .ic:focus { border-color:#0d6efd; outline:none; }
.irow .ic[readonly] { background:#e9ecef; }
.irow-split { display:grid; grid-template-columns:105px 85px 18px 1fr; align-items:center; gap:3px; margin-bottom:3px; }
.irow-split label { font-size:0.73rem; color:#495057; margin:0; font-weight:500; }
.irow-split .ic { font-size:0.75rem; padding:2px 6px; height:24px; border-radius:2px; border:1px solid #adb5bd; width:100%; }
.irow-split span { font-size:0.70rem; color:#6c757d; text-align:center; }
.btn-tambah { margin-top:6px; width:100%; font-size:0.78rem; padding:5px; border-radius:2px; background:#0d6efd; color:#fff; border:none; cursor:pointer; font-weight:600; }
.btn-tambah:hover { background:#0b5ed7; }
.summary-area { padding:8px 10px; background:#f0f4f8; display:flex; flex-direction:column; gap:3px; }
.sum-hdr { font-size:0.69rem; font-weight:700; color:#495057; text-transform:uppercase; letter-spacing:.3px; padding-bottom:3px; border-bottom:1px solid #ced4da; margin-bottom:2px; }
.srow { display:grid; grid-template-columns:95px 1fr; align-items:center; gap:3px; }
.srow label { font-size:0.73rem; color:#495057; margin:0; font-weight:500; }
.srow .sv { font-size:0.75rem; padding:2px 6px; height:22px; border-radius:2px; border:1px solid #ced4da; background:#e9ecef; color:#495057; text-align:right; font-weight:600; width:100%; }
.srow .sv.highlight { background:#fff3cd; color:#856404; border-color:#ffc107; font-weight:800; font-size:0.82rem; }
.status-pill { font-size:0.68rem; padding:2px 8px; border-radius:10px; font-weight:600; }
</style>
@endpush

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-1 mb-1" style="font-size:0.78rem">
    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show py-1 mb-1" style="font-size:0.78rem">
    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
@endif

@php
    $nomorParts = explode('-', $po->po_number, 2);
    $nomorAngka = $nomorParts[1] ?? now()->format('YmdHis');
    $jenisAwal  = $po->jenis_transaksi ?? 'Pembelian';

    // Bisa diedit selama belum canceled
    $isEditable = in_array($po->status, ['draft', 'approved', 'received']);

    $grandTotal = $po->items->sum(fn($i) => $i->qty * $i->price);
    $ppnRp      = $grandTotal * ($po->ppn ?? 0) / 100;
    $totalAkhir = $grandTotal + $ppnRp;
    $totalQty   = $po->items->sum('qty');
    $totalItems = $po->items->count();
    $totalBonus = $po->items->sum('bonus_qty');
@endphp

<div class="po-page">
    <div class="po-titlebar">
        <i class="bi bi-cart-plus fs-5"></i>
        Edit Transaksi Pembelian
        <span class="ms-auto status-pill
            @if($po->status==='draft')    bg-secondary
            @elseif($po->status==='approved') bg-primary
            @elseif($po->status==='received') bg-success
            @else bg-danger @endif">
            {{ ucfirst($po->status) }}
        </span>
        @if($po->status === 'canceled')
        <span class="badge bg-danger ms-2" style="font-size:0.72rem">
            <i class="bi bi-lock-fill me-1"></i>Tidak Dapat Diedit
        </span>
        @endif
    </div>

    <div class="po-body">

        {{-- ══ PANEL KIRI ══ --}}
        <div class="panel-left">
            <div class="panel-hdr"><i class="bi bi-file-earmark-text me-1"></i>Header Transaksi</div>
            <div class="panel-form">

                @if($isEditable)
                <form method="POST" action="{{ route('po.updateHeader', $po->id) }}" id="form-header">
                @csrf
                @endif

                <div class="frow">
                    <label>Jenis Transaksi</label>
                    @if($isEditable)
                    <select name="jenis_transaksi" id="jenis_transaksi" class="fc">
                        <option value="Pembelian" {{ $jenisAwal==='Pembelian'?'selected':'' }}>PR — Pembelian Reguler</option>
                        <option value="PO"        {{ $jenisAwal==='PO'?'selected':'' }}>PO — Private Order</option>
                    </select>
                    @else
                    <input class="fc ro" readonly value="{{ $jenisAwal==='PO'?'PO — Private Order':'PR — Pembelian Reguler' }}">
                    @endif
                </div>

                <div class="frow">
                    <label>
                        Nomor Data
                        @if($isEditable)
                        <span id="nomor-badge" class="nomor-badge {{ $jenisAwal==='PO'?'po':'pr' }}">
                            {{ $jenisAwal==='PO'?'PO':'PR' }}
                        </span>
                        @endif
                    </label>
                    <input type="text" name="po_number" id="po_number" class="fc ro"
                           value="{{ $po->po_number }}" readonly>
                </div>

                <div class="frow">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" id="tgl_transaksi"
                           class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->tanggal?->format('Y-m-d') ?? date('Y-m-d') }}"
                           {{ !$isEditable?'readonly':'' }}>
                </div>

                <div class="frow">
                    <label>Masuk di</label>
                    @if($isEditable)
                    <select name="gudang" class="fc">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach(\App\Models\Warehouse::orderBy('name')->get() as $wh)
                        <option value="{{ $wh->name }}" {{ ($po->gudang??'')===$wh->name?'selected':'' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @else
                    <input class="fc ro" readonly value="{{ $po->gudang ?? '-' }}">
                    @endif
                </div>

                <div class="frow">
                    <label>Nama Supplier</label>
                    @if($isEditable)
                    <select name="supplier_id" class="fc" required>
                        <option value="">-- Pilih --</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ $po->supplier_id==$sup->id?'selected':'' }}>{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>
                    @else
                    <input class="fc ro" readonly value="{{ $po->supplier->nama_supplier ?? '-' }}">
                    @endif
                </div>

                <div class="frow" id="row-faktur">
                    <label>Faktur</label>
                    <input type="text" name="nomor_faktur"
                           class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->nomor_faktur }}"
                           {{ !$isEditable?'readonly':'' }}
                           placeholder="No. Faktur Supplier">
                </div>

                <div class="frow" id="row-tgl-faktur">
                    <label>Tanggal Faktur</label>
                    <input type="date" name="tanggal_faktur" id="tgl_faktur"
                           class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->tanggal_faktur?->format('Y-m-d') }}"
                           {{ !$isEditable?'readonly':'' }}>
                </div>

                <div class="frow" id="row-jenis-bayar">
                    <label>Jenis Pembayaran</label>
                    @if($isEditable)
                    <select name="jenis_pembayaran" id="jenis_pembayaran" class="fc">
                        <option value="Cash"     {{ ($po->jenis_pembayaran??'Cash')==='Cash'?'selected':'' }}>Cash</option>
                        <option value="Kredit"   {{ ($po->jenis_pembayaran??'')==='Kredit'?'selected':'' }}>Kredit</option>
                        <option value="Transfer" {{ ($po->jenis_pembayaran??'')==='Transfer'?'selected':'' }}>Transfer</option>
                    </select>
                    @else
                    <input class="fc ro" readonly value="{{ $po->jenis_pembayaran ?? '-' }}">
                    @endif
                </div>

                <div class="frow" id="row-jk">
                    <label>Jk. Wkt</label>
                    <div class="jk-wrap">
                        <input type="number" name="jk_waktu" id="jk_waktu" class="fc-num"
                               value="{{ $po->jk_waktu ?? 0 }}" min="0"
                               {{ !$isEditable?'readonly':'' }}>
                        <span>hari</span>
                        <input type="date" name="tanggal_jatuh_tempo" id="tgl_jatuh_tempo"
                               class="fc-date"
                               value="{{ $po->tanggal_jatuh_tempo?->format('Y-m-d') }}" readonly>
                    </div>
                </div>

                <div class="frow" id="row-ppn">
                    <label>Faktur Pajak</label>
                    @if($isEditable)
                    <select name="ppn" class="fc">
                        <option value="0"   {{ ($po->ppn??0)==0  ?'selected':'' }}>0% — Non PPN</option>
                        <option value="11"  {{ ($po->ppn??0)==11 ?'selected':'' }}>11% — PPN</option>
                        <option value="1.1" {{ ($po->ppn??0)==1.1?'selected':'' }}>1.1% — PPN Final</option>
                    </select>
                    @else
                    <input class="fc ro" readonly value="{{ $po->ppn ?? 0 }}%">
                    @endif
                </div>

                <div class="frow" id="row-bulan-lapor">
                    <label>Bulan Lapor</label>
                    <input type="month" name="bulan_lapor"
                           class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->bulan_lapor }}"
                           {{ !$isEditable?'readonly':'' }}>
                </div>

                <div class="frow">
                    <label>Status</label>
                    <input class="fc ro" readonly value="{{ ucfirst($po->status) }}">
                </div>

                @if($isEditable)</form>@endif
            </div>

            {{-- Riwayat --}}
            <div class="panel-riwayat">
                <div class="panel-riwayat-hdr"><i class="bi bi-table me-1"></i>Daftar Transaksi</div>
                <div class="riwayat-scroll">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr><th>Nomor</th><th>Supplier</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @php $riwayat = \App\Models\PurchaseOrder::with('supplier')->latest()->limit(30)->get(); @endphp
                            @forelse($riwayat as $r)
                            <tr class="{{ $r->id==$po->id?'table-primary fw-bold':'' }}"
                                onclick="window.location='{{ route('po.edit', $r->id) }}'">
                                <td>{{ $r->po_number }}</td>
                                <td style="max-width:80px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                                    {{ $r->supplier->nama_supplier ?? '-' }}
                                </td>
                                <td>
                                    @php $bc = match($r->status) { 'approved'=>'primary','received'=>'success','canceled'=>'danger', default=>'secondary' }; @endphp
                                    <span class="badge bg-{{ $bc }}" style="font-size:0.63rem">{{ ucfirst($r->status) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-2">Kosong</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel-actions">
                @if($isEditable)
                <button type="submit" form="form-header" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-save me-1"></i>Simpan
                </button>
                @endif
                <a href="{{ route('po.create') }}" class="btn btn-outline-secondary btn-sm" title="Buat Baru">
                    <i class="bi bi-plus"></i>
                </a>
                <a href="{{ route('po.index') }}" class="btn btn-outline-secondary btn-sm" title="Daftar PO">
                    <i class="bi bi-list"></i>
                </a>
            </div>
        </div>{{-- end panel-left --}}

        {{-- ══ PANEL KANAN ══ --}}
        <div class="panel-right">
            <div class="panel-right-hdr">
                <span>
                    <i class="bi bi-table me-1"></i>
                    {{ $po->po_number }}
                    @if($po->supplier)
                    <span class="fw-normal" style="opacity:.75"> — {{ $po->supplier->nama_supplier }}</span>
                    @endif
                </span>
                <span style="font-size:0.71rem;opacity:.8">
                    {{ $totalItems }} item &nbsp;|&nbsp; Qty: {{ number_format($totalQty,0,',','.') }}
                    @if($totalBonus > 0) &nbsp;|&nbsp; Bonus: {{ number_format($totalBonus,0,',','.') }} @endif
                </span>
            </div>

            {{-- Tabel item --}}
            <div class="items-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th width="28">No</th>
                            <th>Nama Barang</th>
                            <th width="52" style="text-align:center">Stn</th>
                            <th width="55" style="text-align:right">Qty</th>
                            <th width="110" style="text-align:right">Harga Satuan</th>
                            <th width="80" style="text-align:right">Diskon</th>
                            <th width="115" style="text-align:right">Sub Total</th>
                            <th width="90">Bonus</th>
                            <th width="65" style="text-align:right">Jml Bonus</th>
                            @if($isEditable)<th width="32"></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($po->items as $i => $item)
                        @php $sub = $item->qty * ($item->price - ($item->price * ($item->diskon_persen ?? 0) / 100)); @endphp
                        <tr>
                            <td style="text-align:center;color:#999">{{ $i+1 }}</td>
                            <td style="font-weight:600">{{ $item->unit->product->name ?? '-' }}</td>
                            <td style="text-align:center;color:#777">{{ $item->unit->unit_name ?? '-' }}</td>
                            <td style="text-align:right">{{ number_format($item->qty,0,',','.') }}</td>
                            <td style="text-align:right">{{ number_format($item->price,0,',','.') }}</td>
                            <td style="text-align:right;color:#dc3545">
                                @if(($item->diskon_persen ?? 0) > 0)
                                    {{ $item->diskon_persen }}%
                                @else
                                    <span style="color:#bbb">-</span>
                                @endif
                            </td>
                            <td style="text-align:right;font-weight:700;color:#0d6efd">{{ number_format($sub,0,',','.') }}</td>
                            <td>
                                @if(!empty($item->bonus_nama) && ($item->bonus_qty??0)>0)
                                <span style="font-size:0.67rem;background:#d4edda;color:#155724;border-radius:10px;padding:1px 7px;border:1px solid #c3e6cb">
                                    <i class="bi bi-gift"></i> {{ $item->bonus_nama }}
                                </span>
                                @else<span style="color:#bbb">-</span>@endif
                            </td>
                            <td style="text-align:right">
                                @if(($item->bonus_qty??0)>0)
                                <strong style="color:#198754">{{ number_format($item->bonus_qty,0,',','.') }}</strong>
                                @else<span style="color:#bbb">-</span>@endif
                            </td>
                            @if($isEditable)
                            <td style="text-align:center">
                                <form method="POST" action="{{ route('po.deleteItem', $item->id) }}"
                                      onsubmit="return confirm('Hapus item ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link text-danger p-0" style="font-size:0.82rem">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" style="text-align:center;color:#aaa;padding:24px 0">
                                <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:4px;opacity:.4"></i>
                                Belum ada item
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bottom-area">
                {{-- Form tambah item --}}
                <div class="item-input-area">
                    @if($isEditable)
                    <form method="POST" action="{{ route('po.addItem', $po->id) }}" id="form-item">
                    @csrf
                    <div class="form-section-title">
                        <i class="bi bi-plus-circle me-1 text-primary"></i>Tambah Item Barang
                    </div>

                    <div class="irow">
                        <label>Nama Barang</label>
                        <select name="product_unit_id" id="sel_produk" class="ic"
                                onchange="onProdukChange(this)" required>
                            <option value="">-- Cari / Pilih Item --</option>
                            @foreach($units as $unit)
                            <option value="{{ $unit->id }}"
                                    data-satuan="{{ $unit->unit_name ?? 'pcs' }}"
                                    data-harga="{{ $unit->price ?? 0 }}">
                                {{ $unit->product->name ?? '-' }}
                                @if($unit->unit_name)({{ $unit->unit_name }})@endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="irow-split">
                        <label>Jumlah</label>
                        <input type="number" name="qty" id="input_qty" class="ic"
                               value="1" min="1" step="1" oninput="hitungSubtotal()" required>
                        <span></span>
                        <input type="text" id="preview_satuan" name="satuan" class="ic"
                               style="background:#e9ecef" readonly placeholder="Satuan">
                    </div>

                    <div class="irow">
                        <label>Harga Satuan</label>
                        <input type="number" name="price" id="input_price" class="ic"
                               value="0" min="0" step="1" oninput="hitungSubtotal()" required>
                    </div>

                    <div class="irow-split">
                        <label>Diskon Barang</label>
                        <input type="number" name="diskon_persen" id="input_diskon" class="ic"
                               value="0" min="0" max="100" step="0.1" oninput="hitungSubtotal()">
                        <span>%</span>
                        <input type="number" id="preview_diskon_rp" class="ic"
                               style="background:#e9ecef;text-align:right" readonly value="0" placeholder="Rp diskon">
                    </div>

                    <div class="irow">
                        <label>Sub Total</label>
                        <input type="text" id="preview_subtotal" class="ic"
                               style="background:#fff3cd;font-weight:700;text-align:right;border-color:#ffc107"
                               readonly value="0">
                    </div>

                    <div class="irow">
                        <label><i class="bi bi-gift text-success me-1"></i>Bonus</label>
                        <input type="text" name="bonus_nama" class="ic"
                               placeholder="Nama bonus (opsional)" maxlength="100">
                    </div>

                    <div class="irow">
                        <label>Jml Bonus</label>
                        <input type="number" name="bonus_qty" class="ic" value="0" min="0">
                    </div>

                    <button type="submit" class="btn-tambah">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Item ke PO
                    </button>
                    </form>

                    @else
                    <div style="text-align:center;padding:24px 0;color:#aaa">
                        <i class="bi bi-lock-fill" style="font-size:1.8rem;display:block;margin-bottom:6px;opacity:.4"></i>
                        <small>PO sudah dikunci ({{ ucfirst($po->status) }})</small>
                    </div>
                    @endif
                </div>

                {{-- Summary --}}
                <div class="summary-area">
                    <div class="sum-hdr"><i class="bi bi-calculator me-1"></i>Ringkasan</div>
                    <div class="srow">
                        <label>Jumlah Item</label>
                        <input class="sv" readonly value="{{ $totalItems }} item">
                    </div>
                    <div class="srow">
                        <label>Total Qty</label>
                        <input class="sv" readonly value="{{ number_format($totalQty,0,',','.') }}">
                    </div>
                    <div class="srow">
                        <label>Total HNA</label>
                        <input class="sv" readonly value="Rp {{ number_format($grandTotal,0,',','.') }}">
                    </div>
                    <div class="srow">
                        <label>PPN ({{ $po->ppn??0 }}%)</label>
                        <input class="sv" readonly value="Rp {{ number_format($ppnRp,0,',','.') }}">
                    </div>
                    <div class="srow">
                        <label>Jenis Bayar</label>
                        <input class="sv" readonly value="{{ $po->jenis_pembayaran ?? '-' }}">
                    </div>
                    <div class="srow">
                        <label>Jatuh Tempo</label>
                        <input class="sv" readonly value="{{ $po->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}">
                    </div>
                    <div class="srow" style="margin-top:4px">
                        <label><strong>TOTAL</strong></label>
                        <input class="sv highlight" readonly value="Rp {{ number_format($totalAkhir,0,',','.') }}">
                    </div>

                    <div style="margin-top:auto;padding-top:8px;display:flex;flex-direction:column;gap:4px">
                        @if($po->status === 'draft')
                        <form method="POST" action="{{ route('po.approve', $po->id) }}"
                              onsubmit="return confirm('Approve & kunci PO ini?')">
                            @csrf
                            <button class="btn btn-success w-100"
                                    style="font-size:0.75rem;padding:4px 8px;border-radius:2px">
                                <i class="bi bi-check-lg me-1"></i>Approve PO
                            </button>
                        </form>
                        <form method="POST" action="{{ route('po.cancel', $po->id) }}"
                              onsubmit="return confirm('Batalkan PO ini?')">
                            @csrf
                            <button class="btn btn-outline-danger w-100"
                                    style="font-size:0.75rem;padding:4px 8px;border-radius:2px">
                                <i class="bi bi-x-lg me-1"></i>Batalkan
                            </button>
                        </form>

                        @elseif($po->status === 'approved')
                        <form method="POST" action="{{ route('po.receive', $po->id) }}"
                              onsubmit="return confirm('Tandai barang sudah diterima?')">
                            @csrf
                            <button class="btn btn-primary w-100"
                                    style="font-size:0.75rem;padding:4px 8px;border-radius:2px">
                                <i class="bi bi-box-arrow-in-down me-1"></i>Terima Barang
                            </button>
                        </form>
                        <form method="POST" action="{{ route('po.cancel', $po->id) }}"
                              onsubmit="return confirm('Batalkan PO yang sudah diapprove ini?')">
                            @csrf
                            <button class="btn btn-outline-danger w-100"
                                    style="font-size:0.75rem;padding:4px 8px;border-radius:2px">
                                <i class="bi bi-x-lg me-1"></i>Batalkan
                            </button>
                        </form>

                        @elseif($po->status === 'received')
                        <div class="d-flex align-items-center gap-1 p-2"
                             style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:3px;font-size:0.72rem;color:#065f46">
                            <i class="bi bi-check-circle-fill"></i>
                            Barang sudah diterima
                        </div>
                        @endif

                        <a href="{{ route('po.index') }}" class="btn btn-outline-secondary w-100"
                           style="font-size:0.75rem;padding:4px 8px;border-radius:2px">
                            <i class="bi bi-list me-1"></i>Daftar PO
                        </a>
                    </div>
                </div>
            </div>{{-- end bottom-area --}}
        </div>{{-- end panel-right --}}
    </div>{{-- end po-body --}}
</div>{{-- end po-page --}}
@endsection

@push('scripts')
<script>
const nomorAngka = @json($nomorAngka);
const isEditable = @json($isEditable);

// ── PREFIX NOMOR ──
const jenisSelect   = document.getElementById('jenis_transaksi');
const poNumberInput = document.getElementById('po_number');
const nomorBadge    = document.getElementById('nomor-badge');
const rowsPO = ['row-faktur','row-tgl-faktur','row-jenis-bayar','row-jk','row-ppn','row-bulan-lapor'];

function updateNomor(jenis) {
    if (!poNumberInput || !isEditable) return;
    const parts  = poNumberInput.value.split('-');
    const angka  = parts.slice(1).join('-') || nomorAngka;
    const prefix = jenis === 'PO' ? 'PO' : 'PR';
    poNumberInput.value = prefix + '-' + angka;
    if (nomorBadge) {
        nomorBadge.textContent = prefix;
        nomorBadge.className   = 'nomor-badge ' + prefix.toLowerCase();
    }
}

function togglePORows(jenis) {
    if (!isEditable) return;
    const isPO = jenis === 'PO';
    rowsPO.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = isPO ? 'none' : '';
    });
}

jenisSelect?.addEventListener('change', function() {
    updateNomor(this.value);
    togglePORows(this.value);
});

// ── JENIS PEMBAYARAN ──
const jenisBayarSel = document.getElementById('jenis_pembayaran');
const rowJK         = document.getElementById('row-jk');

function syncRowJK() {
    if (!rowJK) return;
    const isKredit = jenisBayarSel ? jenisBayarSel.value === 'Kredit' : false;
    rowJK.style.display = isKredit ? '' : 'none';
}

jenisBayarSel?.addEventListener('change', function() {
    syncRowJK();
    if (this.value === 'Kredit') hitungJatuhTempo();
});

// ── HITUNG JATUH TEMPO ──
// Rumus: Jatuh Tempo = Tanggal Faktur + Jk. Waktu hari
function hitungJatuhTempo() {
    const tglFaktur = document.getElementById('tgl_faktur')?.value;
    const jkHari    = parseInt(document.getElementById('jk_waktu')?.value) || 0;
    const output    = document.getElementById('tgl_jatuh_tempo');
    if (!output || !tglFaktur) { if (output) output.value = ''; return; }
    const d = new Date(tglFaktur);
    d.setDate(d.getDate() + jkHari);
    output.value = d.toISOString().split('T')[0];
}

// Auto-isi Jk. Waktu = 30 hari saat Tanggal Faktur diubah & Kredit
document.getElementById('tgl_faktur')?.addEventListener('change', function() {
    if (!isEditable) return;
    const jkInput = document.getElementById('jk_waktu');
    if (jkInput && parseInt(jkInput.value || '0') <= 0 && jenisBayarSel?.value === 'Kredit') {
        jkInput.value = 30;
    }
    hitungJatuhTempo();
});

document.getElementById('jk_waktu')?.addEventListener('input', hitungJatuhTempo);

// ── HITUNG SUBTOTAL ──
function hitungSubtotal() {
    const qty    = parseFloat(document.getElementById('input_qty')?.value)    || 0;
    const price  = parseFloat(document.getElementById('input_price')?.value)  || 0;
    const diskon = parseFloat(document.getElementById('input_diskon')?.value) || 0;
    const diskonRpTotal = price * (diskon / 100) * qty;
    const subtotal      = (price - price * (diskon / 100)) * qty;
    const fmt = v => v.toLocaleString('id-ID');
    const elDis = document.getElementById('preview_diskon_rp');
    const elSub = document.getElementById('preview_subtotal');
    if (elDis) elDis.value = fmt(diskonRpTotal);
    if (elSub) elSub.value = fmt(subtotal);
}

function onProdukChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const el  = id => document.getElementById(id);
    if (el('preview_satuan')) el('preview_satuan').value = opt.dataset.satuan || '';
    if (el('input_price'))    el('input_price').value    = opt.dataset.harga  || 0;
    hitungSubtotal();
}

// ── INIT ──
document.addEventListener('DOMContentLoaded', function() {
    if (isEditable && jenisSelect) {
        togglePORows(jenisSelect.value);
        updateNomor(jenisSelect.value);
    }
    syncRowJK();
    hitungJatuhTempo();
    hitungSubtotal();
});
</script>
@endpush
@extends('layouts.app')
@section('title', 'Edit Pembelian - ' . $po->po_number)

@push('styles')
<style>
* { box-sizing: border-box; }
.po-page { padding: 6px 10px; height: calc(100vh - 60px); display: flex; flex-direction: column; gap: 6px; font-size: 0.80rem; overflow: hidden; }
.po-titlebar { background: linear-gradient(135deg,#2c5f2e,#4a9c4e); color:#fff; padding:6px 14px; border-radius:4px 4px 0 0; display:flex; align-items:center; gap:10px; font-size:1rem; font-weight:700; flex-shrink:0; }
.po-body { display:grid; grid-template-columns:285px 1fr; gap:8px; flex:1; min-height:0; overflow:hidden; }

/* PANEL KIRI */
.panel-left { display:flex; flex-direction:column; border:1px solid #adb5bd; border-radius:3px; overflow:hidden; background:#fff; }
.panel-hdr { background:#495057; color:#fff; padding:5px 10px; font-size:0.78rem; font-weight:600; flex-shrink:0; }
.panel-form { padding:8px 10px; overflow-y:auto; flex:1; }
.frow { display:grid; grid-template-columns:108px 1fr; align-items:center; gap:3px; margin-bottom:4px; }
.frow label { font-size:0.74rem; color:#343a40; margin:0; font-weight:500; }
.frow .fc { font-size:0.74rem; padding:2px 6px; height:23px; border-radius:2px; border:1px solid #adb5bd; width:100%; background:#fff; }
.frow .fc:focus { border-color:#0d6efd; outline:none; box-shadow:0 0 0 2px rgba(13,110,253,.15); }
.frow .fc.ro,.frow .fc[readonly] { background:#e9ecef; color:#495057; }
.jk-wrap { display:flex; gap:4px; align-items:center; }
.jk-wrap .fc-num { width:50px; text-align:center; font-size:0.74rem; padding:2px 4px; height:23px; border-radius:2px; border:1px solid #ffc107; background:#fffbeb; }
.jk-wrap span { font-size:0.70rem; color:#6c757d; white-space:nowrap; }
.jk-wrap .fc-date { flex:1; font-size:0.74rem; padding:2px 5px; height:23px; border-radius:2px; border:1px solid #adb5bd; background:#e9ecef; }
.nomor-badge { font-size:0.64rem; padding:1px 5px; border-radius:3px; font-weight:700; margin-left:3px; vertical-align:middle; }
.nomor-badge.pr { background:#d1ecf1; color:#0c5460; }
.nomor-badge.po { background:#fff3cd; color:#856404; }
.panel-riwayat { border-top:1px solid #dee2e6; flex:0 0 145px; display:flex; flex-direction:column; overflow:hidden; }
.panel-riwayat-hdr { background:#6c757d; color:#fff; padding:3px 8px; font-size:0.72rem; font-weight:600; flex-shrink:0; }
.riwayat-scroll { overflow-y:auto; flex:1; }
.riwayat-scroll table { font-size:0.70rem; margin:0; }
.riwayat-scroll table th { background:#e9ecef; padding:3px 5px; font-size:0.67rem; position:sticky; top:0; border-bottom:1px solid #ced4da; }
.riwayat-scroll table td { padding:2px 5px; border-color:#f1f3f5; }
.riwayat-scroll table tr:hover { background:#e8f4fd; cursor:pointer; }
.panel-actions { border-top:1px solid #dee2e6; padding:4px 8px; background:#f8f9fa; display:flex; gap:4px; flex-shrink:0; }
.panel-actions .btn { font-size:0.73rem; padding:3px 8px; border-radius:2px; }

/* PANEL KANAN */
.panel-right { display:flex; flex-direction:column; border:1px solid #adb5bd; border-radius:3px; overflow:hidden; background:#fff; min-height:0; }
.panel-right-hdr { background:#2c5f2e; color:#fff; padding:5px 12px; font-size:0.78rem; font-weight:600; flex-shrink:0; display:flex; justify-content:space-between; align-items:center; }

/* TABLE — flex:1 agar ambil semua sisa ruang */
.items-table-wrap { flex:1; overflow-y:auto; min-height:0; }
.items-table-wrap table { font-size:0.72rem; margin:0; width:100%; border-collapse:collapse; }
.items-table-wrap table thead th { background:#343a40; color:#fff; padding:4px 6px; font-size:0.67rem; font-weight:500; white-space:nowrap; position:sticky; top:0; z-index:1; border-right:1px solid #555; }
.items-table-wrap table tbody td { padding:3px 6px; border-bottom:1px solid #f1f3f5; border-right:1px solid #f1f3f5; vertical-align:middle; }
.items-table-wrap table tbody tr:hover { background:#e8f4fd; }

/* BOTTOM — fixed height */
.bottom-area { border-top:2px solid #dee2e6; display:grid; grid-template-columns:1fr 265px; flex-shrink:0; max-height:215px; }
.item-input-area { padding:6px 10px; border-right:1px solid #dee2e6; background:#f8f9fa; overflow-y:auto; }
.form-section-title { font-size:0.69rem; font-weight:700; color:#495057; text-transform:uppercase; letter-spacing:.3px; padding-bottom:2px; border-bottom:1px solid #dee2e6; margin-bottom:5px; }

/* Input field base */
.ic { font-size:0.74rem; padding:2px 6px; height:24px; border-radius:2px; border:1px solid #adb5bd; width:100%; background:#fff; }
.ic:focus { border-color:#0d6efd; outline:none; box-shadow:0 0 0 2px rgba(13,110,253,.12); }
.ic[readonly], .ic.ro { background:#e9ecef; color:#495057; }
.ic.yellow { background:#fffbf0; border-color:#ffc107; font-weight:700; text-align:right; }

/* Label kecil di atas input */
.lbl { display:block; font-size:0.68rem; color:#6c757d; font-weight:600; margin-bottom:1px; text-transform:uppercase; letter-spacing:.2px; }

/* Baris form — pakai flex/grid yang rapi */
.form-row { display:flex; gap:6px; margin-bottom:4px; align-items:flex-end; }
.form-col { display:flex; flex-direction:column; }
.form-col.grow { flex:1; }
.form-col.w-qty  { width:72px; flex-shrink:0; }
.form-col.w-stn  { width:56px; flex-shrink:0; }
.form-col.w-pct  { width:68px; flex-shrink:0; }
.form-col.w-jml  { width:68px; flex-shrink:0; }

.btn-tambah { margin-top:5px; width:100%; font-size:0.78rem; padding:5px; border-radius:2px; background:#0d6efd; color:#fff; border:none; cursor:pointer; font-weight:600; letter-spacing:.2px; }
.btn-tambah:hover { background:#0b5ed7; }

/* SUMMARY */
.summary-area { padding:6px 10px; background:#f0f4f8; display:flex; flex-direction:column; gap:2px; overflow-y:auto; }
.sum-hdr { font-size:0.68rem; font-weight:700; color:#495057; text-transform:uppercase; letter-spacing:.3px; padding-bottom:2px; border-bottom:1px solid #ced4da; margin-bottom:2px; }
.srow { display:grid; grid-template-columns:100px 1fr; align-items:center; gap:3px; }
.srow label { font-size:0.71rem; color:#495057; margin:0; font-weight:500; }
.srow .sv { font-size:0.72rem; padding:1px 5px; height:21px; border-radius:2px; border:1px solid #ced4da; background:#e9ecef; color:#495057; text-align:right; font-weight:600; width:100%; }
.srow .sv.highlight { background:#fff3cd; color:#856404; border-color:#ffc107; font-weight:800; }
.srow .sv.netto { background:#d1fae5; color:#065f46; border-color:#6ee7b7; font-weight:900; font-size:0.78rem; }
.srow-divider { border:none; border-top:1px dashed #ced4da; margin:2px 0; }
.sum-btn-area { display:flex; flex-direction:column; gap:3px; margin-top:4px; }
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
    $nomorParts     = explode('-', $po->po_number, 2);
    $nomorAngka     = $nomorParts[1] ?? now()->format('YmdHis');
    $jenisAwal      = $po->jenis_transaksi ?? 'Pembelian';
    $isEditable     = in_array($po->status, ['draft', 'approved', 'received']);

    $totalHna        = $po->items->sum(fn($i) => (float)$i->qty * (float)$i->price);
    $totalDiskBrg    = $po->items->sum(fn($i) => (float)$i->qty * (float)$i->price * ((float)($i->diskon_persen ?? 0) / 100));
    $totalOngkirItem = $po->items->sum(fn($i) => (float)($i->ongkir ?? 0));
    $subTotal        = $totalHna - $totalDiskBrg + $totalOngkirItem;

    $discNotaPersen  = (float)($po->disc_nota_persen ?? 0);
    $discNotaRupiah  = $discNotaPersen > 0
                         ? round($subTotal * $discNotaPersen / 100, 0)
                         : (float)($po->disc_nota_rupiah ?? 0);
    $afterDisc       = $subTotal - $discNotaRupiah;
    $ppnRp           = round($afterDisc * ((float)($po->ppn ?? 0) / 100), 0);
    $biayaTransport  = (float)($po->biaya_transport ?? 0);
    $totalNetto      = $afterDisc + $ppnRp + $biayaTransport;

    $totalQty   = $po->items->sum('qty');
    $totalItems = $po->items->count();
    $totalBonus = $po->items->sum('bonus_qty');
@endphp

<div class="po-page">
    <div class="po-titlebar">
        <i class="bi bi-cart-plus fs-5"></i>
        Input Transaksi Pembelian Barang
        <span class="ms-auto status-pill
            @if($po->status==='draft') bg-secondary
            @elseif($po->status==='approved') bg-primary
            @elseif($po->status==='received') bg-success
            @else bg-danger @endif">
            {{ ucfirst($po->status) }}
        </span>
        @if($po->status === 'canceled')
        <span class="badge bg-danger ms-2" style="font-size:0.71rem">
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
                    <label>Nomor Data
                        @if($isEditable)
                        <span id="nomor-badge" class="nomor-badge {{ $jenisAwal==='PO'?'po':'pr' }}">{{ $jenisAwal==='PO'?'PO':'PR' }}</span>
                        @endif
                    </label>
                    <input type="text" name="po_number" id="po_number" class="fc ro" value="{{ $po->po_number }}" readonly>
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
                    <input type="text" name="nomor_faktur" class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->nomor_faktur }}" {{ !$isEditable?'readonly':'' }} placeholder="No. Faktur Supplier">
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
                               value="{{ $po->jk_waktu ?? 0 }}" min="0" {{ !$isEditable?'readonly':'' }}>
                        <span>hari</span>
                        <input type="date" name="tanggal_jatuh_tempo" id="tgl_jatuh_tempo"
                               class="fc-date" value="{{ $po->tanggal_jatuh_tempo?->format('Y-m-d') }}" readonly>
                    </div>
                </div>

                <div class="frow" id="row-ppn">
                    <label>Faktur Pajak</label>
                    @if($isEditable)
                    <select name="ppn" id="sel_ppn" class="fc" onchange="hitungSummaryLive()">
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
                    <input type="month" name="bulan_lapor" class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->bulan_lapor }}" {{ !$isEditable?'readonly':'' }}>
                </div>

                <div class="frow">
                    <label>Status</label>
                    <input class="fc ro" readonly value="{{ ucfirst($po->status) }}">
                </div>

                <div style="border-top:1px dashed #dee2e6;margin:5px 0 4px;"></div>
                <div style="font-size:0.68rem;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.3px;margin-bottom:3px;">
                    <i class="bi bi-tag me-1"></i>Diskon & Transport
                </div>

                <div class="frow">
                    <label>Disk Nota (%)</label>
                    <input type="number" name="disc_nota_persen" id="disc_nota_persen"
                           class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->disc_nota_persen ?? 0 }}" min="0" max="100" step="0.1"
                           {{ !$isEditable?'readonly':'' }} oninput="hitungSummaryLive()">
                </div>

                <div class="frow">
                    <label>Disk Nota (Rp)</label>
                    <input type="number" name="disc_nota_rupiah" id="disc_nota_rupiah"
                           class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->disc_nota_rupiah ?? 0 }}" min="0"
                           {{ !$isEditable?'readonly':'' }} oninput="hitungSummaryLive()">
                </div>

                <div class="frow">
                    <label>Biaya Transport</label>
                    <input type="number" name="biaya_transport" id="biaya_transport"
                           class="fc {{ !$isEditable?'ro':'' }}"
                           value="{{ $po->biaya_transport ?? 0 }}" min="0"
                           {{ !$isEditable?'readonly':'' }} oninput="hitungSummaryLive()">
                </div>

                @if($isEditable)</form>@endif
            </div>

            <div class="panel-riwayat">
                <div class="panel-riwayat-hdr"><i class="bi bi-table me-1"></i>Daftar Transaksi</div>
                <div class="riwayat-scroll">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Nomor</th><th>Supplier</th><th>St.</th></tr></thead>
                        <tbody>
                            @php $riwayat = \App\Models\PurchaseOrder::with('supplier')->latest()->limit(30)->get(); @endphp
                            @forelse($riwayat as $r)
                            <tr class="{{ $r->id==$po->id?'table-primary fw-bold':'' }}"
                                onclick="window.location='{{ route('po.edit', $r->id) }}'">
                                <td style="font-size:0.67rem;max-width:90px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">{{ $r->po_number }}</td>
                                <td style="max-width:65px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size:0.67rem;">{{ $r->supplier->nama_supplier ?? '-' }}</td>
                                <td>
                                    @php $bc = match($r->status) { 'approved'=>'primary','received'=>'success','canceled'=>'danger', default=>'secondary' }; @endphp
                                    <span class="badge bg-{{ $bc }}" style="font-size:0.58rem;padding:1px 4px">{{ ucfirst(substr($r->status,0,3)) }}</span>
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
                <a href="{{ route('po.create') }}" class="btn btn-outline-secondary btn-sm" title="Buat Baru"><i class="bi bi-plus"></i></a>
                <a href="{{ route('po.index') }}" class="btn btn-outline-secondary btn-sm" title="Daftar PO"><i class="bi bi-list"></i></a>
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
                <span style="font-size:0.71rem;opacity:.85">
                    {{ $totalItems }} item &nbsp;|&nbsp; Qty: {{ number_format($totalQty,0,',','.') }}
                    @if($totalBonus > 0) &nbsp;|&nbsp; Bonus: {{ number_format($totalBonus,0,',','.') }} @endif
                </span>
            </div>

            <div class="items-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th width="26">No</th>
                            <th>Nama Barang</th>
                            <th width="44" style="text-align:center">Stn</th>
                            <th width="55" style="text-align:right">Qty</th>
                            <th width="100" style="text-align:right">Harga Satuan</th>
                            <th width="64" style="text-align:right">Disk %</th>
                            <th width="100" style="text-align:right">Harga-Disk</th>
                            <th width="75" style="text-align:right">Ongkir</th>
                            <th width="105" style="text-align:right">Sub Total</th>
                            <th width="82">Bonus</th>
                            <th width="55" style="text-align:right">Jml Bon</th>
                            @if($isEditable)<th width="28"></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($po->items as $i => $item)
                        @php
                            $hargaDisk = (float)$item->price * (1 - ((float)($item->diskon_persen ?? 0) / 100));
                            $ongkir    = (float)($item->ongkir ?? 0);
                            $sub       = $hargaDisk * (float)$item->qty + $ongkir;
                        @endphp
                        <tr>
                            <td style="text-align:center;color:#999">{{ $i+1 }}</td>
                            <td style="font-weight:600">{{ $item->unit->product->name ?? '-' }}</td>
                            <td style="text-align:center;color:#777">{{ $item->unit->unit_name ?? '-' }}</td>
                            <td style="text-align:right">{{ number_format((int)$item->qty,0,',','.') }}</td>
                            <td style="text-align:right">{{ number_format($item->price,0,',','.') }}</td>
                            <td style="text-align:right;color:#dc3545">
                                @if(($item->diskon_persen ?? 0) > 0){{ number_format($item->diskon_persen,1) }}%
                                @else<span style="color:#ccc">-</span>@endif
                            </td>
                            <td style="text-align:right;color:#198754">{{ number_format($hargaDisk,0,',','.') }}</td>
                            <td style="text-align:right;color:#6c757d">
                                @if($ongkir > 0){{ number_format($ongkir,0,',','.') }}
                                @else<span style="color:#ccc">-</span>@endif
                            </td>
                            <td style="text-align:right;font-weight:700;color:#0d6efd">{{ number_format($sub,0,',','.') }}</td>
                            <td>
                                @if(!empty($item->bonus_nama) && ($item->bonus_qty??0)>0)
                                <span style="font-size:0.66rem;background:#d4edda;color:#155724;border-radius:10px;padding:1px 6px;border:1px solid #c3e6cb">
                                    <i class="bi bi-gift"></i> {{ $item->bonus_nama }}
                                </span>
                                @else<span style="color:#ccc">-</span>@endif
                            </td>
                            <td style="text-align:right">
                                @if(($item->bonus_qty??0)>0)<strong style="color:#198754">{{ number_format((int)$item->bonus_qty,0,',','.') }}</strong>
                                @else<span style="color:#ccc">-</span>@endif
                            </td>
                            @if($isEditable)
                            <td style="text-align:center;padding:2px 4px">
                                <form method="POST" action="{{ route('po.deleteItem', $item->id) }}" onsubmit="return confirm('Hapus item ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link text-danger p-0" style="font-size:0.80rem"><i class="bi bi-x-circle-fill"></i></button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" style="text-align:center;color:#aaa;padding:18px 0">
                                <i class="bi bi-inbox" style="font-size:1.4rem;display:block;margin-bottom:4px;opacity:.4"></i>Belum ada item
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bottom-area">
                {{-- ══ FORM TAMBAH ITEM ══ --}}
                <div class="item-input-area">
                    @if($isEditable)
                    <form method="POST" action="{{ route('po.addItem', $po->id) }}" id="form-item">
                    @csrf
                    <div class="form-section-title">
                        <i class="bi bi-plus-circle me-1 text-primary"></i>Tambah Item Barang
                    </div>

                    {{-- Baris 1: Nama Barang (full width) --}}
                    <div class="form-row">
                        <div class="form-col grow">
                            <span class="lbl">Nama Barang</span>
                            <select name="product_unit_id" id="sel_produk" class="ic" onchange="onProdukChange(this)" required>
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
                    </div>

                    {{-- Baris 2: Qty | Stn | Harga Satuan | → | Harga-Disk --}}
                    <div class="form-row">
                        <div class="form-col w-qty">
                            <span class="lbl">Jumlah</span>
                            <input type="number" name="qty" id="input_qty" class="ic"
                                   value="1" min="1" step="1" oninput="hitungItemSubtotal()" required>
                        </div>
                        <div class="form-col w-stn">
                            <span class="lbl">Satuan</span>
                            <input type="text" id="preview_satuan" class="ic ro" readonly placeholder="Stn">
                        </div>
                        <div class="form-col grow">
                            <span class="lbl">Harga Satuan</span>
                            <input type="number" name="price" id="input_price" class="ic"
                                   value="0" min="0" step="1" oninput="hitungItemSubtotal()" required placeholder="0">
                        </div>
                        <div class="form-col grow">
                            <span class="lbl">Harga - Disk</span>
                            <input type="number" id="preview_harga_disk" class="ic ro" readonly value="0">
                        </div>
                    </div>

                    {{-- Baris 3: Disk% | Rp Disk | Ongkir | Sub Total --}}
                    <div class="form-row">
                        <div class="form-col w-pct">
                            <span class="lbl">Disk (%)</span>
                            <input type="number" name="diskon_persen" id="input_diskon" class="ic"
                                   value="0" min="0" max="100" step="0.1" oninput="hitungItemSubtotal()" placeholder="0">
                        </div>
                        <div class="form-col grow">
                            <span class="lbl">Rp Diskon</span>
                            <input type="number" id="preview_diskon_rp" class="ic ro" readonly value="0">
                        </div>
                        <div class="form-col grow">
                            <span class="lbl">Ongkir</span>
                            <input type="number" name="ongkir" id="input_ongkir" class="ic"
                                   value="0" min="0" step="1" oninput="hitungItemSubtotal()" placeholder="0">
                        </div>
                        <div class="form-col grow">
                            <span class="lbl">Sub Total</span>
                            <input type="text" id="preview_subtotal" class="ic yellow" readonly value="0">
                        </div>
                    </div>

                    {{-- Baris 4: Bonus nama | Jml Bonus --}}
                    <div class="form-row">
                        <div class="form-col" style="width:68px;flex-shrink:0;">
                            <span class="lbl"><i class="bi bi-gift text-success"></i> Bonus</span>
                            <input type="number" name="bonus_qty" id="input_bonus_qty" class="ic w-jml"
                                   value="0" min="0" placeholder="0">
                        </div>
                        <div class="form-col grow">
                            <span class="lbl">Nama Bonus</span>
                            <input type="text" name="bonus_nama" class="ic" placeholder="Nama bonus (opsional)" maxlength="100">
                        </div>
                    </div>

                    <button type="submit" class="btn-tambah">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Item ke PO
                    </button>
                    </form>

                    @else
                    <div style="text-align:center;padding:28px 0;color:#aaa">
                        <i class="bi bi-lock-fill" style="font-size:1.6rem;display:block;margin-bottom:5px;opacity:.4"></i>
                        <small>PO sudah dikunci ({{ ucfirst($po->status) }})</small>
                    </div>
                    @endif
                </div>

                {{-- ══ SUMMARY ══ --}}
                <div class="summary-area">
                    <div class="sum-hdr"><i class="bi bi-calculator me-1"></i>Ringkasan</div>
                    <div class="srow"><label>Jumlah Item</label><input class="sv" readonly value="{{ $totalItems }} item"></div>
                    <div class="srow"><label>Total HNA</label><input class="sv" readonly value="Rp {{ number_format($totalHna,0,',','.') }}"></div>
                    <div class="srow"><label>Total Disk Brg</label><input class="sv" readonly style="color:#dc3545" value="Rp {{ number_format($totalDiskBrg,0,',','.') }}"></div>
                    <div class="srow"><label>Sub Total</label><input class="sv highlight" id="sv_subtotal" readonly value="Rp {{ number_format($subTotal,0,',','.') }}"></div>
                    <div class="srow"><label>Disk Nota</label><input class="sv" id="sv_disc_nota_rp" readonly style="color:#dc3545" value="Rp {{ number_format($discNotaRupiah,0,',','.') }}"></div>
                    <div class="srow"><label>PPN ({{ $po->ppn ?? 0 }}%)</label><input class="sv" id="sv_ppn_rp" readonly value="Rp {{ number_format($ppnRp,0,',','.') }}"></div>
                    <div class="srow"><label>Transport</label><input class="sv" id="sv_biaya_transport" readonly value="Rp {{ number_format($biayaTransport,0,',','.') }}"></div>
                    <hr class="srow-divider">
                    <div class="srow"><label><strong>Total Netto</strong></label><input class="sv netto" id="sv_total_netto" readonly value="Rp {{ number_format($totalNetto,0,',','.') }}"></div>

                    <div class="sum-btn-area">
                        @if($po->status === 'draft')
                        <form method="POST" action="{{ route('po.approve', $po->id) }}" onsubmit="return confirm('Approve & kunci PO ini?')">
                            @csrf
                            <button class="btn btn-success w-100" style="font-size:0.74rem;padding:4px 8px;border-radius:2px">
                                <i class="bi bi-check-lg me-1"></i>Approve PO
                            </button>
                        </form>
                        <form method="POST" action="{{ route('po.cancel', $po->id) }}" onsubmit="return confirm('Batalkan PO ini?')">
                            @csrf
                            <button class="btn btn-outline-danger w-100" style="font-size:0.74rem;padding:3px 8px;border-radius:2px">
                                <i class="bi bi-x-lg me-1"></i>Batalkan
                            </button>
                        </form>

                        @elseif($po->status === 'approved')
                        <form method="POST" action="{{ route('po.receive', $po->id) }}" onsubmit="return confirm('Tandai barang sudah diterima?')">
                            @csrf
                            <button class="btn btn-primary w-100" style="font-size:0.74rem;padding:4px 8px;border-radius:2px">
                                <i class="bi bi-box-arrow-in-down me-1"></i>Terima Barang
                            </button>
                        </form>
                        <form method="POST" action="{{ route('po.cancel', $po->id) }}" onsubmit="return confirm('Batalkan PO yang sudah diapprove ini?')">
                            @csrf
                            <button class="btn btn-outline-danger w-100" style="font-size:0.74rem;padding:3px 8px;border-radius:2px">
                                <i class="bi bi-x-lg me-1"></i>Batalkan
                            </button>
                        </form>

                        @elseif($po->status === 'received')
                        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:3px;padding:5px 8px;font-size:0.72rem;color:#065f46;text-align:center">
                            <i class="bi bi-check-circle-fill me-1"></i>Barang sudah diterima
                        </div>
                        @endif

                        <a href="{{ route('po.index') }}" class="btn btn-outline-secondary w-100"
                           style="font-size:0.74rem;padding:3px 8px;border-radius:2px;margin-top:2px">
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
const jenisSelect   = document.getElementById('jenis_transaksi');
const poNumberInput = document.getElementById('po_number');
const nomorBadge    = document.getElementById('nomor-badge');
const rowsPO = ['row-faktur','row-tgl-faktur','row-jenis-bayar','row-jk','row-ppn','row-bulan-lapor'];

function updateNomor(jenis) {
    if (!poNumberInput || !isEditable) return;
    const parts = poNumberInput.value.split('-');
    const angka = parts.slice(1).join('-') || nomorAngka;
    const prefix = jenis === 'PO' ? 'PO' : 'PR';
    poNumberInput.value = prefix + '-' + angka;
    if (nomorBadge) { nomorBadge.textContent = prefix; nomorBadge.className = 'nomor-badge ' + prefix.toLowerCase(); }
}
function togglePORows(jenis) {
    if (!isEditable) return;
    const isPO = jenis === 'PO';
    rowsPO.forEach(id => { const el = document.getElementById(id); if (el) el.style.display = isPO ? 'none' : ''; });
}
jenisSelect?.addEventListener('change', function() { updateNomor(this.value); togglePORows(this.value); });

const jenisBayarSel = document.getElementById('jenis_pembayaran');
const rowJK = document.getElementById('row-jk');
function syncRowJK() {
    if (!rowJK) return;
    rowJK.style.display = jenisBayarSel?.value === 'Kredit' ? '' : 'none';
}
jenisBayarSel?.addEventListener('change', function() { syncRowJK(); if (this.value === 'Kredit') hitungJatuhTempo(); });

function hitungJatuhTempo() {
    const tglFaktur = document.getElementById('tgl_faktur')?.value;
    const jkHari = parseInt(document.getElementById('jk_waktu')?.value) || 0;
    const output = document.getElementById('tgl_jatuh_tempo');
    if (!output || !tglFaktur) { if (output) output.value = ''; return; }
    const d = new Date(tglFaktur);
    d.setDate(d.getDate() + jkHari);
    output.value = d.toISOString().split('T')[0];
}
document.getElementById('tgl_faktur')?.addEventListener('change', function() {
    if (!isEditable) return;
    const jkInput = document.getElementById('jk_waktu');
    if (jkInput && parseInt(jkInput.value || '0') <= 0 && jenisBayarSel?.value === 'Kredit') jkInput.value = 30;
    hitungJatuhTempo();
});
document.getElementById('jk_waktu')?.addEventListener('input', hitungJatuhTempo);

function hitungItemSubtotal() {
    const qty    = parseFloat(document.getElementById('input_qty')?.value) || 0;
    const price  = parseFloat(document.getElementById('input_price')?.value) || 0;
    const diskon = parseFloat(document.getElementById('input_diskon')?.value) || 0;
    const ongkir = parseFloat(document.getElementById('input_ongkir')?.value) || 0;
    const hargaDisk   = price * (1 - diskon / 100);
    const diskonRpTot = price * (diskon / 100) * qty;
    const subtotal    = hargaDisk * qty + ongkir;
    const fmt = v => Math.round(v).toLocaleString('id-ID');
    const el = id => document.getElementById(id);
    if (el('preview_harga_disk')) el('preview_harga_disk').value = fmt(hargaDisk);
    if (el('preview_diskon_rp'))  el('preview_diskon_rp').value  = fmt(diskonRpTot);
    if (el('preview_subtotal'))   el('preview_subtotal').value   = fmt(subtotal);
}

const serverHna     = {{ $totalHna }};
const serverDiskBrg = {{ $totalDiskBrg }};
const serverOngkir  = {{ $totalOngkirItem }};

function hitungSummaryLive() {
    if (!isEditable) return;
    const discNotaPersen = parseFloat(document.getElementById('disc_nota_persen')?.value) || 0;
    const discNotaRpInput= parseFloat(document.getElementById('disc_nota_rupiah')?.value) || 0;
    const ppnPersen      = parseFloat(document.getElementById('sel_ppn')?.value) || 0;
    const biayaTransport = parseFloat(document.getElementById('biaya_transport')?.value) || 0;
    const subTotal    = serverHna - serverDiskBrg + serverOngkir;
    const discNotaRp  = discNotaPersen > 0 ? Math.round(subTotal * discNotaPersen / 100) : discNotaRpInput;
    const afterDisc   = subTotal - discNotaRp;
    const ppnRp       = Math.round(afterDisc * ppnPersen / 100);
    const totalNetto  = afterDisc + ppnRp + biayaTransport;
    const fmt = v => 'Rp ' + Math.round(v).toLocaleString('id-ID');
    const el = id => document.getElementById(id);
    if (el('sv_disc_nota_rp'))    el('sv_disc_nota_rp').value    = fmt(discNotaRp);
    if (el('sv_ppn_rp'))          el('sv_ppn_rp').value          = fmt(ppnRp);
    if (el('sv_biaya_transport')) el('sv_biaya_transport').value = fmt(biayaTransport);
    if (el('sv_total_netto'))     el('sv_total_netto').value     = fmt(totalNetto);
    if (discNotaPersen > 0) { const rnEl = document.getElementById('disc_nota_rupiah'); if (rnEl) rnEl.value = Math.round(discNotaRp); }
}

function onProdukChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const el = id => document.getElementById(id);
    if (el('preview_satuan')) el('preview_satuan').value = opt.dataset.satuan || '';
    if (el('input_price'))    el('input_price').value    = opt.dataset.harga  || 0;
    hitungItemSubtotal();
}

document.addEventListener('DOMContentLoaded', function() {
    if (isEditable && jenisSelect) { togglePORows(jenisSelect.value); updateNomor(jenisSelect.value); }
    syncRowJK();
    hitungJatuhTempo();
    hitungItemSubtotal();
});
</script>
@endpush
@extends('layouts.app')
@section('title', 'Neraca')

@section('content')
<style>
.nc-wrap       { max-width: 960px; margin: 24px auto; padding: 0 14px; font-family: Arial, sans-serif; font-size: 13px; }
.nc-card       { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 18px; }
.nc-card-title { font-size: 15px; font-weight: 700; color: #1f2937; margin-bottom: 14px; display: flex; align-items: center; gap: 7px; }

/* Dua kolom neraca */
.neraca-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:680px) { .neraca-grid { grid-template-columns: 1fr; } }

/* Tabel neraca */
.nc-table { width: 100%; border-collapse: collapse; }
.nc-table th { padding: 9px 12px; font-size: 11px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing:.4px; background: #f9fafb; border-bottom: 2px solid #e5e7eb; }
.nc-table td { padding: 9px 12px; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
.nc-table tr:last-child td { border-bottom: none; }
.nc-table .row-section { background: #f3f4f6; font-weight: 700; color: #374151; font-size: 12px; }
.nc-table .row-indent  td:first-child { padding-left: 26px; color: #4b5563; }
.nc-table .row-subtotal { background: #f9fafb; font-weight: 700; }
.nc-table .row-total    { background: #1f2937; color: #fff; }
.nc-table .row-total td { color: #fff; font-weight: 800; font-size: 14px; }

/* KPI */
.kpi-grid3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
@media(max-width:600px){ .kpi-grid3 { grid-template-columns: 1fr; } }
.kpi-box   { border-radius: 10px; padding: 14px 16px; }
.kpi-label { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing:.4px; margin-bottom: 4px; }
.kpi-value { font-size: 20px; font-weight: 800; }
.kpi-sub   { font-size: 11px; color: #9ca3af; margin-top: 3px; }
.kpi-blue   { background: #eff6ff; } .kpi-blue   .kpi-value { color: #1d4ed8; }
.kpi-red    { background: #fef2f2; } .kpi-red    .kpi-value { color: #dc2626; }
.kpi-green  { background: #f0fdf4; } .kpi-green  .kpi-value { color: #15803d; }

/* Balance check */
.balance-check { border-radius: 8px; padding: 12px 16px; font-size: 13px; display:flex; align-items:center; gap:10px; }
.balance-ok     { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
.balance-error  { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

/* Detail stok toggle */
.detail-toggle { cursor: pointer; color: #2563eb; font-size: 12px; text-decoration: underline; margin-top:6px; display:inline-block; }
.detail-table-wrap { display: none; margin-top: 10px; }

/* Filter */
.filter-form { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; margin-bottom:20px; }
.filter-form label { font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px; }
.filter-form input { padding:7px 10px; border:1.5px solid #d1d5db; border-radius:7px; font-size:13px; }
.filter-form button { padding:7px 18px; background:#6d28d9; color:#fff; border:none; border-radius:7px; font-size:13px; font-weight:700; cursor:pointer; }

/* Print */
@media print { .no-print { display:none !important; } }
</style>

<div class="nc-wrap">

    {{-- HEADER --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
        <div>
            <h2 style="margin:0; font-size:20px; color:#111827;">🏦 Neraca Keuangan</h2>
            <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">
                Per tanggal: <strong>{{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d M Y') }}</strong>
            </p>
        </div>
        <div style="display:flex; gap:8px;" class="no-print">
            <a href="{{ route('reports.laba-rugi') }}"
               style="background:#2563eb;color:#fff;padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
               📊 Laba / Rugi
            </a>
            <button onclick="window.print()"
                    style="background:#6b7280;color:#fff;padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;border:none;cursor:pointer;">
                🖨 Print
            </button>
        </div>
    </div>

    {{-- FILTER TANGGAL --}}
    <div class="nc-card no-print" style="padding:14px 20px;">
        <form method="GET" action="{{ route('reports.neraca') }}" class="filter-form">
            <div>
                <label>Per Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}">
            </div>
            <button type="submit">🔍 Tampilkan</button>
        </form>
    </div>

    {{-- KPI RINGKASAN --}}
    <div class="kpi-grid3">
        <div class="kpi-box kpi-blue">
            <div class="kpi-label">🏢 Total Aset</div>
            <div class="kpi-value">Rp {{ number_format($totalAset) }}</div>
            <div class="kpi-sub">Aset lancar + aset tetap</div>
        </div>
        <div class="kpi-box kpi-red">
            <div class="kpi-label">💸 Total Kewajiban</div>
            <div class="kpi-value">Rp {{ number_format($totalKewajiban) }}</div>
            <div class="kpi-sub">Hutang supplier belum lunas</div>
        </div>
        <div class="kpi-box kpi-green">
            <div class="kpi-label">💎 Modal / Ekuitas</div>
            <div class="kpi-value">Rp {{ number_format($modal) }}</div>
            <div class="kpi-sub">Aset − Kewajiban</div>
        </div>
    </div>

    {{-- PERSAMAAN NERACA: ASET = KEWAJIBAN + MODAL --}}
    @php $selisih = abs($totalAset - ($totalKewajiban + $modal)); @endphp
    <div class="balance-check {{ $selisih < 1 ? 'balance-ok' : 'balance-error' }}" style="margin-bottom:18px;">
        @if($selisih < 1)
            ✅ <strong>Neraca Seimbang</strong> — Total Aset = Kewajiban + Modal (Rp {{ number_format($totalAset) }})
        @else
            ⚠️ <strong>Neraca Tidak Seimbang</strong> — Selisih Rp {{ number_format($selisih) }}. Periksa data stok / PO.
        @endif
    </div>

    {{-- DUA KOLOM: KIRI = ASET, KANAN = KEWAJIBAN + MODAL --}}
    <div class="neraca-grid">

        {{-- ===== KOLOM KIRI: ASET ===== --}}
        <div class="nc-card">
            <div class="nc-card-title">🏢 ASET</div>
            <table class="nc-table">
                <thead>
                    <tr><th>Keterangan</th><th style="text-align:right">Jumlah (Rp)</th></tr>
                </thead>
                <tbody>
                    {{-- Aset Lancar --}}
                    <tr class="row-section">
                        <td colspan="2">📦 Aset Lancar</td>
                    </tr>
                    <tr class="row-indent">
                        <td>💵 Kas</td>
                        <td style="text-align:right;">Rp {{ number_format($ringkasan['aset']['lancar']['kas']) }}</td>
                    </tr>
                    <tr class="row-indent">
                        <td>
                            📋 Piutang Dagang
                            @if($ringkasan['aset']['lancar']['piutang'] > 0)
                                <small style="color:#6b7280;">({{ $kreditTrx->count() }} trx kredit aktif)</small>
                            @endif
                        </td>
                        <td style="text-align:right;">Rp {{ number_format($ringkasan['aset']['lancar']['piutang']) }}</td>
                    </tr>
                    <tr class="row-indent">
                        <td>
                            📦 Persediaan Barang
                            <span class="detail-toggle" onclick="toggleDetail()">▼ lihat detail</span>
                        </td>
                        <td style="text-align:right;">Rp {{ number_format($ringkasan['aset']['lancar']['stok']) }}</td>
                    </tr>

                    {{-- Detail stok --}}
                    <tr id="detailStokRow" style="display:none;">
                        <td colspan="2" style="padding:0;">
                            <div style="background:#f9fafb; padding:10px 14px;">
                                @foreach($detailStok as $nama => $d)
                                <div style="display:flex; justify-content:space-between; padding:3px 0; font-size:12px; border-bottom:1px solid #e5e7eb;">
                                    <span style="color:#4b5563; padding-left:10px;">{{ $nama }}
                                        <small style="color:#9ca3af;">({{ number_format($d['qty'], 0) }} unit)</small>
                                    </span>
                                    <span style="color:#374151; font-weight:600;">Rp {{ number_format($d['nilai']) }}</span>
                                </div>
                                @endforeach
                                @if(empty($detailStok))
                                    <div style="text-align:center; color:#9ca3af; padding:8px;">Tidak ada stok</div>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <tr class="row-subtotal">
                        <td>Total Aset Lancar</td>
                        <td style="text-align:right; color:#1d4ed8;">Rp {{ number_format($ringkasan['aset']['lancar']['total']) }}</td>
                    </tr>

                    {{-- Aset Tetap --}}
                    <tr class="row-section">
                        <td colspan="2">🏗 Aset Tetap</td>
                    </tr>
                    <tr class="row-indent">
                        <td>Aset Tetap (peralatan, dll)</td>
                        <td style="text-align:right; color:#9ca3af;">Rp {{ number_format($ringkasan['aset']['tetap']) }}</td>
                    </tr>
                    <tr class="row-subtotal">
                        <td>Total Aset Tetap</td>
                        <td style="text-align:right; color:#1d4ed8;">Rp {{ number_format($ringkasan['aset']['tetap']) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="row-total">
                        <td>TOTAL ASET</td>
                        <td style="text-align:right; font-size:15px;">Rp {{ number_format($totalAset) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ===== KOLOM KANAN: KEWAJIBAN + MODAL ===== --}}
        <div>
            {{-- KEWAJIBAN --}}
            <div class="nc-card" style="margin-bottom:12px;">
                <div class="nc-card-title" style="color:#dc2626;">💸 KEWAJIBAN</div>
                <table class="nc-table">
                    <thead>
                        <tr><th>Keterangan</th><th style="text-align:right">Jumlah (Rp)</th></tr>
                    </thead>
                    <tbody>
                        <tr class="row-section">
                            <td colspan="2">📋 Kewajiban Jangka Pendek</td>
                        </tr>
                        <tr class="row-indent">
                            <td>Hutang Supplier (PO belum lunas)</td>
                            <td style="text-align:right; color:#dc2626;">Rp {{ number_format($ringkasan['kewajiban']['hutang_supplier']) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="row-total">
                            <td>TOTAL KEWAJIBAN</td>
                            <td style="text-align:right;">Rp {{ number_format($totalKewajiban) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- MODAL / EKUITAS --}}
            <div class="nc-card">
                <div class="nc-card-title" style="color:#15803d;">💎 MODAL / EKUITAS</div>
                <table class="nc-table">
                    <thead>
                        <tr><th>Keterangan</th><th style="text-align:right">Jumlah (Rp)</th></tr>
                    </thead>
                    <tbody>
                        <tr class="row-section">
                            <td colspan="2">📈 Ekuitas Pemilik</td>
                        </tr>
                        <tr class="row-indent">
                            <td>Laba Ditahan (akumulasi s/d tgl ini)</td>
                            <td style="text-align:right; color:#15803d;">
                                Rp {{ number_format($ringkasan['modal']['laba_ditahan']) }}
                            </td>
                        </tr>
                        <tr class="row-indent">
                            <td>Modal Lainnya</td>
                            <td style="text-align:right; color:#9ca3af;">—</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="row-total">
                            <td>TOTAL MODAL</td>
                            <td style="text-align:right;">Rp {{ number_format($modal) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- TOTAL KEWAJIBAN + MODAL --}}
            <div style="background:#1f2937; color:#fff; border-radius:8px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                <span style="font-weight:700; font-size:14px;">KEWAJIBAN + MODAL</span>
                <span style="font-weight:800; font-size:16px;">Rp {{ number_format($totalKewajiban + $modal) }}</span>
            </div>
        </div>

    </div>

    {{-- CATATAN --}}
    <div class="nc-card" style="background:#fffbeb; border-color:#fcd34d;">
        <div class="nc-card-title" style="color:#92400e;">📝 Catatan & Asumsi</div>
        <ul style="margin:0; padding-left:18px; line-height:2; font-size:12px; color:#78350f;">
            <li><strong>Kas</strong> dihitung dari: total uang masuk (transaksi paid) + pembayaran kredit − pembelian ke supplier (PO paid)</li>
            <li><strong>Piutang</strong> = sisa hutang pelanggan kredit yang belum dilunasi</li>
            <li><strong>Persediaan</strong> = stok saat ini × harga beli terakhir dari PO</li>
            <li><strong>Hutang Supplier</strong> = total PO yang belum berstatus <em>paid</em></li>
            <li><strong>Laba Ditahan</strong> = akumulasi selisih penjualan − HPP sejak awal s/d tanggal ini</li>
            <li>Aset Tetap (peralatan, dll) belum diinput — bisa ditambahkan dengan tabel <em>assets</em></li>
        </ul>
    </div>

    {{-- INFO PIUTANG KREDIT AKTIF --}}
    @if($kreditTrx->count() > 0)
    <div class="nc-card no-print">
        <div class="nc-card-title">📋 Detail Piutang Kredit Aktif</div>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:8px 12px; text-align:left; background:#f9fafb; font-size:11px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Invoice</th>
                    <th style="padding:8px 12px; text-align:left; background:#f9fafb; font-size:11px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Pelanggan</th>
                    <th style="padding:8px 12px; text-align:right; background:#f9fafb; font-size:11px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Total</th>
                    <th style="padding:8px 12px; text-align:right; background:#f9fafb; font-size:11px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Terbayar</th>
                    <th style="padding:8px 12px; text-align:right; background:#f9fafb; font-size:11px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Sisa</th>
                    <th style="padding:8px 12px; text-align:left; background:#f9fafb; font-size:11px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kreditTrx as $t)
                @php
                    $terbayar = $t->payments->sum('amount');
                    $sisa     = max($t->total - $terbayar, 0);
                @endphp
                <tr>
                    <td style="padding:8px 12px; font-size:12px; border-bottom:1px solid #f3f4f6;">
                        <a href="{{ route('pos.kredit.show', $t->id) }}" style="color:#2563eb; text-decoration:none;">
                            {{ $t->trx_number }}
                        </a>
                    </td>
                    <td style="padding:8px 12px; font-size:12px; border-bottom:1px solid #f3f4f6;">
                        {{ $t->member->name ?? $t->debtor_name ?? '—' }}
                    </td>
                    <td style="padding:8px 12px; font-size:12px; text-align:right; border-bottom:1px solid #f3f4f6;">
                        Rp {{ number_format($t->total) }}
                    </td>
                    <td style="padding:8px 12px; font-size:12px; text-align:right; color:#15803d; border-bottom:1px solid #f3f4f6;">
                        Rp {{ number_format($terbayar) }}
                    </td>
                    <td style="padding:8px 12px; font-size:12px; text-align:right; font-weight:700; color:#dc2626; border-bottom:1px solid #f3f4f6;">
                        Rp {{ number_format($sisa) }}
                    </td>
                    <td style="padding:8px 12px; font-size:12px; color:#6b7280; border-bottom:1px solid #f3f4f6;">
                        {{ $t->created_at->format('d/m/Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;">
                    <td colspan="4" style="padding:10px 12px; text-align:right; font-weight:700;">Total Piutang:</td>
                    <td style="padding:10px 12px; text-align:right; font-weight:800; color:#dc2626;">
                        Rp {{ number_format($ringkasan['aset']['lancar']['piutang']) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

</div>

<script>
function toggleDetail() {
    const row = document.getElementById('detailStokRow');
    const btn = event.target;
    if (row.style.display === 'none' || row.style.display === '') {
        row.style.display = 'table-row';
        btn.textContent   = '▲ sembunyikan';
    } else {
        row.style.display = 'none';
        btn.textContent   = '▼ lihat detail';
    }
}
</script>

@endsection
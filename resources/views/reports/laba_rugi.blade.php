@extends('layouts.app')
@section('title', 'Laporan Laba / Rugi')

@section('content')
<style>
.lr-wrap        { max-width: 1100px; margin: 24px auto; padding: 0 14px; font-family: Arial, sans-serif; font-size: 13px; }
.lr-card        { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 18px; }
.lr-card-title  { font-size: 15px; font-weight: 700; color: #1f2937; margin-bottom: 16px; display: flex; align-items: center; gap: 7px; }

/* KPI boxes */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
@media(max-width:700px){ .kpi-grid { grid-template-columns: 1fr 1fr; } }
.kpi-box  { border-radius: 10px; padding: 14px 16px; }
.kpi-label { font-size: 11px; color: #6b7280; margin-bottom: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.kpi-value { font-size: 20px; font-weight: 800; }
.kpi-sub   { font-size: 11px; color: #9ca3af; margin-top: 3px; }

.kpi-blue   { background: #eff6ff; } .kpi-blue   .kpi-value { color: #1d4ed8; }
.kpi-yellow { background: #fffbeb; } .kpi-yellow .kpi-value { color: #b45309; }
.kpi-green  { background: #f0fdf4; } .kpi-green  .kpi-value { color: #15803d; }
.kpi-red    { background: #fef2f2; } .kpi-red    .kpi-value { color: #dc2626; }
.kpi-purple { background: #f5f3ff; } .kpi-purple .kpi-value { color: #6d28d9; }

/* Main table */
.lr-table { width: 100%; border-collapse: collapse; }
.lr-table th { padding: 10px 14px; text-align: left; background: #f9fafb; font-size: 12px; color: #6b7280; font-weight: 700; border-bottom: 2px solid #e5e7eb; }
.lr-table td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
.lr-table .row-total { background: #1f2937; color: #fff; font-weight: 800; }
.lr-table .row-total td { color: #fff; }
.lr-table .row-section { background: #f9fafb; font-weight: 700; color: #374151; }
.lr-table .row-indent td:first-child { padding-left: 30px; color: #6b7280; }

/* Badge */
.badge-positive { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-negative { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }

/* Chart */
.chart-wrap { position: relative; height: 220px; }

/* Produk table */
.prod-table { width: 100%; border-collapse: collapse; }
.prod-table th { padding: 8px 12px; text-align: left; background: #f9fafb; font-size: 11px; color: #6b7280; font-weight: 700; border-bottom: 1px solid #e5e7eb; }
.prod-table td { padding: 8px 12px; font-size: 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.prod-table tr:last-child td { border-bottom: none; }
.margin-bar-wrap { background: #e5e7eb; border-radius: 10px; height: 6px; width: 80px; display: inline-block; vertical-align: middle; }
.margin-bar      { background: #22c55e; height: 100%; border-radius: 10px; }

/* Filter */
.filter-form { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 20px; }
.filter-form label { font-size: 12px; font-weight: 600; color: #374151; display: block; margin-bottom: 4px; }
.filter-form input { padding: 7px 10px; border: 1.5px solid #d1d5db; border-radius: 7px; font-size: 13px; }
.filter-form button { padding: 7px 18px; background: #2563eb; color: #fff; border: none; border-radius: 7px; font-size: 13px; font-weight: 700; cursor: pointer; }
.filter-form .btn-export { background: #16a34a; }
</style>

<div class="lr-wrap">

    {{-- HEADER --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
        <div>
            <h2 style="margin:0; font-size:20px; color:#111827;">📊 Laporan Laba / Rugi</h2>
            <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">
                Periode: {{ \Carbon\Carbon::parse($from)->locale('id')->translatedFormat('d M Y') }}
                — {{ \Carbon\Carbon::parse($to)->locale('id')->translatedFormat('d M Y') }}
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('reports.neraca') }}"
               style="background:#6d28d9;color:#fff;padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                🏦 Lihat Neraca
            </a>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="lr-card" style="padding:14px 20px;">
        <form method="GET" action="{{ route('reports.laba-rugi') }}" class="filter-form">
            <div>
                <label>Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}">
            </div>
            <div>
                <label>Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}">
            </div>
            <button type="submit">🔍 Tampilkan</button>
            <a href="{{ route('reports.laba-rugi.export', ['from' => $from, 'to' => $to]) }}"
               style="padding:7px 18px; background:#16a34a; color:#fff; border-radius:7px; font-size:13px; font-weight:700; text-decoration:none;">
                📥 Export CSV
            </a>
        </form>
    </div>

    {{-- KPI BOXES --}}
    <div class="kpi-grid">
        <div class="kpi-box kpi-blue">
            <div class="kpi-label">💰 Total Penjualan</div>
            <div class="kpi-value">Rp {{ number_format($totalPenjualan) }}</div>
            <div class="kpi-sub">{{ $jumlahTrx }} transaksi ({{ $jumlahTrxPaid }} paid, {{ $jumlahTrxKredit }} kredit)</div>
        </div>
        <div class="kpi-box kpi-yellow">
            <div class="kpi-label">🏷 Total HPP / Modal</div>
            <div class="kpi-value">Rp {{ number_format($totalModal) }}</div>
            <div class="kpi-sub">Harga beli terakhir dari PO</div>
        </div>
        <div class="kpi-box {{ $labaKotor >= 0 ? 'kpi-green' : 'kpi-red' }}">
            <div class="kpi-label">📈 Laba Kotor</div>
            <div class="kpi-value">Rp {{ number_format($labaKotor) }}</div>
            <div class="kpi-sub">Margin: {{ $marginPersen }}%</div>
        </div>
        <div class="kpi-box {{ $labaBersih >= 0 ? 'kpi-green' : 'kpi-red' }}">
            <div class="kpi-label">✅ Laba Bersih</div>
            <div class="kpi-value">Rp {{ number_format($labaBersih) }}</div>
            <div class="kpi-sub">Setelah biaya operasional</div>
        </div>
    </div>

    {{-- Piutang info --}}
    @if($totalPiutang > 0)
    <div style="background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:10px 16px; margin-bottom:16px; font-size:13px; color:#92400e;">
        ⚠️ Terdapat piutang kredit belum lunas sebesar
        <strong>Rp {{ number_format($totalPiutang) }}</strong>
        di periode ini — belum termasuk dalam kas, sudah termasuk dalam penjualan.
    </div>
    @endif

    {{-- RINGKASAN LABA RUGI --}}
    <div class="lr-card">
        <div class="lr-card-title">📋 Ringkasan Laba / Rugi</div>
        <table class="lr-table">
            <thead>
                <tr>
                    <th style="width:60%">Keterangan</th>
                    <th style="text-align:right">Jumlah (Rp)</th>
                    <th style="text-align:right; width:120px">%</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-section">
                    <td colspan="3">📊 PENDAPATAN</td>
                </tr>
                <tr class="row-indent">
                    <td>Penjualan Bersih (setelah diskon)</td>
                    <td style="text-align:right; font-weight:700; color:#1d4ed8;">Rp {{ number_format($totalPenjualan) }}</td>
                    <td style="text-align:right;">100%</td>
                </tr>

                <tr class="row-section">
                    <td colspan="3">🏷 HARGA POKOK PENJUALAN (HPP)</td>
                </tr>
                <tr class="row-indent">
                    <td>Harga Beli / Modal Barang</td>
                    <td style="text-align:right; font-weight:700; color:#b45309;">Rp {{ number_format($totalModal) }}</td>
                    <td style="text-align:right; color:#b45309;">
                        {{ $totalPenjualan > 0 ? round(($totalModal / $totalPenjualan) * 100, 1) : 0 }}%
                    </td>
                </tr>

                <tr style="background:#eff6ff; font-weight:700;">
                    <td>📈 LABA KOTOR</td>
                    <td style="text-align:right; color:{{ $labaKotor >= 0 ? '#15803d' : '#dc2626' }};">
                        Rp {{ number_format($labaKotor) }}
                        <span class="{{ $labaKotor >= 0 ? 'badge-positive' : 'badge-negative' }}" style="margin-left:6px;">
                            {{ $marginPersen }}%
                        </span>
                    </td>
                    <td style="text-align:right;">{{ $marginPersen }}%</td>
                </tr>

                <tr class="row-section">
                    <td colspan="3">💸 BIAYA OPERASIONAL</td>
                </tr>
                <tr class="row-indent">
                    <td>Total Biaya Operasional</td>
                    <td style="text-align:right; color:#dc2626;">Rp {{ number_format($totalBiayaOperasional) }}</td>
                    <td style="text-align:right;">
                        {{ $totalPenjualan > 0 ? round(($totalBiayaOperasional / $totalPenjualan) * 100, 1) : 0 }}%
                    </td>
                </tr>

                <tr class="row-total">
                    <td>🏆 LABA / RUGI BERSIH</td>
                    <td style="text-align:right; font-size:16px;">
                        Rp {{ number_format($labaBersih) }}
                    </td>
                    <td style="text-align:right;">
                        {{ $totalPenjualan > 0 ? round(($labaBersih / $totalPenjualan) * 100, 1) : 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- GRAFIK PER BULAN --}}
    @if(count($bulanData) > 0)
    <div class="lr-card">
        <div class="lr-card-title">📅 Tren Laba per Bulan</div>
        <div class="chart-wrap">
            <canvas id="chartLabaBulan"></canvas>
        </div>
    </div>
    @endif

    {{-- BREAKDOWN PRODUK --}}
    @if(!empty($penjualanProduk))
    <div class="lr-card">
        <div class="lr-card-title">🛒 Breakdown Produk (urut laba tertinggi)</div>
        <div style="overflow-x:auto;">
            <table class="prod-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th style="text-align:right">Qty</th>
                        <th style="text-align:right">Omzet</th>
                        <th style="text-align:right">HPP</th>
                        <th style="text-align:right">Laba</th>
                        <th style="text-align:center">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penjualanProduk as $i => $p)
                    @php
                        $margin = $p['omzet'] > 0 ? round(($p['laba'] / $p['omzet']) * 100, 1) : 0;
                        $barW   = min(max($margin, 0), 100);
                    @endphp
                    <tr>
                        <td style="color:#9ca3af;">{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $p['name'] }}</td>
                        <td style="text-align:right;">{{ number_format($p['qty'], 0) }}</td>
                        <td style="text-align:right;">Rp {{ number_format($p['omzet']) }}</td>
                        <td style="text-align:right; color:#b45309;">Rp {{ number_format($p['hpp']) }}</td>
                        <td style="text-align:right; font-weight:700; color:{{ $p['laba'] >= 0 ? '#15803d' : '#dc2626' }};">
                            Rp {{ number_format($p['laba']) }}
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex; align-items:center; gap:6px; justify-content:center;">
                                <div class="margin-bar-wrap">
                                    <div class="margin-bar" style="width:{{ $barW }}%; background:{{ $p['laba'] >= 0 ? '#22c55e' : '#ef4444' }};"></div>
                                </div>
                                <span style="font-size:11px; color:#6b7280; min-width:35px;">{{ $margin }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="border-top:2px solid #e5e7eb; background:#f9fafb;">
                    <tr>
                        <td colspan="3" style="padding:10px 12px; font-weight:700; text-align:right;">Total:</td>
                        <td style="padding:10px 12px; text-align:right; font-weight:800; color:#1d4ed8;">
                            Rp {{ number_format($totalPenjualan) }}
                        </td>
                        <td style="padding:10px 12px; text-align:right; font-weight:800; color:#b45309;">
                            Rp {{ number_format($totalModal) }}
                        </td>
                        <td style="padding:10px 12px; text-align:right; font-weight:800; color:{{ $labaKotor >= 0 ? '#15803d' : '#dc2626' }};">
                            Rp {{ number_format($labaKotor) }}
                        </td>
                        <td style="padding:10px 12px; text-align:center; font-weight:700;">
                            {{ $marginPersen }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    @if(count($bulanData) === 0 && empty($penjualanProduk))
    <div class="lr-card" style="text-align:center; padding:40px; color:#9ca3af;">
        📭 Tidak ada data transaksi di periode ini.
    </div>
    @endif

</div>

@if(count($bulanData) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulanData = @json($bulanData);
    const labels    = Object.keys(bulanData).map(b => {
        const [y, m] = b.split('-');
        const names  = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return names[parseInt(m)] + ' ' + y;
    });

    const penjualan = Object.values(bulanData).map(v => v.penjualan);
    const modal     = Object.values(bulanData).map(v => v.modal);
    const laba      = Object.values(bulanData).map(v => v.laba);

    new Chart(document.getElementById('chartLabaBulan'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Penjualan', data: penjualan, backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 4, order: 2 },
                { label: 'HPP / Modal', data: modal, backgroundColor: 'rgba(245,158,11,0.7)', borderRadius: 4, order: 2 },
                { label: 'Laba Kotor', data: laba, type: 'line', borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.1)', borderWidth: 2, pointRadius: 4, fill: true, order: 1 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top' }, tooltip: {
                callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID') }
            }},
            scales: {
                y: { ticks: { callback: v => 'Rp ' + (v/1000).toFixed(0) + 'K' }, grid: { color: '#f3f4f6' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endif

@endsection
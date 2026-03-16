@extends('layouts.app')
@section('title','Dashboard')

@section('content')
<style>
:root {
    --c-jual:    #1565c0;
    --c-beli:    #2e7d32;
    --c-hutang:  #b71c1c;
    --c-piutang: #e65100;
    --c-keluar:  #4a148c;
}

/* ══ WRAPPER ══ */
.dash-wrap {
    display: flex;
    flex-direction: column;
    height: auto; 
    min-height: calc(100vh - 70px);
}

/* ══ KARTU KEUANGAN ══ */
.fcard {
    border: none; border-radius: 12px;
    box-shadow: 0 2px 14px rgba(0,0,0,.09);
    transition: transform .18s, box-shadow .18s;
    overflow: hidden; position: relative;
    flex-shrink: 0;
}
.fcard:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.13); }
.fcard .fc-body  { padding: 12px 14px 8px; color: #fff; }
.fcard .fc-icon  { position:absolute; right:12px; top:10px; font-size:26px; opacity:.20; line-height:1; }
.fcard .fc-label { font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; opacity:.88; }
.fcard .fc-value { font-size:17px; font-weight:900; margin:3px 0 2px; line-height:1.15; }
.fcard .fc-sub   { font-size:10px; opacity:.78; }
.fcard .fc-footer {
    background:rgba(255,255,255,.12); padding:4px 12px;
    font-size:10px; color:rgba(255,255,255,.85);
    display:flex; justify-content:space-between; align-items:center;
    border-top:1px solid rgba(255,255,255,.12);
}
.fcard .fc-footer .ff-val { font-weight:700; }
.fcard-jual    { background: linear-gradient(135deg,#1565c0,#1e88e5); }
.fcard-beli    { background: linear-gradient(135deg,#2e7d32,#43a047); }
.fcard-hutang  { background: linear-gradient(135deg,#b71c1c,#e53935); }
.fcard-piutang { background: linear-gradient(135deg,#e65100,#fb8c00); }
.fcard-keluar  { background: linear-gradient(135deg,#4a148c,#7b1fa2); cursor:pointer; }

/* ══ LABA BANNER ══ */
.laba-banner {
    border-radius:8px; padding:9px 16px;
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; flex-shrink:0;
}
.laba-banner.positif { background:#e8f5e9; border:1.5px solid #a5d6a7; }
.laba-banner.negatif { background:#ffebee; border:1.5px solid #ef9a9a; }
.laba-banner .lb-label { font-size:11px; font-weight:700; color:#555; }
.laba-banner .lb-sub   { font-size:10px; color:#888; margin-top:1px; }
.laba-banner .lb-value { font-size:18px; font-weight:900; white-space:nowrap; }
.laba-banner.positif .lb-value { color:#2e7d32; }
.laba-banner.negatif .lb-value { color:#c62828; }

/* ══ PANEL ══ */
.dash-panel {
    background:#fff; border-radius:10px;
    border:none; box-shadow:0 2px 10px rgba(0,0,0,.07);
    display:flex; flex-direction:column;
    overflow:hidden; margin-bottom: 10px;
}
.dash-panel .dp-head {
    padding:8px 12px; border-bottom:1.5px solid #f0f0f0;
    display:flex; align-items:center; justify-content:space-between;
    font-size:11px; font-weight:800; color:#333;
}
.dash-panel .dp-footer {
    padding:6px 12px; border-top:1px solid #f0f0f0;
    font-size:11px; display:flex; justify-content:space-between;
}

.dp-scroll {
    overflow-y: auto; max-height: 180px;
    padding: 4px 12px;
}
.dp-scroll::-webkit-scrollbar { width:4px; }
.dp-scroll::-webkit-scrollbar-thumb { background:#ddd; border-radius:4px; }

.dp-item {
    display:flex; justify-content:space-between; align-items:center;
    padding:5px 0; border-bottom:1px solid #f5f5f5; font-size:11px;
}
.dp-item:last-child { border-bottom:none; }
.dp-item .di-label { font-weight:600; color:#333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:70%; }

.exp-row {
    display:flex; align-items:center;
    padding:5px 0; border-bottom:1px solid #f5f5f5; font-size:11px; gap:6px;
}
.exp-row .er-name { font-weight:600; color:#333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; }
.exp-row .er-amt  { font-weight:800; color:#6a1b9a; white-space:nowrap; }

.mini-tbl { width:100%; border-collapse:collapse; font-size:11px; }
.mini-tbl th { padding:6px 8px; background:#f8f9fa; color:#666; font-size:9px; text-transform:uppercase; }
.mini-tbl td { padding:6px 8px; border-bottom:1px solid #f5f5f5; }

.qbtn {
    display:inline-flex; align-items:center; gap:4px;
    padding:6px 12px; border-radius:6px; font-size:11px; font-weight:700;
    text-decoration:none; transition:all .15s;
}

.bottom-row { display:flex; gap:12px; margin-top:5px; }
.bottom-col-left, .bottom-col-mid { flex: 0 0 280px; }
.bottom-col-right { flex: 1; }

@media (max-width: 992px) {
    .bottom-row { flex-direction:column; }
    .bottom-col-left, .bottom-col-mid, .bottom-col-right { flex:none; }
}
</style>

<div class="dash-wrap">

    {{-- 1. LABA BANNER --}}
    <div class="laba-banner {{ $labaKotorEstimasi >= 0 ? 'positif' : 'negatif' }} mb-2">
        <div>
            <div class="lb-label">
                {{ $labaKotorEstimasi >= 0 ? '📈' : '📉' }}
                Estimasi Laba Kotor Bulan Ini
            </div>
            <div class="lb-sub">
                Sales <strong>Rp {{ number_format($monthSales) }}</strong>
                &nbsp;−&nbsp;
                Expense <strong>Rp {{ number_format($monthExpense) }}</strong>
                &nbsp;·&nbsp; {{ now()->translatedFormat('F Y') }}
            </div>
        </div>
        <div class="lb-value">
            {{ $labaKotorEstimasi >= 0 ? '+' : '-' }}Rp {{ number_format(abs($labaKotorEstimasi)) }}
        </div>
    </div>

    {{-- 2. KARTU STATISTIK --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg">
            <div class="fcard fcard-jual">
                <div class="fc-body">
                    <span class="fc-icon">🛒</span>
                    <div class="fc-label">Penjualan</div>
                    <div class="fc-value">Rp {{ number_format($todaySales) }}</div>
                    <div class="fc-sub">Hari ini · {{ $todayTransactions }} trx</div>
                </div>
                <div class="fc-footer">
                    <span>Bulan ini</span>
                    <span class="ff-val">Rp {{ number_format($monthSales) }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="fcard fcard-beli">
                <div class="fc-body">
                    <span class="fc-icon">📦</span>
                    <div class="fc-label">Pembelian</div>
                    <div class="fc-value">Rp {{ number_format($todayPembelian) }}</div>
                    <div class="fc-sub">Hari ini</div>
                </div>
                <div class="fc-footer">
                    <span>Bulan ini</span>
                    <span class="ff-val">Rp {{ number_format($monthPembelian) }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="fcard fcard-hutang">
                <div class="fc-body">
                    <span class="fc-icon">🏦</span>
                    <div class="fc-label">Hutang Supplier</div>
                    <div class="fc-value">Rp {{ number_format($hutangSupplier) }}</div>
                    <div class="fc-sub">{{ $hutangSupplierCount }} Nota Belum Lunas</div>
                </div>
                <div class="fc-footer">
                    <span>Tunggakan/Jatuh Tempo</span>
                    <span class="ff-val text-warning">{{ $hutangJatuhTempo }} PO</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="fcard fcard-piutang">
                <div class="fc-body">
                    <span class="fc-icon">💳</span>
                    <div class="fc-label">Piutang Pelanggan</div>
                    <div class="fc-value">Rp {{ number_format($totalPiutang) }}</div>
                    <div class="fc-sub">{{ $piutangCount }} Pelanggan Aktif</div>
                </div>
                <div class="fc-footer">
                    <span>Kredit Bulan Ini</span>
                    <span class="ff-val">Rp {{ number_format($piutangBulanIni) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg">
            <div class="fcard fcard-keluar" onclick="openExpenseModal()">
                <div class="fc-body">
                    <span class="fc-icon">💸</span>
                    <div class="fc-label">Pengeluaran Lain</div>
                    <div class="fc-value">Rp {{ number_format($todayExpense) }}</div>
                    <div class="fc-sub">Hari ini · <span style="text-decoration:underline;">+ Tambah</span></div>
                </div>
                <div class="fc-footer">
                    <span>Total Bulan Ini</span>
                    <span class="ff-val">Rp {{ number_format($monthExpense) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. BOTTOM PANEL --}}
    <div class="bottom-row">
        {{-- KIRI: PRODUK & STOK --}}
        <div class="bottom-col-left">
            <div class="dash-panel">
                <div class="dp-head">🔥 Produk Terlaris (Hari Ini)</div>
                <div class="dp-scroll">
                    @forelse($bestProducts as $item)
                    <div class="dp-item">
                        <span class="di-label">{{ $item->unit->product->name ?? 'Produk Terhapus' }}</span>
                        <span class="badge bg-primary">{{ $item->total_qty }} pcs</span>
                    </div>
                    @empty
                    <p class="text-center text-muted py-3 m-0" style="font-size:11px;">Belum ada penjualan hari ini</p>
                    @endforelse
                </div>
            </div>
            <div class="dash-panel">
                <div class="dp-head">⚠️ Stok Menipis (Sisa <= 5)</div>
                <div class="dp-scroll">
                    @forelse($lowStockProducts as $unit)
                    <div class="dp-item">
                        <span class="di-label">{{ $unit->product->name }}</span>
                        <span class="badge bg-danger">{{ $unit->stock->sum('qty') }}</span>
                    </div>
                    @empty
                    <p class="text-center text-success py-3 m-0" style="font-size:11px;">Stok aman semua</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- TENGAH: PENGELUARAN --}}
        <div class="bottom-col-mid">
            <div class="dash-panel">
                <div class="dp-head">💸 Pengeluaran Terbaru</div>
                <div class="dp-scroll">
                    @forelse($recentExpenses as $exp)
                    <div class="exp-row">
                        <div style="flex:1; min-width:0;">
                            <div class="er-name">{{ $exp->name }}</div>
                            <div style="font-size:9px; color:#aaa;">{{ \Carbon\Carbon::parse($exp->date)->format('d/m H:i') }}</div>
                        </div>
                        <span class="er-amt">Rp {{ number_format($exp->amount) }}</span>
                    </div>
                    @empty
                    <p class="text-center text-muted py-3 m-0" style="font-size:11px;">Belum ada pengeluaran</p>
                    @endforelse
                </div>
                <div class="dp-footer">
                    <span style="color:#888;">Bulan Ini:</span>
                    <strong style="color:var(--c-keluar);">Rp {{ number_format($monthExpense) }}</strong>
                </div>
            </div>
        </div>

        {{-- KANAN: AKSI & TRANSAKSI --}}
        <div class="bottom-col-right">
            <div class="dash-panel">
                <div class="dp-head">⚡ Akses Cepat</div>
                <div style="padding:10px; display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="{{ route('pos') }}" class="qbtn btn btn-primary">🛒 Kasir (POS)</a>
                    <a href="{{ route('transactions.index') }}" class="qbtn btn btn-outline-info">📄 Histori Trx</a>
                    @if(auth()->user()->role === 'owner')
                    <a href="{{ route('products.index') }}" class="qbtn btn btn-outline-success">📦 Stok Barang</a>
                    <a href="{{ route('reports.sales') }}" class="qbtn btn btn-dark">📊 Laporan</a>
                    @endif
                </div>
            </div>

            <div class="dash-panel">
                <div class="dp-head">🕐 Penjualan Hari Ini</div>
                <div class="dp-scroll" style="padding:0;">
                    <table class="mini-tbl">
                        <thead class="sticky-top">
                            <tr>
                                <th>Invoice</th>
                                <th>Jam</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $t)
                            <tr>
                                <td style="font-weight:600;">{{ $t->trx_number }}</td>
                                <td class="text-muted">{{ $t->created_at->format('H:i') }}</td>
                                <td style="font-weight:700;">Rp {{ number_format($t->total) }}</td>
                                <td>
                                    <span class="badge bg-{{ $t->status == 'paid' ? 'success' : 'warning' }}" style="font-size:9px;">
                                        {{ strtoupper($t->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi hari ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PENGELUARAN --}}
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title m-0">💸 Tambah Pengeluaran</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Keterangan</label>
                    <input type="text" id="expName" class="form-control" placeholder="Contoh: Bayar Listrik / Gaji">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label fw-bold small">Nominal (Rp)</label>
                        <input type="number" id="expAmount" class="form-control" placeholder="0">
                    </div>
                    <div class="col-5">
                        <label class="form-label fw-bold small">Tanggal</label>
                        <input type="date" id="expDate" value="{{ today()->toDateString() }}" class="form-control">
                    </div>
                </div>
                <button type="button" onclick="submitExpense()" id="expSubmitBtn" class="btn btn-primary w-100 fw-bold">SIMPAN PENGELUARAN</button>
            </div>
        </div>
    </div>
</div>

<script>
const csrf = '{{ csrf_token() }}';

// 1. Auto Refresh Halaman setiap 5 Menit
setTimeout(function(){
   location.reload();
}, 300000);

function openExpenseModal() {
    new bootstrap.Modal(document.getElementById('expenseModal')).show();
}

async function submitExpense() {
    const name = document.getElementById('expName').value;
    const amount = document.getElementById('expAmount').value;
    const date = document.getElementById('expDate').value;
    const btn = document.getElementById('expSubmitBtn');

    if(!name || !amount) {
        alert('Mohon isi Nama dan Jumlah Pengeluaran');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';

    try {
        const res = await fetch('{{ route("expenses.store") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf },
            body: JSON.stringify({ 
                description: name, 
                total: amount, 
                created_at: date, 
                type: 'expense' 
            })
        });
        const data = await res.json();
        if(data.success) {
            location.reload();
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
        }
    } catch (e) { 
        alert('Gagal terhubung ke server'); 
    }
    btn.disabled = false;
    btn.innerHTML = 'SIMPAN PENGELUARAN';
}
</script>
@endsection
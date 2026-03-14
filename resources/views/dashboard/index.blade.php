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

/* ══ WRAPPER — paksa konten tidak overflow halaman ══ */
.dash-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 70px); 
    overflow: hidden;
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

.jtbadge {
    background:rgba(255,255,255,.22); color:#fff;
    border-radius:20px; padding:1px 6px; font-size:9px; font-weight:700;
}

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

/* ══ PANEL (baris bawah) ══ */
.dash-panel {
    background:#fff; border-radius:10px;
    border:none; box-shadow:0 2px 10px rgba(0,0,0,.07);
    display:flex; flex-direction:column;
    overflow:hidden;
}
.dash-panel .dp-head {
    padding:8px 12px; border-bottom:1.5px solid #f0f0f0;
    display:flex; align-items:center; justify-content:space-between;
    font-size:11px; font-weight:800; color:#333;
    flex-shrink:0;
}

/* ══ SCROLLABLE BODY ══ */
.dp-scroll {
    overflow-y: auto;
    max-height: 165px;
    padding: 4px 12px;
    flex: 1;
}
.dp-scroll::-webkit-scrollbar { width:4px; }
.dp-scroll::-webkit-scrollbar-thumb { background:#ddd; border-radius:4px; }

/* Item dalam scroll */
.dp-item {
    display:flex; justify-content:space-between; align-items:center;
    padding:5px 0; border-bottom:1px solid #f5f5f5; font-size:11px;
}
.dp-item:last-child { border-bottom:none; }
.dp-item .di-label { font-weight:600; color:#333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:60%; }

/* ══ EXPENSE ROW ══ */
.exp-row {
    display:flex; align-items:center;
    padding:5px 0; border-bottom:1px solid #f5f5f5; font-size:11px; gap:6px;
}
.exp-row:last-child { border-bottom:none; }
.exp-row .er-name { font-weight:600; color:#333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; min-width:0; }
.exp-row .er-date { font-size:9px; color:#aaa; margin-top:1px; }
.exp-row .er-amt  { font-weight:800; color:#6a1b9a; white-space:nowrap; font-size:11px; }
.exp-row .er-del  {
    background:none; border:none; color:#ddd; cursor:pointer;
    padding:1px 4px; border-radius:3px; transition:all .15s;
    font-size:12px; line-height:1; flex-shrink:0;
}
.exp-row .er-del:hover { color:#e53935; background:#ffebee; }

.mini-tbl { width:100%; border-collapse:collapse; font-size:11px; }
.mini-tbl th { padding:5px 8px; background:#f8f9fa; color:#666; text-transform:uppercase; font-size:9px; position:sticky; top:0; }
.mini-tbl td { padding:4px 8px; vertical-align:middle; border-bottom:1px solid #f5f5f5; }

.qbtn {
    display:inline-flex; align-items:center; gap:4px;
    padding:5px 10px; border-radius:6px; font-size:11px; font-weight:700;
    text-decoration:none; border:none; cursor:pointer;
    transition:all .15s; white-space:nowrap;
}
.qbtn:hover { transform:translateY(-1px); box-shadow:0 3px 8px rgba(0,0,0,.15); }

/* ══ MODAL UI ══ */
.exp-field label { font-size:11px; font-weight:700; color:#555; margin-bottom:4px; display:block; text-transform:uppercase; }
.exp-field input {
    width:100%; padding:7px 11px; font-size:13px;
    border:1.5px solid #e0e0e0; border-radius:8px; outline:none;
}
.exp-total-today {
    background:#f3e5f5; border:1.5px solid #ce93d8; border-radius:8px;
    padding:7px 12px; margin-bottom:12px;
    display:flex; justify-content:space-between; align-items:center; font-size:12px;
}
.qnominal-chip {
    background:#f3e5f5; border:1.5px solid #ce93d8; border-radius:20px;
    padding:2px 8px; font-size:10px; font-weight:700; color:#6a1b9a;
    cursor:pointer; transition:all .12s; display:inline-block;
}

.bottom-row { display:flex; gap:12px; flex:1; min-height:0; margin-top:10px; }
.bottom-col { display:flex; flex-direction:column; gap:8px; min-height:0; overflow:hidden; }
.bottom-col-left  { flex:0 0 280px; }
.bottom-col-mid   { flex:0 0 280px; }
.bottom-col-right { flex:1; min-width:0; }

@media (max-width: 992px) {
    .dash-wrap { height:auto; overflow:visible; }
    .bottom-row { flex-direction:column; }
    .bottom-col-left, .bottom-col-mid, .bottom-col-right { flex:none; }
}
</style>

<div class="dash-wrap">

    {{-- LABA BANNER --}}
    <div class="laba-banner {{ $labaKotorEstimasi >= 0 ? 'positif' : 'negatif' }} mb-2">
        <div>
            <div class="lb-label">
                {{ $labaKotorEstimasi >= 0 ? '📈' : '📉' }}
                Estimasi Laba Bersih Bulan Ini
            </div>
            <div class="lb-sub">
                Penjualan <strong>Rp {{ number_format($monthSales) }}</strong>
                &nbsp;−&nbsp;
                Pengeluaran <strong>Rp {{ number_format($monthExpense) }}</strong>
                &nbsp;·&nbsp; {{ now()->translatedFormat('F Y') }}
            </div>
        </div>
        <div class="lb-value">
            {{ $labaKotorEstimasi >= 0 ? '+' : '−' }}Rp {{ number_format(abs($labaKotorEstimasi)) }}
        </div>
    </div>

    {{-- 5 KARTU KEUANGAN --}}
    <div class="row g-2 mb-2 flex-shrink-0">
        {{-- JUAL --}}
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
        {{-- BELI --}}
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
        {{-- HUTANG --}}
        <div class="col-6 col-lg">
            <div class="fcard fcard-hutang">
                <div class="fc-body">
                    <span class="fc-icon">🏦</span>
                    <div class="fc-label">Hutang Supplier</div>
                    <div class="fc-value">Rp {{ number_format($hutangSupplier) }}</div>
                    <div class="fc-sub">{{ $hutangSupplierCount }} PO belum lunas</div>
                </div>
                <div class="fc-footer">
                    <span>Jatuh Tempo</span>
                    <span class="ff-val">{{ $hutangJatuhTempo }} PO</span>
                </div>
            </div>
        </div>
        {{-- PIUTANG --}}
        <div class="col-6 col-lg">
            <div class="fcard fcard-piutang">
                <div class="fc-body">
                    <span class="fc-icon">💳</span>
                    <div class="fc-label">Piutang Pelanggan</div>
                    <div class="fc-value">Rp {{ number_format($totalPiutang) }}</div>
                    <div class="fc-sub">{{ $piutangCount }} trx aktif</div>
                </div>
                <div class="fc-footer">
                    <span>Kredit Bulan Ini</span>
                    <span class="ff-val">Rp {{ number_format($piutangBulanIni) }}</span>
                </div>
            </div>
        </div>
        {{-- KELUAR --}}
        <div class="col-6 col-lg">
            <div class="fcard fcard-keluar" onclick="openExpenseModal()">
                <div class="fc-body">
                    <span class="fc-icon">💸</span>
                    <div class="fc-label">Pengeluaran Lain</div>
                    <div class="fc-value">Rp {{ number_format($todayExpense) }}</div>
                    <div class="fc-sub">Hari ini · <span style="opacity:.85;">+ Tambah</span></div>
                </div>
                <div class="fc-footer">
                    <span>Bulan ini</span>
                    <span class="ff-val">Rp {{ number_format($monthExpense) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS BAWAH --}}
    <div class="bottom-row">
        {{-- KIRI: PRODUK & STOK --}}
        <div class="bottom-col bottom-col-left">
            <div class="dash-panel">
                <div class="dp-head">📦 Produk Terlaris Hari Ini</div>
                <div class="dp-scroll">
                    @forelse($bestProducts as $item)
                    <div class="dp-item">
                        <span class="di-label">{{ $item->unit->product->name ?? '-' }}</span>
                        <span class="badge bg-primary" style="font-size:9px;">{{ $item->total_qty }} pcs</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Kosong</p>
                    @endforelse
                </div>
            </div>

            <div class="dash-panel">
                <div class="dp-head">⚠️ Stok Menipis</div>
                <div class="dp-scroll">
                    @forelse($lowStockProducts as $unit)
                    <div class="dp-item">
                        <span class="di-label">{{ $unit->product->name }}</span>
                        <span class="badge bg-danger" style="font-size:9px;">{{ $unit->stock->first()->qty ?? 0 }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Semua aman</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- TENGAH: PENGELUARAN --}}
        <div class="bottom-col bottom-col-mid">
            <div class="dash-panel" style="flex:1;">
                <div class="dp-head">
                    💸 Pengeluaran Terbaru
                    {{-- <button onclick="openExpenseModal()" class="btn btn-sm btn-dark" style="font-size:9px;">+ Tambah</button> --}}
                </div>
                <div class="dp-scroll" id="expenseListDash">
                    @forelse($recentExpenses as $exp)
                    <div class="exp-row" id="exp-row-{{ $exp->id }}">
                        <div style="min-width:0; flex:1;">
                            <div class="er-name">{{ $exp->name }}</div>
                            <div class="er-date">{{ \Carbon\Carbon::parse($exp->date)->format('d/m/Y') }}</div>
                        </div>
                        <span class="er-amt">Rp {{ number_format($exp->amount) }}</span>
                        @if(auth()->user()->role === 'owner')
                        {{-- <button class="er-del" onclick="deleteExpense({{ $exp->id }})">🗑</button> --}}
                        @endif
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada pengeluaran</p>
                    @endforelse
                </div>
                <div class="dp-footer">
                    <span style="font-weight:600;">Total Bulan Ini</span>
                    <span style="font-weight:900; color:#4a148c;">Rp {{ number_format($monthExpense) }}</span>
                </div>
            </div>
        </div>

        {{-- KANAN: AKSI & TRANSAKSI --}}
        <div class="bottom-col bottom-col-right">
            <div class="dash-panel panel-aksi">
                <div class="dp-head">⚡ Aksi Cepat</div>
                <div style="padding:8px 12px; display:flex; flex-wrap:wrap; gap:5px;">
                    <a href="{{ route('pos') }}" class="qbtn btn btn-primary">🛒 POS</a>
                    <a href="{{ route('transactions.index') }}" class="qbtn btn btn-info text-white">📄 Transaksi</a>
                    @if(auth()->user()->role === 'owner')
                    <a href="{{ route('products.index') }}" class="qbtn btn btn-success">📦 Produk</a>
                    <a href="{{ route('reports.sales') }}" class="qbtn btn btn-dark">📊 Laporan</a>
                    @endif
                </div>
            </div>

            <div class="dash-panel" style="flex:1;">
                <div class="dp-head">🕐 Transaksi Terbaru</div>
                <div class="dp-scroll" style="padding:0;">
                    <table class="mini-tbl">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $t)
                            <tr>
                                <td>{{ $t->trx_number }}</td>
                                <td style="font-weight:700;">Rp {{ number_format($t->total) }}</td>
                                <td><span class="badge bg-{{ $t->status == 'paid' ? 'success' : 'warning' }}">{{ $t->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title">💸 Tambah Pengeluaran</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="exp-total-today">
                    <span>Total Hari Ini</span>
                    <strong id="expTodayTotal">Rp {{ number_format($todayExpense) }}</strong>
                </div>

                <div class="exp-field mb-3">
                    <label>📝 Nama Pengeluaran</label>
                    <input type="text" id="expName" placeholder="Contoh: Bayar Listrik" class="form-control">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="exp-field">
                            <label>💰 Jumlah (Rp)</label>
                            <input type="number" id="expAmount" class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="exp-field">
                            <label>📅 Tanggal</label>
                            <input type="date" id="expDate" value="{{ today()->toDateString() }}" class="form-control">
                        </div>
                    </div>
                </div>

                <button type="button" onclick="submitExpense()" id="expSubmitBtn" class="btn btn-primary w-100">💾 Simpan</button>

                <div class="mt-3">
                    <small class="text-muted fw-bold">PENGELUARAN HARI INI</small>
                    <div id="expModalList" class="mt-2" style="max-height:150px; overflow-y:auto;">
                        @php 
                            // Ambil dari tabel transactions dengan type expense
                            $todayExps = \App\Models\Transaction::where('type','expense')->whereDate('created_at', today())->latest()->get(); 
                        @endphp
                        @foreach($todayExps as $te)
                        <div class="exp-row" id="exp-modal-row-{{ $te->id }}">
                            <div class="er-name" style="flex:1">{{ $te->description ?? $te->trx_number }}</div>
                            <span class="er-amt">Rp {{ number_format($te->total) }}</span>
                            <button class="er-del" onclick="deleteExpense({{ $te->id }})">🗑</button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const csrf = '{{ csrf_token() }}';

function openExpenseModal() {
    new bootstrap.Modal(document.getElementById('expenseModal')).show();
}

async function submitExpense() {
    const name = document.getElementById('expName').value;
    const amount = document.getElementById('expAmount').value;
    const date = document.getElementById('expDate').value;
    const btn = document.getElementById('expSubmitBtn');

    if(!name || !amount) return alert('Isi semua data!');

    btn.disabled = true;
    try {
        const res = await fetch('{{ route("expenses.store") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf },
            body: JSON.stringify({ description: name, total: amount, created_at: date, type: 'expense' })
        });
        const data = await res.json();
        if(data.success) location.reload();
    } catch (e) { alert('Gagal simpan'); }
    btn.disabled = false;
}

async function deleteExpense(id) {
    if(!confirm('Hapus?')) return;
    try {
        await fetch(`/transactions/${id}`, { 
            method: 'DELETE', 
            headers: { 'X-CSRF-TOKEN':csrf } 
        });
        location.reload();
    } catch (e) { alert('Gagal hapus'); }
}
</script>
@endsection
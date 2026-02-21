@extends('layouts.app')
@section('title','Point of Sale')

@section('content')

<style>
* { box-sizing: border-box; }

/* =============================================
   RESET SCROLL — Semua muat 1 layar
   ============================================= */
html, body {
    margin: 0; padding: 0;
    height: 100%; overflow: hidden;
    font-family: Arial, sans-serif;
    font-size: 13px;
}

.pos-wrapper {
    padding: 7px 10px;
    height: calc(100vh - 56px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* =============================================
   HEADER TRANSAKSI
   ============================================= */
.trx-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 4px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 7px;
    height: 34px;
    flex-shrink: 0;
}
.trx-header .trx-left   { display: flex; align-items: center; gap: 10px; }
.trx-header .trx-number { font-size: 13px; font-weight: 700; color: #0d6efd; }
.trx-header .trx-time   { font-size: 11px; color: #6c757d; }

.new-transaction-btn {
    display: flex; align-items: center; gap: 4px;
    background: #28a745; color: white; border: none; border-radius: 5px;
    padding: 4px 10px; cursor: pointer; font-weight: 600; font-size: 12px;
    transition: all .2s; white-space: nowrap;
}
.new-transaction-btn:hover { background: #218838; }

/* =============================================
   LAYOUT UTAMA
   ============================================= */
.pos-container {
    display: flex;
    gap: 10px;
    flex: 1;
    overflow: hidden;
    min-height: 0;
}

/* =============================================
   KOLOM KIRI
   ============================================= */
.pos-left {
    flex: 0 0 285px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    gap: 5px;
}

/* =============================================
   KOLOM KANAN
   ============================================= */
.pos-right {
    flex: 1;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    gap: 5px;
}

/* =============================================
   TABEL UMUM
   ============================================= */
.pos-box      { border: 1px solid #ddd; border-radius: 5px; overflow: auto; }
.pos-table    { margin: 0; font-size: 12px; }
.pos-table th { background: #f5f5f5; position: sticky; top: 0; z-index: 1;
                font-size: 12px; padding: 4px 6px; }
.pos-table td { vertical-align: middle; padding: 3px 6px; font-size: 12px; }

.qty-input   { width: 48px; text-align: center; font-size: 12px; padding: 1px 3px; }
.unit-select { width: 74px; font-size: 12px; padding: 1px 3px; }
.big-total   { font-size: 17px; font-weight: bold; }
.locked      { background: #eee; cursor: not-allowed; }
.member-info { font-size: 11px; color: #555; word-break: break-word; }

/* =============================================
   LABEL SECTION
   ============================================= */
.section-label {
    font-size: 12px;
    font-weight: 700;
    color: #333;
    margin: 0;
    flex-shrink: 0;
}

/* =============================================
   FOKUS AKTIF — highlight input yang sedang aktif
   ============================================= */
.input-active {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 2px rgba(13,110,253,.2) !important;
    background-color: #f0f6ff !important;
}

/* =============================================
   KERANJANG — tampil 4 baris, scroll internal
   ============================================= */
.cart-section {
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}
.cart-table-header {
    background: #f5f5f5;
    border-bottom: 2px solid #ddd;
    flex-shrink: 0;
}
.cart-table-header table { margin: 0; width: 100%; table-layout: fixed; font-size: 12px; }
.cart-table-header th    { font-weight: 600; padding: 5px 7px; }

/* 4 item × ~32px per baris = 128px */
.cart-table-body {
    overflow-y: auto;
    max-height: calc(32px * 4);
    flex-shrink: 0;
}
.cart-table-body table { margin: 0; width: 100%; table-layout: fixed; font-size: 12px; }
.cart-table-body td    { padding: 3px 6px; vertical-align: middle; }

/* =============================================
   FOOTER KERANJANG
   ============================================= */
.cart-footer {
    border-top: 1px solid #ddd;
    padding-top: 6px;
    flex-shrink: 0;
}
.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

/* =============================================
   TRANSAKSI HARI INI — dengan badge pending
   ============================================= */
.trx-today-header {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-shrink: 0;
}
.pending-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #dc3545;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    border-radius: 20px;
    padding: 2px 7px;
    min-width: 20px;
    height: 18px;
    line-height: 1;
    animation: pulse-badge 1.5s infinite;
}
.pending-badge.hidden { display: none; }

@keyframes pulse-badge {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .8; transform: scale(1.08); }
}

/* =============================================
   MODAL PEMBAYARAN
   ============================================= */
.modal-overlay {
    display: none; position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,.5); z-index: 9999;
    justify-content: center; align-items: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: #fff; border-radius: 10px; padding: 18px;
    width: 360px; max-width: 95vw;
    box-shadow: 0 10px 30px rgba(0,0,0,.3); font-size: 13px;
}
.modal-box h5          { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
.modal-subtitle        { font-size: 11px; color: #6c757d; margin-bottom: 12px; }
.modal-total-display   {
    background: #f0f4ff; border: 1px solid #c8d8ff; border-radius: 6px;
    padding: 7px 12px; margin-bottom: 12px;
    display: flex; justify-content: space-between; align-items: center;
}
.modal-total-display .label  { font-size: 11px; color: #6c757d; }
.modal-total-display .amount { font-size: 16px; font-weight: 700; color: #0d6efd; }
.payment-methods       { display: flex; gap: 6px; margin-bottom: 10px; }
.payment-method-btn    {
    flex: 1; padding: 7px 5px; border: 2px solid #ddd; border-radius: 6px;
    background: #fff; cursor: pointer; text-align: center; transition: all .2s;
    font-size: 11px; font-weight: 600; color: #333;
}
.payment-method-btn:hover    { border-color: #0d6efd; background: #f0f4ff; }
.payment-method-btn.selected { border-color: #0d6efd; background: #e8f0fe; color: #0d6efd; }
.payment-method-btn .icon    { font-size: 20px; display: block; margin-bottom: 3px; }
.transfer-info         {
    display: none; background: #fff3cd; border: 1px solid #ffc107;
    border-radius: 5px; padding: 6px 8px; font-size: 11px; margin-bottom: 10px; color: #856404;
}
.transfer-info.show    { display: block; }
.modal-input-group     { margin-bottom: 8px; }
.modal-input-group label { font-size: 11px; font-weight: 600; margin-bottom: 3px; display: block; color: #333; }
.modal-kembalian-row   {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 10px; padding: 6px 8px;
    background: #f8fff8; border: 1px solid #d4edda; border-radius: 5px; font-size: 12px;
}
.modal-actions         { display: flex; gap: 6px; }
.modal-actions button  {
    flex: 1; padding: 8px; border: none; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer; transition: all .2s;
}
.btn-cancel-modal  { background: #f8f9fa; color: #333; border: 1px solid #ddd !important; }
.btn-cancel-modal:hover { background: #e9ecef; }
.btn-confirm-pay   { background: #0d6efd; color: white; }
.btn-confirm-pay:hover { background: #0b5ed7; }

/* Form controls */
.form-control-xs { font-size: 12px; padding: 3px 7px; height: 28px; }
.form-control-xs:focus { outline: none; border-color: #86b7fe; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }

.alert-xs { font-size: 12px; padding: 4px 9px; margin-bottom: 0; border-radius: 4px; }

/* Tombol qty */
.btn-qty { padding: 1px 6px; font-size: 12px; line-height: 1.5; }
</style>

<div class="pos-wrapper">

    {{-- HEADER --}}
    <div class="trx-header">
        <div class="trx-left">
            <span class="trx-number">{{ $trx->trx_number }}</span>
            <span class="trx-time">{{ $trx->created_at->format('d M Y') }} • {{ $trx->created_at->format('H:i:s') }}</span>
        </div>
        <button class="new-transaction-btn" onclick="createNewTransaction()">
            + Transaksi Baru
        </button>
    </div>

    <div class="pos-container">

        {{-- ========== KOLOM KIRI ========== --}}
        <div class="pos-left">

            <input type="hidden" id="warehouse_id" value="{{ $activeWarehouse->id }}">

            <div class="alert alert-info alert-xs">
                Gudang: <strong>{{ $activeWarehouse->name }}</strong>
            </div>

            {{-- Urutan navigasi Enter: barcode → search → member → discount_rp → discount_percent → paid → btnPay --}}
            <input type="text" id="barcode" class="form-control form-control-xs"
                   placeholder="① Scan barcode / Enter untuk lanjut">
            <input type="text" id="search"  class="form-control form-control-xs"
                   placeholder="② Cari nama / barcode produk">

            <span class="section-label">Hasil Pencarian</span>

            {{-- Tabel pencarian dengan kolom stok per gudang --}}
            <div class="pos-box" style="max-height: calc(28px * 4); flex-shrink:0; overflow-x:auto;">
                <table class="table table-sm pos-table mb-0" id="searchTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barcode</th>
                            <th>Nama</th>
                            <th>Sat.</th>
                            @foreach($warehouses as $idx => $wh)
                                <th title="{{ $wh->name }}">Stok {{ chr(65 + $idx) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="searchResult"></tbody>
                </table>
            </div>

            {{-- MEMBER --}}
            <div style="flex-shrink:0;">
                <span class="section-label">③ Member</span>
                <input type="text" id="member" class="form-control form-control-xs locked mt-1"
                       placeholder="Klik untuk input member" readonly onclick="unlockMember()">
                <div id="memberResult" class="border mt-1" style="max-height:65px; overflow:auto;"></div>
                <div id="memberInfo" class="mt-1 member-info"></div>
            </div>

            {{-- TRANSAKSI HARI INI --}}
            <div class="trx-today-header">
                <span class="section-label">Transaksi Hari Ini</span>
                @php $pendingCount = $todayTransactions->where('status','pending')->count(); @endphp
                <span class="pending-badge {{ $pendingCount == 0 ? 'hidden' : '' }}"
                      id="pendingBadge"
                      title="{{ $pendingCount }} transaksi pending">
                    {{ $pendingCount }} Pending
                </span>
            </div>

            <div class="pos-box" style="flex:1; overflow-y:auto; min-height:0;">
                <table class="table table-sm pos-table mb-0">
                    <thead>
                        <tr>
                            <th>No</th><th>Transaksi</th><th>Jam</th><th>Total</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayTransactions as $t)
                        <tr style="font-size:11px; cursor:{{ $t->status=='pending' ? 'pointer' : 'default' }};"
                            onclick="{{ $t->status=='pending' ? "openPending({$t->id})" : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $t->trx_number }}</td>
                            <td>{{ $t->created_at->format('H:i') }}</td>
                            <td>Rp {{ number_format($t->total) }}</td>
                            <td>
                                @if($t->status=='paid')
                                    <span class="badge bg-success" style="font-size:10px;">Paid</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size:10px;">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ========== KOLOM KANAN ========== --}}
        <div class="pos-right">

            {{-- KERANJANG --}}
            <div class="cart-section">
                <div class="cart-table-header">
                    <table class="table table-sm mb-0">
                        <colgroup>
                            <col style="width:28px">
                            <col>
                            <col style="width:78px">
                            <col style="width:125px">
                            <col style="width:95px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>*</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th>Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                {{-- BODY: maks 4 baris → scroll jika lebih --}}
                <div class="cart-table-body">
                    <table class="table table-bordered table-sm mb-0">
                        <colgroup>
                            <col style="width:28px">
                            <col>
                            <col style="width:78px">
                            <col style="width:125px">
                            <col style="width:95px">
                        </colgroup>
                        <tbody id="cartBody">
                            @php $total = 0; @endphp
                            @foreach($trx->items as $i)
                            @php $sub = $i->price * $i->qty; $total += $sub; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $i->unit->product->name }}
                                    <br><small class="text-muted" style="font-size:10px;">{{ $i->unit->barcode ?? '-' }}</small>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm unit-select"
                                            style="font-size:11px; padding:1px 3px;"
                                            onchange="updateUnit({{ $i->id }},this.value)">
                                        @foreach($i->unit->product->units as $u)
                                        <option value="{{ $u->id }}" {{ $u->id==$i->product_unit_id ? 'selected':'' }}>
                                            {{ $u->unit_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <button class="btn btn-sm btn-outline-secondary btn-qty"
                                                onclick="minusQty({{ $i->id }})">−</button>
                                        <input type="number" class="form-control form-control-sm qty-input"
                                               value="{{ $i->qty }}"
                                               onchange="updateQtyManual({{ $i->id }},this.value)">
                                        <button class="btn btn-sm btn-outline-secondary btn-qty"
                                                onclick="plusQty({{ $i->id }})">+</button>
                                        {{-- Hapus: wajib password owner dulu --}}
                                        <button class="btn btn-sm btn-danger btn-qty"
                                                onclick="removeItemWithAuth({{ $i->id }}, '{{ addslashes($i->unit->product->name) }}')">🗑</button>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold" style="font-size:12px;">
                                    Rp {{ number_format($sub) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER PEMBAYARAN --}}
            <div class="cart-footer">
                <div class="total-row">
                    <span style="font-size:14px; color:#6c757d; font-weight:600;">Total</span>
                    <span class="big-total" id="totalText"
                          data-total="{{ $total }}" data-original="{{ $total }}">
                        Rp {{ number_format($total) }}
                    </span>
                </div>

                <div class="total-row">
                    <span style="font-size:12px;">④ Diskon (Rp):</span>
                    <input type="number" id="discount_rp" class="form-control locked"
                           style="width:95px; font-size:12px; padding:2px 7px; height:28px;"
                           placeholder="Diskon (Rp)" readonly onclick="unlockDiscountRp()">
                </div>

                <div class="total-row">
                    <span style="font-size:12px;">⑤ Diskon (%):</span>
                    <input type="number" id="discount_percent" class="form-control locked"
                           style="width:95px; font-size:12px; padding:2px 7px; height:28px;"
                           placeholder="Diskon (%)" readonly onclick="unlockDiscountPercent()">
                </div>

                <input type="number" id="paid" class="form-control form-control-xs"
                       placeholder="⑥ Jumlah bayar → Enter untuk bayar">

                <div class="total-row mt-1">
                    <span style="font-size:13px;">Kembalian:</span>
                    <span id="changeText" class="big-total" style="color:#28a745; font-size:15px;">Rp 0</span>
                </div>

                <button id="btnPay" class="btn btn-primary btn-sm w-100 mt-1" style="font-size:13px;">
                    💳 Simpan / Bayar
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ========== MODAL PAYMENT ========== --}}
<div class="modal-overlay" id="paymentModal">
    <div class="modal-box">
        <h5>💳 Pilih Metode Pembayaran</h5>
        <div class="modal-subtitle">Pilih cara pembayaran untuk transaksi ini</div>

        <div class="modal-total-display">
            <span class="label">Total Tagihan</span>
            <span class="amount" id="modalTotalAmount">Rp 0</span>
        </div>

        <div class="payment-methods">
            <div class="payment-method-btn selected" id="btnCash" onclick="selectMethod('cash')">
                <span class="icon">💵</span>Cash
            </div>
            <div class="payment-method-btn" id="btnTransfer" onclick="selectMethod('transfer')">
                <span class="icon">🏦</span>Transfer
            </div>
        </div>

        <div class="transfer-info" id="transferInfo">
            <strong>⚠️ Perhatian:</strong> Pastikan bukti transfer sudah diterima sebelum menyelesaikan transaksi.
        </div>

        <div class="modal-input-group">
            <label for="modalPaid">Jumlah Bayar</label>
            <input type="number" id="modalPaid" class="form-control form-control-sm"
                   style="font-size:12px;" placeholder="Masukkan jumlah bayar">
        </div>

        <div class="modal-kembalian-row">
            <span style="font-size:13px; font-weight:600;">Kembalian</span>
            <span id="modalChangeText" class="big-total" style="color:#28a745; font-size:14px;">Rp 0</span>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel-modal" onclick="closePaymentModal()">✕ Batal</button>
            <button class="btn-confirm-pay"  id="btnConfirmPay" onclick="confirmPay()">✓ Proses Bayar</button>
        </div>
    </div>
</div>

<script>
let TRX  = {{ $trx->id }};
const csrf = '{{ csrf_token() }}';

// Data gudang dari PHP untuk kolom stok pencarian
const warehouseList = @json($warehousesJson);

function getWarehouseId() {
    return document.getElementById('warehouse_id').value;
}

const jsonHeaders = {
    'Content-Type' : 'application/json',
    'X-CSRF-TOKEN' : csrf,
    'Accept'       : 'application/json'
};

let memberUnlocked        = false;
let manualDiscountRp      = 0;
let manualDiscountPercent = 0;
let memberDiscount        = 0;
let selectedPaymentMethod = 'cash';

// ============================================
// URUTAN NAVIGASI ENTER
// barcode → search → member → discount_rp → discount_percent → paid → btnPay
// ============================================
const NAV_ORDER = [
    'barcode',
    'search',
    'member',
    'discount_rp',
    'discount_percent',
    'paid',
];

/**
 * Pindah fokus ke field berikutnya.
 * Jika sudah di field terakhir (paid), trigger klik btnPay.
 */
function focusNext(currentId) {
    const idx = NAV_ORDER.indexOf(currentId);
    if (idx === -1) return;

    if (idx === NAV_ORDER.length - 1) {
        // Terakhir: paid → buka modal bayar
        document.getElementById('btnPay').click();
        return;
    }

    const nextId = NAV_ORDER[idx + 1];
    const nextEl = document.getElementById(nextId);
    if (!nextEl) return;

    // Jika field terkunci (locked), lewati ke field berikutnya
    if (nextEl.readOnly || nextEl.classList.contains('locked')) {
        focusNext(nextId);
        return;
    }

    nextEl.focus();
    nextEl.select && nextEl.select();
    highlightActive(nextId);
}

/** Tambah highlight ke input yang aktif, hapus dari yang lain */
function highlightActive(activeId) {
    NAV_ORDER.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('input-active');
    });
    const el = document.getElementById(activeId);
    if (el) el.classList.add('input-active');
}

// ============================================
// ENTER DI BARCODE
// Jika ada isi → scan; jika kosong → pindah ke search
// ============================================
document.getElementById('barcode').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const code = this.value.trim();

    if (code === '') {
        // Kosong: loncat ke search
        focusNext('barcode');
        return;
    }

    // Ada isi: proses scan
    fetch('/pos/scan', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({ code: code, warehouse_id: getWarehouseId() })
    })
    .then(r => r.json())
    .then(r => {
        if (!r.success) { alert(r.message); return; }
        add(r.id);
        this.value = '';
        // Setelah scan berhasil, tetap di barcode untuk scan berikutnya
        this.focus();
        highlightActive('barcode');
    });
});

document.getElementById('barcode').addEventListener('focus', function () {
    highlightActive('barcode');
});

// ============================================
// ENTER DI SEARCH
// Jika ada hasil → pilih item pertama; jika kosong → pindah ke member
// ============================================
document.getElementById('search').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();

    const q = this.value.trim();

    if (q === '') {
        // Kosong: loncat ke member
        focusNext('search');
        return;
    }

    // Coba pilih item pertama dari hasil pencarian
    const firstRow = document.querySelector('#searchResult tr[data-unit-id]');
    if (firstRow) {
        firstRow.click();
        this.value = '';
        document.getElementById('searchResult').innerHTML = '';
        // Setelah tambah item, kembali ke barcode untuk scan berikutnya
        document.getElementById('barcode').focus();
        highlightActive('barcode');
    } else {
        // Tidak ada hasil / hasil belum dimuat: pindah ke member
        focusNext('search');
    }
});

document.getElementById('search').addEventListener('focus', function () {
    highlightActive('search');
});

// ============================================
// ENTER DI MEMBER
// Pindah ke discount_rp (jika unlocked) atau langsung ke discount_percent / paid
// ============================================
document.getElementById('member').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    focusNext('member');
});

document.getElementById('member').addEventListener('focus', function () {
    highlightActive('member');
});

// ============================================
// ENTER DI DISCOUNT RP
// ============================================
document.getElementById('discount_rp').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    focusNext('discount_rp');
});

document.getElementById('discount_rp').addEventListener('focus', function () {
    highlightActive('discount_rp');
});

// ============================================
// ENTER DI DISCOUNT %
// ============================================
document.getElementById('discount_percent').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    focusNext('discount_percent');
});

document.getElementById('discount_percent').addEventListener('focus', function () {
    highlightActive('discount_percent');
});

// ============================================
// ENTER DI PAID → buka modal bayar
// ============================================
document.getElementById('paid').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    focusNext('paid'); // akan trigger btnPay.click()
});

document.getElementById('paid').addEventListener('focus', function () {
    highlightActive('paid');
});

// ============================================
// ENTER DI MODAL PAID → konfirmasi bayar
// ============================================
document.getElementById('modalPaid').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    confirmPay();
});

// ============================================
// Saat halaman load, fokus ke barcode langsung
// ============================================
window.addEventListener('load', function () {
    document.getElementById('barcode').focus();
    highlightActive('barcode');
});

// ============================================
// BUAT TRANSAKSI BARU
// ============================================
function createNewTransaction() {
    window.location.href = '/pos?new_transaction=1';
}

// ============================================
// UNLOCK MEMBER
// ============================================
function unlockMember() {
    if (memberUnlocked) return;
    const pwd = prompt("Masukkan password owner:");
    if (!pwd) return;
    fetch('/pos/override-owner', {
        method:'POST', headers:jsonHeaders, body:JSON.stringify({ password: pwd })
    }).then(r=>r.json()).then(r=>{
        if (!r.success) { alert("Password salah"); return; }
        memberUnlocked = true;
        const el = document.getElementById('member');
        el.readOnly = false;
        el.classList.remove('locked');
        el.focus();
        highlightActive('member');
    });
}

// ============================================
// UNLOCK DISKON Rp
// ============================================
function unlockDiscountRp() {
    const el = document.getElementById('discount_rp');
    if (!el.classList.contains('locked')) return;
    const pwd = prompt("Masukkan password owner:");
    if (!pwd) return;
    fetch('/pos/override-owner', {
        method:'POST', headers:jsonHeaders, body:JSON.stringify({ password: pwd })
    }).then(r=>r.json()).then(r=>{
        if (!r.success) { alert("Password salah"); return; }
        el.readOnly = false;
        el.classList.remove('locked');
        el.focus();
        highlightActive('discount_rp');
    });
}

// ============================================
// UNLOCK DISKON %
// ============================================
function unlockDiscountPercent() {
    const el = document.getElementById('discount_percent');
    if (!el.classList.contains('locked')) return;
    const pwd = prompt("Masukkan password owner:");
    if (!pwd) return;
    fetch('/pos/override-owner', {
        method:'POST', headers:jsonHeaders, body:JSON.stringify({ password: pwd })
    }).then(r=>r.json()).then(r=>{
        if (!r.success) { alert("Password salah"); return; }
        el.readOnly = false;
        el.classList.remove('locked');
        el.focus();
        highlightActive('discount_percent');
    });
}

// ============================================
// INPUT DISKON Rp
// ============================================
document.getElementById('discount_rp').addEventListener('input', function () {
    const val = this.value.trim();
    if (val === '' || Number(val) <= 0) {
        manualDiscountRp = 0; this.value = '';
    } else {
        manualDiscountRp      = Number(val);
        manualDiscountPercent = 0;
        document.getElementById('discount_percent').value = '';
    }
    applyDiscountLive();
});

// ============================================
// INPUT DISKON %
// ============================================
document.getElementById('discount_percent').addEventListener('input', function () {
    const val = this.value.trim();
    if (val === '' || Number(val) <= 0) {
        manualDiscountPercent = 0; this.value = '';
    } else {
        manualDiscountPercent = Number(val);
        manualDiscountRp      = 0;
        document.getElementById('discount_rp').value = '';
    }
    applyDiscountLive();
});

// ============================================
// SEARCH PRODUK — tampilkan stok per gudang
// ============================================
document.getElementById('search').addEventListener('keyup', function (e) {
    // Abaikan Enter (sudah dihandle di keydown)
    if (e.key === 'Enter') return;

    const q = e.target.value.trim();
    if (q.length < 2) {
        document.getElementById('searchResult').innerHTML = '';
        return;
    }
    fetch(`/pos/search?q=${encodeURIComponent(q)}&warehouse_id=${getWarehouseId()}`)
        .then(r => r.json())
        .then(items => {
            let html = '';
            items.forEach((p, i) => {
                let stockCols = '';
                if (p.stocks && p.stocks.length) {
                    p.stocks.forEach(s => {
                        const color = s > 0 ? '#155724' : '#721c24';
                        const bg    = s > 0 ? '#d4edda' : '#f8d7da';
                        stockCols += `<td style="text-align:center;">
                            <span style="background:${bg};color:${color};padding:1px 5px;border-radius:4px;font-size:11px;font-weight:600;">${s}</span>
                        </td>`;
                    });
                }
                // data-unit-id dipakai oleh navigasi Enter untuk pilih item pertama
                html += `<tr style="cursor:pointer" data-unit-id="${p.id}" onclick="addFromSearch(${p.id})">
                    <td>${i+1}</td>
                    <td>${p.barcode ?? '-'}</td>
                    <td>${p.name}</td>
                    <td>${p.unit}</td>
                    ${stockCols}
                </tr>`;
            });
            document.getElementById('searchResult').innerHTML =
                html || '<tr><td colspan="' + (4 + warehouseList.length) + '" class="text-center text-muted">Tidak ada hasil</td></tr>';
        });
});

// ============================================
// ADD FROM SEARCH — klik baris hasil pencarian
// ============================================
function addFromSearch(id) {
    add(id);
    // Bersihkan search dan kembali ke barcode
    document.getElementById('search').value = '';
    document.getElementById('searchResult').innerHTML = '';
    document.getElementById('barcode').focus();
    highlightActive('barcode');
}

// ============================================
// ADD ITEM — kirim warehouse_id
// ============================================
function add(id, overridePassword = null) {
    fetch('/pos/add-item', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({
            trx_id           : TRX,
            product_unit_id  : id,
            warehouse_id     : getWarehouseId(),
            override_password: overridePassword
        })
    })
    .then(r => r.json())
    .then(r => {
        if (r.need_override) {
            const pwd = prompt("Stok tidak cukup!\nMasukkan password owner:");
            if (!pwd) return;
            add(id, pwd);
            return;
        }
        if (!r.success) { alert(r.message); return; }
        loadCart();
    });
}

// ============================================
// LOAD CART
// ============================================
function loadCart() {
    fetch(`/pos?trx_id=${TRX}`)
        .then(r => r.text())
        .then(html => {
            const doc           = new DOMParser().parseFromString(html, 'text/html');
            const totalEl       = doc.querySelector('#totalText');
            const originalTotal = Math.round(Number(totalEl.dataset.total));

            document.querySelector('#cartBody').innerHTML = doc.querySelector('#cartBody').innerHTML;
            document.getElementById('totalText').dataset.original = originalTotal;
            document.getElementById('totalText').dataset.total    = originalTotal;
            document.getElementById('totalText').innerText        = 'Rp ' + originalTotal.toLocaleString('id-ID');

            // Update badge pending
            const newPendingBadge = doc.querySelector('#pendingBadge');
            if (newPendingBadge) {
                const badge = document.getElementById('pendingBadge');
                badge.innerText = newPendingBadge.innerText;
                if (newPendingBadge.classList.contains('hidden')) {
                    badge.classList.add('hidden');
                } else {
                    badge.classList.remove('hidden');
                }
            }

            applyDiscountLive();
            updateKembalian();
        });
}

// ============================================
// DISKON LIVE
// ============================================
function applyDiscountLive() {
    const totalEl    = document.getElementById('totalText');
    const totalAwal  = Math.round(Number(totalEl.dataset.original));
    let   totalAkhir = totalAwal;

    if (manualDiscountRp > 0) {
        totalAkhir = totalAwal - Math.round(manualDiscountRp);
    } else if (manualDiscountPercent > 0) {
        totalAkhir = totalAwal - Math.round(totalAwal * manualDiscountPercent / 100);
    } else if (memberDiscount > 0) {
        totalAkhir = totalAwal - Math.round(totalAwal * memberDiscount / 100);
    }

    if (totalAkhir < 0) totalAkhir = 0;
    totalEl.innerText     = 'Rp ' + totalAkhir.toLocaleString('id-ID');
    totalEl.dataset.total = totalAkhir;
    updateKembalian();
}

// ============================================
// QTY
// ============================================
function plusQty(id)  { updateQtyManual(id, getQty(id) + 1); }
function minusQty(id) { updateQtyManual(id, Math.max(getQty(id) - 1, 1)); }
function getQty(id) {
    return Number(document.querySelector(`input[onchange="updateQtyManual(${id},this.value)"]`).value);
}

function updateQtyManual(itemId, qty, overridePassword = null) {
    fetch('/pos/update-qty-manual', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({
            trx_id           : TRX,
            item_id          : itemId,
            qty              : qty,
            warehouse_id     : getWarehouseId(),
            override_password: overridePassword
        })
    })
    .then(r => r.json())
    .then(r => {
        if (r.need_override) {
            const pwd = prompt("Stok tidak cukup!\nMasukkan password owner:");
            if (!pwd) return;
            updateQtyManual(itemId, qty, pwd);
            return;
        }
        loadCart();
    });
}

function updateUnit(itemId, unitId) {
    fetch('/pos/update-unit', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({
            trx_id         : TRX,
            item_id        : itemId,
            product_unit_id: unitId,
            warehouse_id   : getWarehouseId()
        })
    }).then(() => loadCart());
}

// ============================================
// REMOVE ITEM — wajib password owner + konfirmasi
// ============================================
function removeItemWithAuth(itemId, productName) {
    const pwd = prompt("🔐 Masukkan password owner untuk menghapus item:");
    if (!pwd) return;

    fetch('/pos/override-owner', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({ password: pwd })
    })
    .then(r => r.json())
    .then(r => {
        if (!r.success) {
            alert("❌ Password salah! Tidak dapat menghapus item.");
            return;
        }
        const ok = confirm(
            "⚠️ Konfirmasi Hapus Item\n\n" +
            "Produk : " + productName + "\n\n" +
            "Apakah Anda yakin ingin menghapus item ini dari keranjang?\n" +
            "Tindakan ini tidak dapat dibatalkan."
        );
        if (!ok) return;

        fetch('/pos/remove-item', {
            method  : 'POST',
            headers : jsonHeaders,
            body    : JSON.stringify({ trx_id: TRX, item_id: itemId })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                loadCart();
            } else {
                alert("Gagal menghapus item. Coba lagi.");
            }
        });
    });
}

// ============================================
// KEMBALIAN
// ============================================
document.getElementById('paid').addEventListener('input', updateKembalian);
function updateKembalian() {
    const total = Number(document.getElementById('totalText').dataset.total);
    const bayar = Number(document.getElementById('paid').value || 0);
    document.getElementById('changeText').innerText = 'Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID');
}

// ============================================
// MODAL PEMBAYARAN
// ============================================
function selectMethod(method) {
    selectedPaymentMethod = method;
    document.getElementById('btnCash').classList.toggle('selected', method === 'cash');
    document.getElementById('btnTransfer').classList.toggle('selected', method === 'transfer');
    document.getElementById('transferInfo').classList.toggle('show', method === 'transfer');
}

function openPaymentModal() {
    const total   = Number(document.getElementById('totalText').dataset.total);
    const paidVal = document.getElementById('paid').value;
    document.getElementById('modalPaid').value            = paidVal;
    document.getElementById('modalTotalAmount').innerText = 'Rp ' + total.toLocaleString('id-ID');
    updateModalKembalian();
    selectMethod('cash');
    document.getElementById('paymentModal').classList.add('show');
    setTimeout(() => {
        document.getElementById('modalPaid').focus();
        document.getElementById('modalPaid').select();
    }, 100);
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
    // Setelah tutup modal, kembalikan fokus ke paid
    setTimeout(() => {
        document.getElementById('paid').focus();
        highlightActive('paid');
    }, 50);
}

document.getElementById('modalPaid').addEventListener('input', updateModalKembalian);
function updateModalKembalian() {
    const total = Number(document.getElementById('totalText').dataset.total);
    const bayar = Number(document.getElementById('modalPaid').value || 0);
    document.getElementById('modalChangeText').innerText = 'Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID');
}

document.getElementById('paymentModal').addEventListener('click', function (e) {
    if (e.target === this) closePaymentModal();
});

document.getElementById('btnPay').addEventListener('click', function () {
    openPaymentModal();
});

// ============================================
// KONFIRMASI BAYAR
// ============================================
async function confirmPay() {
    const total         = Number(document.getElementById('totalText').dataset.total);
    const bayar         = Number(document.getElementById('modalPaid').value || 0);
    const memberId      = document.getElementById('member').dataset.memberId || null;
    const paymentMethod = selectedPaymentMethod;
    const strukWindow   = window.open('', '_blank');

    try {
        const res = await fetch('/pos/pay', {
            method  : 'POST',
            headers : jsonHeaders,
            body    : JSON.stringify({
                trx_id         : TRX,
                paid           : bayar,
                member_id      : memberId,
                payment_method : paymentMethod,
                frontend_total : total
            })
        });
        const r = await res.json();

        if (r.success) {
            if (r.paid_off) {
                closePaymentModal();
                const methodLabel = paymentMethod === 'cash' ? '💵 Cash' : '🏦 Transfer';
                alert('Transaksi lunas!\nMetode: ' + methodLabel +
                      '\nKembalian: Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID'));
                strukWindow.location.href = `/transactions/${r.trx_id}/struk`;
                setTimeout(() => { window.location.href = '/pos?new_transaction=1'; }, 500);
            } else {
                alert('Transaksi pending, sisa: Rp ' + (total - bayar).toLocaleString('id-ID'));
                strukWindow.close();
                closePaymentModal();
            }
        } else {
            alert(r.message || 'Gagal menyimpan transaksi');
            strukWindow.close();
        }
    } catch (e) {
        alert('Terjadi error: ' + e.message);
        strukWindow.close();
    }
}

// ============================================
// MEMBER SEARCH & SELECT
// ============================================
const memberBox  = document.getElementById('memberResult');
const memberInfo = document.getElementById('memberInfo');

document.getElementById('member').addEventListener('keyup', function (e) {
    if (!memberUnlocked) return;
    if (e.key === 'Enter') return; // sudah dihandle keydown
    const q = e.target.value;
    if (q.length < 2) { memberBox.innerHTML = ''; return; }
    fetch(`/pos/search-member?q=${q}`)
        .then(r => r.json())
        .then(items => {
            memberBox.innerHTML = '';
            items.forEach(m => {
                memberBox.innerHTML += `<div class="p-1 border-bottom" style="cursor:pointer; font-size:12px;"
                    onclick="selectMember(${m.id})">
                    <strong>${m.name}</strong> — <small class="text-muted">${m.phone}</small>
                </div>`;
            });
        });
});

function selectMember(id) {
    manualDiscountRp      = 0;
    manualDiscountPercent = 0;

    fetch(`/pos/get-member?id=${id}`)
        .then(r => r.json())
        .then(m => {
            const memberEl            = document.getElementById('member');
            memberEl.value            = m.name;
            memberEl.dataset.memberId = m.id;
            memberBox.innerHTML       = '';

            memberDiscount = Number(m.discount || 0);
            document.getElementById('discount_rp').value      = '';
            document.getElementById('discount_percent').value = memberDiscount > 0 ? memberDiscount : '';

            const discPctEl = document.getElementById('discount_percent');
            discPctEl.readOnly = false;
            discPctEl.classList.remove('locked');

            memberInfo.innerHTML = `
                <strong>Nama:</strong> ${m.name} |
                <strong>Level:</strong> ${m.level} |
                <strong>Disc:</strong> ${m.discount}% |
                <strong>Poin:</strong> ${m.points}`;

            applyDiscountLive();

            fetch('/pos/set-member', {
                method:'POST', headers:jsonHeaders,
                body: JSON.stringify({ trx_id: TRX, member_id: m.id })
            }).then(() => {
                fetch('/pos/set-discount', {
                    method:'POST', headers:jsonHeaders,
                    body: JSON.stringify({ trx_id: TRX, discount: getFinalDiscount() })
                }).then(() => loadCart());
            });
        });
}

function getFinalDiscount() {
    const total = Number(document.getElementById('totalText').dataset.original || 0);
    if (total <= 0) return 0;
    if (manualDiscountPercent > 0) return manualDiscountPercent;
    if (manualDiscountRp      > 0) return (manualDiscountRp / total) * 100;
    if (memberDiscount        > 0) return memberDiscount;
    return 0;
}

function openPending(trxId) {
    if (!trxId) return;
    if (confirm("Lanjutkan transaksi ini?")) {
        window.location.href = `/pos?trx_id=${trxId}`;
    }
}
</script>

@endsection
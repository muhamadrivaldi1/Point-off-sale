@extends('layouts.app')
@section('title','Point of Sale')

@section('content')

<style>
* { box-sizing: border-box; }

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

.pos-container {
    display: flex;
    gap: 10px;
    flex: 1;
    overflow: hidden;
    min-height: 0;
}

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

.section-label {
    font-size: 12px;
    font-weight: 700;
    color: #333;
    margin: 0;
    flex-shrink: 0;
}

.input-active {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 2px rgba(13,110,253,.2) !important;
    background-color: #f0f6ff !important;
}

#searchBox {
    border: 1px solid #ddd;
    border-radius: 5px 5px 0 0;
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(29px * 4 + 28px);
    flex-shrink: 0;
}
#searchBox table { margin: 0; font-size: 12px; white-space: nowrap; }
#searchBox thead th {
    background: #f5f5f5; position: sticky; top: 0; z-index: 2;
    font-size: 12px; padding: 4px 8px;
    border-bottom: 2px solid #ddd; white-space: nowrap;
}
#searchBox tbody td {
    vertical-align: middle; padding: 3px 8px;
    font-size: 12px; white-space: nowrap;
}

#searchResult tr.search-row-active td { background-color: #0d6efd !important; color: #fff !important; }
#searchResult tr.search-row-active td span { background: rgba(255,255,255,0.25) !important; color: #fff !important; }
#searchResult tr:hover td { background-color: #e8f0fe; }
#searchResult tr.search-row-active:hover td { background-color: #0b5ed7 !important; }

.search-nav-hint {
    display: none; font-size: 10px; color: #6c757d;
    padding: 2px 6px; background: #f8f9fa; border: 1px solid #ddd;
    border-top: none; border-radius: 0 0 4px 4px;
    text-align: center; flex-shrink: 0;
}
.search-nav-hint.show { display: block; }

#searchBox::-webkit-scrollbar        { width: 5px; height: 5px; }
#searchBox::-webkit-scrollbar-track  { background: #f1f1f1; }
#searchBox::-webkit-scrollbar-thumb  { background: #bbb; border-radius: 3px; }
#searchBox::-webkit-scrollbar-thumb:hover { background: #888; }

.cart-section {
    border: 1px solid #ddd; border-radius: 5px;
    overflow: hidden; display: flex; flex-direction: column; flex-shrink: 0;
}
.cart-table-header { background: #f5f5f5; border-bottom: 2px solid #ddd; flex-shrink: 0; }
.cart-table-header table { margin: 0; width: 100%; table-layout: fixed; font-size: 12px; }
.cart-table-header th    { font-weight: 600; padding: 5px 7px; }
.cart-table-body { overflow-y: auto; max-height: calc(32px * 4); flex-shrink: 0; }
.cart-table-body table { margin: 0; width: 100%; table-layout: fixed; font-size: 12px; }
.cart-table-body td    { padding: 3px 6px; vertical-align: middle; }

.cart-footer { border-top: 1px solid #ddd; padding-top: 6px; flex-shrink: 0; }
.total-row   { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }

.trx-today-header { display: flex; align-items: center; gap: 7px; flex-shrink: 0; }
.pending-badge {
    display: inline-flex; align-items: center; justify-content: center;
    background: #dc3545; color: #fff; font-size: 10px; font-weight: 700;
    border-radius: 20px; padding: 2px 7px; min-width: 20px; height: 18px; line-height: 1;
    animation: pulse-badge 1.5s infinite;
}
.pending-badge.hidden { display: none; }
@keyframes pulse-badge {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .8; transform: scale(1.08); }
}

/* ========== MODAL ========== */
.modal-overlay {
    display: none; position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,.48); z-index: 9999;
    justify-content: center; align-items: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: #fff; border-radius: 12px; padding: 22px 24px;
    width: 420px; max-width: 95vw;
    box-shadow: 0 16px 48px rgba(0,0,0,.25); font-size: 13px;
    animation: modalIn .18s ease;
}
@keyframes modalIn {
    from { transform: translateY(-20px); opacity: 0; }
    to   { transform: translateY(0);     opacity: 1; }
}
.modal-box h5    { font-size: 16px; font-weight: 800; margin-bottom: 3px; }
.modal-subtitle  { font-size: 11px; color: #888; margin-bottom: 14px; }

/* Total tagihan */
.modal-total-display {
    background: linear-gradient(135deg, #e8f0fe, #f0f6ff);
    border: 1px solid #c8d8ff; border-radius: 8px;
    padding: 10px 14px; margin-bottom: 16px;
    display: flex; justify-content: space-between; align-items: center;
}
.modal-total-display .label  { font-size: 11px; color: #6c757d; font-weight: 600; }
.modal-total-display .amount { font-size: 20px; font-weight: 800; color: #0d6efd; }

/* ===== COMBO LIST ===== */
.combo-list-label {
    font-size: 11px; font-weight: 700; color: #555;
    margin-bottom: 7px; display: block; text-transform: uppercase; letter-spacing: .4px;
}
.combo-list {
    border: 1.5px solid #e0e0e0; border-radius: 10px;
    overflow: hidden; margin-bottom: 14px;
}
.combo-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px; cursor: pointer; background: #fff;
    transition: background .12s; border-bottom: 1px solid #f2f2f2;
    position: relative; user-select: none;
}
.combo-item:last-child { border-bottom: none; }
.combo-item:hover      { background: #f5f8ff; }
.combo-item:focus      { outline: none; background: #eaf1ff; box-shadow: inset 0 0 0 2px #86b7fe; }
.combo-item.selected   { background: #eef3ff; }
.combo-item.selected::after {
    content: '✓';
    position: absolute; right: 14px;
    font-size: 14px; font-weight: 800; color: #0d6efd;
}
/* Kredit: warna beda saat selected */
.combo-item[data-method="kredit"].selected { background: #fff8e6; }
.combo-item[data-method="kredit"].selected::after { color: #e67e00; }
.combo-item[data-method="kredit"]:hover { background: #fff8e6; }
.combo-item[data-method="kredit"]:focus { box-shadow: inset 0 0 0 2px #ffc069; }

.combo-item-icon  { font-size: 22px; line-height: 1; }
.combo-item-title { font-size: 13px; font-weight: 700; color: #222; }
.combo-item-desc  { font-size: 10px; color: #999; margin-top: 1px; }

/* Peringatan non-cash */
.payment-notice {
    display: none; gap: 8px; align-items: flex-start;
    border-radius: 7px; padding: 8px 11px;
    font-size: 11px; margin-bottom: 12px;
}
.payment-notice.show { display: flex; }
.payment-notice.notice-warning  { background: #fffbe6; border: 1px solid #ffe58f; color: #7a5400; }
.payment-notice.notice-kredit   { background: #fff3e0; border: 1px solid #ffcc80; color: #7a3b00; }
.payment-notice-icon { font-size: 15px; flex-shrink: 0; margin-top: 1px; }

/* Input bayar — disembunyikan saat kredit */
.modal-input-group       { margin-bottom: 10px; }
.modal-input-group label { font-size: 11px; font-weight: 700; margin-bottom: 4px; display: block; color: #444; }
.modal-pay-input {
    width: 100%; font-size: 16px; font-weight: 700; padding: 9px 12px;
    border: 2px solid #dee2e6; border-radius: 8px; color: #222;
    transition: border-color .15s;
}
.modal-pay-input:focus { border-color: #86b7fe; outline: none; box-shadow: 0 0 0 3px rgba(13,110,253,.12); }
.modal-pay-input:disabled { background: #f5f5f5; color: #999; cursor: not-allowed; }

/* Kembalian */
.modal-kembalian-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 9px 13px; margin-bottom: 16px;
    background: #f0fff4; border: 1.5px solid #b7ebc8; border-radius: 8px;
}
.modal-kembalian-row .label { font-size: 13px; font-weight: 700; color: #1a7336; }
.modal-kembalian-row .value { font-size: 18px; font-weight: 800; color: #28a745; }
/* Kredit — warna oranye */
.modal-kembalian-row.kredit-mode { background: #fff3e0; border-color: #ffcc80; }
.modal-kembalian-row.kredit-mode .label { color: #8a3d00; }
.modal-kembalian-row.kredit-mode .value { font-size: 14px; color: #e67e00; }

/* Tombol */
.modal-actions        { display: flex; gap: 8px; }
.modal-actions button {
    flex: 1; padding: 10px; border: none; border-radius: 8px;
    font-size: 13px; font-weight: 700; cursor: pointer; transition: all .15s;
}
.btn-cancel-modal       { background: #f0f0f0; color: #555; }
.btn-cancel-modal:hover { background: #e2e2e2; }
.btn-confirm-pay        { background: #0d6efd; color: #fff; }
.btn-confirm-pay:hover  { background: #0b5ed7; }
.btn-confirm-pay:disabled { background: #9ab9f8; cursor: not-allowed; }
.btn-confirm-pay.kredit-btn        { background: #e67e00; }
.btn-confirm-pay.kredit-btn:hover  { background: #c46a00; }

/* MISC */
.form-control-xs { font-size: 12px; padding: 3px 7px; height: 28px; }
.form-control-xs:focus { outline: none; border-color: #86b7fe; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }
.alert-xs { font-size: 12px; padding: 4px 9px; margin-bottom: 0; border-radius: 4px; }
.btn-qty  { padding: 1px 6px; font-size: 12px; line-height: 1.5; }

#barcode.adding, #search.adding {
    background-color: #fff8e1 !important;
    border-color: #ffc107 !important;
}
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

            <input type="text" id="barcode" class="form-control form-control-xs"
                   placeholder="① Scan barcode / Enter untuk lanjut">
            <input type="text" id="search"  class="form-control form-control-xs"
                   placeholder="② Cari produk — ↑↓ pilih, ←→ geser kolom, Enter ambil">

            <span class="section-label">Hasil Pencarian</span>

            <div id="searchBox">
                <table class="table table-sm table-bordered mb-0" id="searchTable">
                    <thead>
                        <tr>
                            <th style="width:28px;">No</th>
                            <th style="min-width:110px;">Barcode</th>
                            <th style="min-width:140px;">Nama</th>
                            <th style="min-width:50px;">Sat.</th>
                            @foreach($warehouses as $idx => $wh)
                                <th style="min-width:58px; text-align:center;" title="{{ $wh->name }}">
                                    Stok {{ chr(65 + $idx) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="searchResult">
                        <tr>
                            <td colspan="{{ 4 + count($warehouses) }}" class="text-center text-muted"
                                style="font-size:11px; padding:6px;">
                                Ketik minimal 2 karakter untuk mencari produk
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="search-nav-hint" id="searchNavHint">
                ↑↓ Pilih baris &nbsp;|&nbsp; ←→ Geser kolom &nbsp;|&nbsp; Enter Ambil &nbsp;|&nbsp; Esc Tutup
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
                      id="pendingBadge" title="{{ $pendingCount }} transaksi pending">
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
                        <tr style="font-size:11px; cursor:pointer;"
                            onclick="{{ $t->status=='pending' ? "openPending({$t->id})" : "openPaidTransaction({$t->id})" }}"
                            title="{{ $t->status=='paid' ? 'Klik untuk buka kembali transaksi (butuh password)' : 'Klik untuk lanjutkan transaksi' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $t->trx_number }}</td>
                            <td>{{ $t->created_at->format('H:i') }}</td>
                            <td>Rp {{ number_format($t->total) }}</td>
                            <td>
                                @if($t->status=='paid')
                                    <span class="badge bg-success" style="font-size:10px;">✓ Paid</span>
                                @elseif($t->status=='kredit')
                                    <span class="badge bg-warning text-dark" style="font-size:10px;">💳 Kredit</span>
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
                            <col style="width:28px"><col>
                            <col style="width:78px"><col style="width:125px"><col style="width:95px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>*</th><th>Nama Produk</th><th>Satuan</th>
                                <th>Qty</th><th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="cart-table-body">
                    <table class="table table-bordered table-sm mb-0">
                        <colgroup>
                            <col style="width:28px"><col>
                            <col style="width:78px"><col style="width:125px"><col style="width:95px">
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

{{-- ========== MODAL PAYMENT — COMBO LIST ========== --}}
<div class="modal-overlay" id="paymentModal">
    <div class="modal-box">

        <h5>💳 Pembayaran</h5>
        <div class="modal-subtitle">Pilih metode, masukkan nominal, lalu proses bayar</div>

        {{-- Total tagihan --}}
        <div class="modal-total-display">
            <span class="label">Total Tagihan</span>
            <span class="amount" id="modalTotalAmount">Rp 0</span>
        </div>

        {{-- ===== COMBO LIST METODE BAYAR ===== --}}
        <span class="combo-list-label">Metode Pembayaran</span>
        <div class="combo-list">

            <div class="combo-item selected" data-method="cash" tabindex="0" onclick="selectMethod('cash')">
                <span class="combo-item-icon">💵</span>
                <div>
                    <div class="combo-item-title">Cash / Tunai</div>
                    <div class="combo-item-desc">Pembayaran langsung dengan uang tunai</div>
                </div>
            </div>

            <div class="combo-item" data-method="transfer" tabindex="0" onclick="selectMethod('transfer')">
                <span class="combo-item-icon">🏦</span>
                <div>
                    <div class="combo-item-title">Transfer Bank</div>
                    <div class="combo-item-desc">BCA / BNI / Mandiri / BSI dan lainnya</div>
                </div>
            </div>

            <div class="combo-item" data-method="qris" tabindex="0" onclick="selectMethod('qris')">
                <span class="combo-item-icon">📱</span>
                <div>
                    <div class="combo-item-title">QRIS</div>
                    <div class="combo-item-desc">GoPay, OVO, Dana, ShopeePay, dll</div>
                </div>
            </div>

            {{-- ★ BARU: Kredit --}}
            <div class="combo-item" data-method="kredit" tabindex="0" onclick="selectMethod('kredit')">
                <span class="combo-item-icon">📋</span>
                <div>
                    <div class="combo-item-title">Kredit / Hutang</div>
                    <div class="combo-item-desc">Pembayaran ditangguhkan — catat sebagai piutang</div>
                </div>
            </div>

        </div>

        {{-- Peringatan / info metode --}}
        <div class="payment-notice" id="paymentNotice">
            <span class="payment-notice-icon" id="paymentNoticeIcon"></span>
            <span id="paymentNoticeText"></span>
        </div>

        {{-- Input jumlah bayar (disembunyikan saat Kredit) --}}
        <div class="modal-input-group" id="paidInputGroup">
            <label for="modalPaid">Jumlah Bayar (Rp)</label>
            <input type="number" id="modalPaid" class="modal-pay-input"
                   placeholder="Masukkan jumlah bayar">
        </div>

        {{-- Kembalian / info kredit --}}
        <div class="modal-kembalian-row" id="modalKembalianRow">
            <span class="label" id="modalKembalianLabel">Kembalian</span>
            <span class="value" id="modalChangeText">Rp 0</span>
        </div>

        {{-- Tombol aksi --}}
        <div class="modal-actions">
            <button class="btn-cancel-modal" onclick="closePaymentModal()">✕ Batal</button>
            <button class="btn-confirm-pay"  id="btnConfirmPay" onclick="confirmPay()">✓ Proses Bayar</button>
        </div>

    </div>
</div>

<script>
let TRX  = {{ $trx->id }};
const csrf = '{{ csrf_token() }}';

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

// FIX DOUBLE-ADD: dua flag pengaman
let isAdding      = false;
let isScanPending = false;

// =============================================
// NAVIGASI KEYBOARD
// =============================================
let selectedSearchIdx = -1;

function updateSearchHighlight() {
    const rows = document.querySelectorAll('#searchResult tr[data-unit-id]');
    rows.forEach((row, i) => row.classList.toggle('search-row-active', i === selectedSearchIdx));
    if (selectedSearchIdx >= 0 && rows[selectedSearchIdx])
        rows[selectedSearchIdx].scrollIntoView({ block: 'nearest' });
}
function showSearchHint(show) {
    document.getElementById('searchNavHint').classList.toggle('show', show);
}
function resetSearchSelection() {
    selectedSearchIdx = -1; updateSearchHighlight(); showSearchHint(false);
}

const NAV_ORDER = ['barcode','search','member','discount_rp','discount_percent','paid'];

function focusNext(currentId) {
    const idx = NAV_ORDER.indexOf(currentId);
    if (idx === -1) return;
    if (idx === NAV_ORDER.length - 1) { document.getElementById('btnPay').click(); return; }
    const nextId = NAV_ORDER[idx + 1];
    const nextEl = document.getElementById(nextId);
    if (!nextEl) return;
    if (nextEl.readOnly || nextEl.classList.contains('locked')) { focusNext(nextId); return; }
    nextEl.focus(); nextEl.select && nextEl.select(); highlightActive(nextId);
}

function highlightActive(activeId) {
    NAV_ORDER.forEach(id => { const el = document.getElementById(id); if (el) el.classList.remove('input-active'); });
    const el = document.getElementById(activeId);
    if (el) el.classList.add('input-active');
}

// =============================================
// BARCODE — FIX DOUBLE-ADD
// =============================================
document.getElementById('barcode').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    if (isScanPending || isAdding) return;

    const code = this.value.trim();
    if (code === '') { focusNext('barcode'); return; }

    isScanPending = true;
    document.getElementById('barcode').classList.add('adding');
    document.getElementById('search').classList.add('adding');
    const barcodeEl = this;

    fetch('/pos/scan', { method:'POST', headers:jsonHeaders,
        body: JSON.stringify({ code, warehouse_id: getWarehouseId() })
    })
    .then(r => r.json())
    .then(r => {
        isScanPending = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        if (!r.success) { alert(r.message); return; }
        barcodeEl.value = '';
        add(r.id);
        barcodeEl.focus(); highlightActive('barcode');
    })
    .catch(err => {
        isScanPending = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        console.error('Scan error:', err);
        alert('Gagal scan barcode. Coba lagi.');
    });
});
document.getElementById('barcode').addEventListener('focus', () => highlightActive('barcode'));

// =============================================
// SEARCH
// =============================================
let isFromKeyboard = false;

document.getElementById('search').addEventListener('keydown', function (e) {
    const box  = document.getElementById('searchBox');
    const rows = document.querySelectorAll('#searchResult tr[data-unit-id]');
    const rc   = rows.length;

    if (e.key === 'ArrowDown') { e.preventDefault(); if (rc) { selectedSearchIdx = Math.min(selectedSearchIdx+1, rc-1); updateSearchHighlight(); } return; }
    if (e.key === 'ArrowUp')   { e.preventDefault(); if (rc) { selectedSearchIdx = selectedSearchIdx <= 0 ? -1 : selectedSearchIdx-1; updateSearchHighlight(); } return; }
    if (e.key === 'ArrowRight'){ e.preventDefault(); box.scrollLeft += 80; return; }
    if (e.key === 'ArrowLeft') { e.preventDefault(); box.scrollLeft -= 80; return; }

    if (e.key === 'Escape') {
        e.preventDefault();
        document.getElementById('searchResult').innerHTML = `<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:11px;padding:6px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
        this.value = ''; resetSearchSelection(); return;
    }

    if (e.key === 'Enter') {
        e.preventDefault();
        if (isScanPending || isAdding) return;
        if (selectedSearchIdx >= 0 && rows[selectedSearchIdx]) {
            isFromKeyboard = true; addFromSearch(Number(rows[selectedSearchIdx].dataset.unitId));
            setTimeout(() => { isFromKeyboard = false; }, 300); return;
        }
        if (rc === 1) { isFromKeyboard = true; addFromSearch(Number(rows[0].dataset.unitId)); setTimeout(() => { isFromKeyboard = false; }, 300); return; }
        if (this.value.trim() === '' || rc === 0) focusNext('search');
    }
});

document.getElementById('search').addEventListener('keyup', function (e) {
    if (['Enter','ArrowDown','ArrowUp','ArrowLeft','ArrowRight','Escape'].includes(e.key)) return;
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('searchResult').innerHTML = `<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:11px;padding:6px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
        resetSearchSelection(); document.getElementById('searchBox').scrollLeft = 0; return;
    }
    fetch(`/pos/search?q=${encodeURIComponent(q)}&warehouse_id=${getWarehouseId()}`)
        .then(r => r.json())
        .then(items => {
            selectedSearchIdx = -1;
            let html = '';
            items.forEach((p, i) => {
                let sc = '';
                (p.stocks || []).forEach(s => {
                    // Stok 0 tampil abu-abu (bukan merah), karena tetap bisa ditambahkan
                    const col = s > 0 ? '#155724' : '#666', bg = s > 0 ? '#d4edda' : '#e9ecef';
                    sc += `<td style="text-align:center;min-width:58px;"><span style="background:${bg};color:${col};padding:1px 6px;border-radius:4px;font-size:11px;font-weight:600;">${s}</span></td>`;
                });
                html += `<tr style="cursor:pointer;" data-unit-id="${p.id}" onclick="if(!isFromKeyboard) addFromSearch(${p.id})">
                    <td style="width:28px;">${i+1}</td>
                    <td style="min-width:110px;">${p.barcode??'-'}</td>
                    <td style="min-width:140px;">${p.name}</td>
                    <td style="min-width:50px;">${p.unit}</td>${sc}</tr>`;
            });
            const cc = 4 + warehouseList.length;
            document.getElementById('searchResult').innerHTML = html || `<tr><td colspan="${cc}" class="text-center text-muted" style="font-size:11px;padding:6px;">Tidak ada hasil untuk "<strong>${q}</strong>"</td></tr>`;
            document.getElementById('searchBox').scrollLeft = 0;
            showSearchHint(items.length > 0);
        });
});

document.getElementById('search').addEventListener('focus', function () {
    highlightActive('search');
    if (document.querySelectorAll('#searchResult tr[data-unit-id]').length > 0) showSearchHint(true);
});
document.getElementById('search').addEventListener('blur', () => setTimeout(() => showSearchHint(false), 200));

// =============================================
// FIELD LAIN
// =============================================
['member','discount_rp','discount_percent','paid'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('keydown', e => { if (e.key !== 'Enter') return; e.preventDefault(); focusNext(id); });
    el.addEventListener('focus', () => highlightActive(id));
});

document.getElementById('modalPaid').addEventListener('keydown', e => {
    if (e.key === 'Enter')  { e.preventDefault(); confirmPay(); return; }
    if (e.key === 'Escape') { e.preventDefault(); closePaymentModal(); return; }
    if ((e.key === 'ArrowUp') || (e.key === 'Tab' && e.shiftKey)) {
        e.preventDefault();
        const idx   = COMBO_METHODS.indexOf(selectedPaymentMethod);
        const items = document.querySelectorAll('.combo-item');
        if (items[idx]) items[idx].focus();
    }
});

// =============================================
// KEYBOARD NAVIGASI COMBO LIST MODAL
// =============================================
const COMBO_METHODS = ['cash','transfer','qris','kredit'];

document.querySelectorAll('.combo-item').forEach(function(item) {
    item.addEventListener('keydown', function(e) {
        const items = Array.from(document.querySelectorAll('.combo-item'));
        const idx   = items.indexOf(this);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const next = items[idx + 1];
            if (next) { selectMethod(next.dataset.method); next.focus(); }
            return;
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prev = items[idx - 1];
            if (prev) { selectMethod(prev.dataset.method); prev.focus(); }
            return;
        }
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectMethod(this.dataset.method);
            if (this.dataset.method !== 'kredit') {
                setTimeout(() => {
                    const inp = document.getElementById('modalPaid');
                    inp.focus(); inp.select && inp.select();
                }, 50);
            }
            return;
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            closePaymentModal();
        }
    });
});

window.addEventListener('load', () => { document.getElementById('barcode').focus(); highlightActive('barcode'); });

// =============================================
// TRANSAKSI BARU
// =============================================
function createNewTransaction() { window.location.href = '/pos?new_transaction=1'; }

// =============================================
// UNLOCK
// =============================================
function unlockMember() {
    if (memberUnlocked) return;
    const pwd = prompt("Masukkan password owner:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r=>r.json()).then(r=>{
            if (!r.success) { alert("Password salah"); return; }
            memberUnlocked = true;
            const el = document.getElementById('member');
            el.readOnly = false; el.classList.remove('locked'); el.focus(); highlightActive('member');
        });
}
function unlockDiscountRp() {
    const el = document.getElementById('discount_rp'); if (!el.classList.contains('locked')) return;
    const pwd = prompt("Masukkan password owner:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r=>r.json()).then(r=>{
            if (!r.success) { alert("Password salah"); return; }
            el.readOnly = false; el.classList.remove('locked'); el.focus(); highlightActive('discount_rp');
        });
}
function unlockDiscountPercent() {
    const el = document.getElementById('discount_percent'); if (!el.classList.contains('locked')) return;
    const pwd = prompt("Masukkan password owner:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r=>r.json()).then(r=>{
            if (!r.success) { alert("Password salah"); return; }
            el.readOnly = false; el.classList.remove('locked'); el.focus(); highlightActive('discount_percent');
        });
}

// =============================================
// DISKON
// =============================================
document.getElementById('discount_rp').addEventListener('input', function () {
    const val = this.value.trim();
    if (val === '' || Number(val) <= 0) { manualDiscountRp = 0; this.value = ''; }
    else { manualDiscountRp = Number(val); manualDiscountPercent = 0; document.getElementById('discount_percent').value = ''; }
    applyDiscountLive();
});
document.getElementById('discount_percent').addEventListener('input', function () {
    const val = this.value.trim();
    if (val === '' || Number(val) <= 0) { manualDiscountPercent = 0; this.value = ''; }
    else { manualDiscountPercent = Number(val); manualDiscountRp = 0; document.getElementById('discount_rp').value = ''; }
    applyDiscountLive();
});

// =============================================
// ADD FROM SEARCH
// =============================================
function addFromSearch(id) {
    if (isScanPending || isAdding) return;
    add(id);
    document.getElementById('search').value = '';
    document.getElementById('searchResult').innerHTML = `<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:11px;padding:6px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
    resetSearchSelection(); document.getElementById('searchBox').scrollLeft = 0;
    document.getElementById('barcode').focus(); highlightActive('barcode');
}

// =============================================
// ADD ITEM
// =============================================
function add(id, overridePassword = null) {
    if (isAdding) return;
    isAdding = true;
    document.getElementById('barcode').classList.add('adding');
    document.getElementById('search').classList.add('adding');

    fetch('/pos/add-item', { method:'POST', headers:jsonHeaders,
        body: JSON.stringify({ trx_id:TRX, product_unit_id:id, warehouse_id:getWarehouseId(), override_password:overridePassword })
    })
    .then(r => r.json())
    .then(r => {
        isAdding = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        if (r.need_override) { const pwd = prompt("Stok tidak cukup!\nMasukkan password owner:"); if (!pwd) return; add(id, pwd); return; }
        if (!r.success) { alert(r.message); return; }
        loadCart();
    })
    .catch(err => {
        isAdding = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        console.error(err); alert('Terjadi error saat menambah item. Coba lagi.');
    });
}

// =============================================
// LOAD CART
// =============================================
function loadCart() {
    fetch(`/pos?trx_id=${TRX}`).then(r => r.text()).then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const totalEl = doc.querySelector('#totalText');
        const orig = Math.round(Number(totalEl.dataset.total));
        document.querySelector('#cartBody').innerHTML = doc.querySelector('#cartBody').innerHTML;
        document.getElementById('totalText').dataset.original = orig;
        document.getElementById('totalText').dataset.total    = orig;
        document.getElementById('totalText').innerText        = 'Rp ' + orig.toLocaleString('id-ID');
        const nb = doc.querySelector('#pendingBadge');
        if (nb) { const b = document.getElementById('pendingBadge'); b.innerText = nb.innerText; b.classList.toggle('hidden', nb.classList.contains('hidden')); }
        applyDiscountLive(); updateKembalian();
    });
}

// =============================================
// DISKON LIVE
// =============================================
function applyDiscountLive() {
    const totalEl = document.getElementById('totalText');
    const awal    = Math.round(Number(totalEl.dataset.original));
    let   akhir   = awal;
    if (manualDiscountRp > 0)      akhir = awal - Math.round(manualDiscountRp);
    else if (manualDiscountPercent > 0) akhir = awal - Math.round(awal * manualDiscountPercent / 100);
    else if (memberDiscount > 0)   akhir = awal - Math.round(awal * memberDiscount / 100);
    if (akhir < 0) akhir = 0;
    totalEl.innerText = 'Rp ' + akhir.toLocaleString('id-ID');
    totalEl.dataset.total = akhir;
    updateKembalian();
}

// =============================================
// QTY
// =============================================
function plusQty(id)  { updateQtyManual(id, getQty(id) + 1); }
function minusQty(id) { updateQtyManual(id, Math.max(getQty(id) - 1, 1)); }
function getQty(id)   { return Number(document.querySelector(`input[onchange="updateQtyManual(${id},this.value)"]`).value); }

function updateQtyManual(itemId, qty, overridePassword = null) {
    fetch('/pos/update-qty-manual', { method:'POST', headers:jsonHeaders,
        body: JSON.stringify({ trx_id:TRX, item_id:itemId, qty, warehouse_id:getWarehouseId(), override_password:overridePassword })
    }).then(r => r.json()).then(r => {
        if (r.need_override) { const pwd = prompt("Stok tidak cukup!\nMasukkan password owner:"); if (!pwd) return; updateQtyManual(itemId, qty, pwd); return; }
        loadCart();
    });
}

function updateUnit(itemId, unitId) {
    fetch('/pos/update-unit', { method:'POST', headers:jsonHeaders,
        body: JSON.stringify({ trx_id:TRX, item_id:itemId, product_unit_id:unitId, warehouse_id:getWarehouseId() })
    }).then(() => loadCart());
}

// =============================================
// REMOVE ITEM
// =============================================
function removeItemWithAuth(itemId, productName) {
    const pwd = prompt("🔐 Masukkan password owner untuk menghapus item:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r => r.json()).then(r => {
            if (!r.success) { alert("❌ Password salah!"); return; }
            if (!confirm("⚠️ Hapus item:\n" + productName + "\n\nYakin?")) return;
            fetch('/pos/remove-item', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, item_id:itemId }) })
                .then(res => res.json()).then(res => { if (res.success) loadCart(); else alert("Gagal menghapus item."); });
        });
}

// =============================================
// KEMBALIAN MAIN
// =============================================
document.getElementById('paid').addEventListener('input', updateKembalian);
function updateKembalian() {
    const total = Number(document.getElementById('totalText').dataset.total);
    const bayar = Number(document.getElementById('paid').value || 0);
    document.getElementById('changeText').innerText = 'Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID');
}

// =============================================
// COMBO LIST — PILIH METODE BAYAR
// =============================================
function selectMethod(method) {
    selectedPaymentMethod = method;

    // Highlight baris terpilih
    document.querySelectorAll('.combo-item').forEach(el => {
        el.classList.toggle('selected', el.dataset.method === method);
    });

    const notice      = document.getElementById('paymentNotice');
    const noticeText  = document.getElementById('paymentNoticeText');
    const noticeIcon  = document.getElementById('paymentNoticeIcon');
    const paidGroup   = document.getElementById('paidInputGroup');
    const kembalianRow = document.getElementById('modalKembalianRow');
    const kembalianLabel = document.getElementById('modalKembalianLabel');
    const modalPaid   = document.getElementById('modalPaid');
    const confirmBtn  = document.getElementById('btnConfirmPay');
    const total       = Number(document.getElementById('totalText').dataset.total);

    // Reset semua state dulu
    notice.className = 'payment-notice';
    paidGroup.style.display = '';
    modalPaid.disabled = false;
    kembalianRow.className = 'modal-kembalian-row';
    kembalianLabel.textContent = 'Kembalian';
    confirmBtn.className = 'btn-confirm-pay';
    confirmBtn.textContent = '✓ Proses Bayar';

    if (method === 'kredit') {
        // === KREDIT: sembunyikan input bayar, tampilkan info khusus ===
        noticeIcon.textContent = '📋';
        noticeText.innerHTML   = 'Transaksi akan dicatat sebagai <strong>piutang / kredit</strong>. Stok tetap dikurangi. Pembayaran dapat dilakukan kemudian.';
        notice.classList.add('show', 'notice-kredit');

        paidGroup.style.display = 'none'; // sembunyikan input bayar
        modalPaid.value         = 0;

        kembalianRow.classList.add('kredit-mode');
        kembalianLabel.textContent = 'Status';
        document.getElementById('modalChangeText').textContent = '⏳ Belum Dibayar';

        confirmBtn.classList.add('kredit-btn');
        confirmBtn.textContent = '📋 Simpan Kredit';

    } else if (method === 'transfer') {
        noticeIcon.textContent = '⚠️';
        noticeText.innerHTML   = 'Pastikan bukti <strong>transfer bank</strong> sudah diterima sebelum menyelesaikan transaksi.';
        notice.classList.add('show', 'notice-warning');
        modalPaid.value = total;
        updateModalKembalian();

    } else if (method === 'qris') {
        noticeIcon.textContent = '📱';
        noticeText.innerHTML   = 'Pastikan notifikasi <strong>QRIS</strong> diterima terlebih dahulu sebelum proses bayar.';
        notice.classList.add('show', 'notice-warning');
        modalPaid.value = total;
        updateModalKembalian();

    } else {
        // cash — bersih, tidak ada notice
        updateModalKembalian();
    }
}

// =============================================
// MODAL PAYMENT
// =============================================
function openPaymentModal() {
    const total   = Number(document.getElementById('totalText').dataset.total);
    const paidVal = document.getElementById('paid').value;

    document.getElementById('modalTotalAmount').innerText = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('modalPaid').value            = paidVal || '';

    selectMethod('cash');
    updateModalKembalian();

    document.getElementById('paymentModal').classList.add('show');
    setTimeout(() => {
        const firstCombo = document.querySelector('.combo-item');
        if (firstCombo) firstCombo.focus();
    }, 150);
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
    setTimeout(() => { document.getElementById('paid').focus(); highlightActive('paid'); }, 50);
}

document.getElementById('modalPaid').addEventListener('input', updateModalKembalian);
function updateModalKembalian() {
    if (selectedPaymentMethod === 'kredit') return; // jangan update saat kredit
    const total = Number(document.getElementById('totalText').dataset.total);
    const bayar = Number(document.getElementById('modalPaid').value || 0);
    document.getElementById('modalChangeText').innerText = 'Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID');
}

document.getElementById('paymentModal').addEventListener('click', function (e) { if (e.target === this) closePaymentModal(); });
document.getElementById('btnPay').addEventListener('click', openPaymentModal);

// =============================================
// KONFIRMASI BAYAR
// =============================================
async function confirmPay() {
    const total         = Number(document.getElementById('totalText').dataset.total);
    const paymentMethod = selectedPaymentMethod;

    // Kredit: langsung proses tanpa input bayar
    const bayar = paymentMethod === 'kredit' ? 0 : Number(document.getElementById('modalPaid').value || 0);

    const memberId    = document.getElementById('member').dataset.memberId || null;
    const strukWindow = window.open('', '_blank');

    try {
        const res = await fetch('/pos/pay', { method:'POST', headers:jsonHeaders,
            body: JSON.stringify({ trx_id:TRX, paid:bayar, member_id:memberId, payment_method:paymentMethod, frontend_total:total })
        });
        const r = await res.json();

        if (r.success) {
            if (r.paid_off || r.is_kredit) {
                closePaymentModal();

                if (r.is_kredit) {
                    alert('📋 Transaksi disimpan sebagai KREDIT!\nTotal tagihan: Rp ' + total.toLocaleString('id-ID'));
                } else {
                    const labels = { cash:'💵 Cash / Tunai', transfer:'🏦 Transfer Bank', qris:'📱 QRIS' };
                    alert('✅ Transaksi lunas!\nMetode   : ' + (labels[paymentMethod] || paymentMethod) +
                          '\nKembalian: Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID'));
                }

                strukWindow.location.href = `/transactions/${r.trx_id}/struk`;
                setTimeout(() => { window.location.href = '/pos?new_transaction=1'; }, 500);
            } else {
                alert('Transaksi pending, sisa: Rp ' + (total - bayar).toLocaleString('id-ID'));
                strukWindow.close(); closePaymentModal();
            }
        } else {
            alert(r.message || 'Gagal menyimpan transaksi');
            strukWindow.close();
        }
    } catch (err) {
        alert('Terjadi error: ' + err.message);
        strukWindow.close();
    }
}

// =============================================
// MEMBER
// =============================================
const memberBox  = document.getElementById('memberResult');
const memberInfo = document.getElementById('memberInfo');

document.getElementById('member').addEventListener('keyup', function (e) {
    if (!memberUnlocked || e.key === 'Enter') return;
    const q = this.value; if (q.length < 2) { memberBox.innerHTML = ''; return; }
    fetch(`/pos/search-member?q=${q}`).then(r=>r.json()).then(items => {
        memberBox.innerHTML = '';
        items.forEach(m => { memberBox.innerHTML += `<div class="p-1 border-bottom" style="cursor:pointer;font-size:12px;" onclick="selectMember(${m.id})"><strong>${m.name}</strong> — <small class="text-muted">${m.phone}</small></div>`; });
    });
});

function selectMember(id) {
    manualDiscountRp = manualDiscountPercent = 0;
    fetch(`/pos/get-member?id=${id}`).then(r=>r.json()).then(m => {
        const el = document.getElementById('member');
        el.value = m.name; el.dataset.memberId = m.id; memberBox.innerHTML = '';
        memberDiscount = Number(m.discount || 0);
        document.getElementById('discount_rp').value      = '';
        document.getElementById('discount_percent').value = memberDiscount > 0 ? memberDiscount : '';
        const dp = document.getElementById('discount_percent'); dp.readOnly = false; dp.classList.remove('locked');
        memberInfo.innerHTML = `<strong>Nama:</strong> ${m.name} | <strong>Level:</strong> ${m.level} | <strong>Disc:</strong> ${m.discount}% | <strong>Poin:</strong> ${m.points}`;
        applyDiscountLive();
        fetch('/pos/set-member', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, member_id:m.id }) })
            .then(() => fetch('/pos/set-discount', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, discount:getFinalDiscount() }) }))
            .then(() => loadCart());
    });
}

function getFinalDiscount() {
    const total = Number(document.getElementById('totalText').dataset.original || 0); if (total <= 0) return 0;
    if (manualDiscountPercent > 0) return manualDiscountPercent;
    if (manualDiscountRp      > 0) return (manualDiscountRp / total) * 100;
    if (memberDiscount        > 0) return memberDiscount;
    return 0;
}

function openPending(trxId) {
    if (!trxId) return;
    if (confirm("Lanjutkan transaksi ini?")) window.location.href = `/pos?trx_id=${trxId}`;
}

// =============================================
// BUKA KEMBALI TRANSAKSI PAID / KREDIT
// =============================================
function openPaidTransaction(trxId) {
    if (!trxId) return;

    const pwd = prompt("🔐 Masukkan password owner untuk membuka kembali transaksi ini:");
    if (!pwd) return;

    document.body.style.cursor = 'wait';

    fetch('/pos/reopen-transaction', {
        method : 'POST',
        headers: jsonHeaders,
        body   : JSON.stringify({ trx_id: trxId, password: pwd })
    })
    .then(r => r.json())
    .then(r => {
        document.body.style.cursor = '';
        if (!r.success) {
            alert("❌ " + (r.message || "Gagal membuka transaksi"));
            return;
        }
        window.location.href = `/pos?trx_id=${r.trx_id}`;
    })
    .catch(err => {
        document.body.style.cursor = '';
        console.error(err);
        alert("Terjadi error. Coba lagi.");
    });
}
</script>

@endsection
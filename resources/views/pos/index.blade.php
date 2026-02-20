@extends('layouts.app')
@section('title','Point of Sale')

@section('content')

<style>
* { box-sizing: border-box; }
body { margin:0; padding:0; font-family: Arial, sans-serif; }

.pos-wrapper { padding: 15px; max-width: 100%; }

.trx-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 6px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    height: 38px;
}
.trx-header .trx-left   { display:flex; align-items:center; gap:12px; }
.trx-header .trx-number { font-size:14px; font-weight:700; color:#0d6efd; }
.trx-header .trx-time   { font-size:11px; color:#6c757d; }

.new-transaction-btn {
    display:flex; align-items:center; gap:6px;
    background:#28a745; color:white; border:none; border-radius:6px;
    padding:6px 14px; cursor:pointer; font-weight:600; font-size:12px;
    transition:all .2s; white-space:nowrap;
}
.new-transaction-btn:hover { background:#218838; box-shadow:0 2px 6px rgba(0,0,0,.15); }

.pos-container { display:flex; gap:15px; }

.pos-left {
    flex: 0 0 350px;
    background:#fff; border:1px solid #ddd; border-radius:6px; padding:15px;
}
.pos-right {
    flex:1; background:#fff; border:1px solid #ddd; border-radius:6px;
    padding:15px; display:flex; flex-direction:column;
}

.pos-box { border:1px solid #ddd; border-radius:6px; overflow:auto; max-height:350px; }
.pos-table { margin:0; }
.pos-table th { background:#f5f5f5; font-size:13px; position:sticky; top:0; z-index:1; }
.pos-table td { vertical-align:middle; }

.qty-input  { width:70px; text-align:center; }
.unit-select { width:100px; }
.big-total   { font-size:22px; font-weight:bold; }
.locked      { background:#eee; cursor:not-allowed; }
.member-info { font-size:12px; color:#555; word-break:break-word; }

.cart-section      { border:1px solid #ddd; border-radius:6px; overflow:hidden; margin-bottom:15px; }
.cart-table-header { background:#f5f5f5; border-bottom:2px solid #ddd; }
.cart-table-header table { margin:0; width:100%; table-layout:fixed; }
.cart-table-header th    { font-size:13px; padding:8px 10px; font-weight:600; background:#f5f5f5; }
.cart-table-body         { overflow-y:auto; max-height:300px; }
.cart-table-body table   { margin:0; width:100%; table-layout:fixed; }

.cart-footer { border-top:2px solid #ddd; padding-top:15px; }
.total-row   { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }

/* MODAL */
.modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.5); z-index:9999; justify-content:center; align-items:center; }
.modal-overlay.show { display:flex; }
.modal-box   { background:#fff; border-radius:10px; padding:25px; width:400px; max-width:95vw; box-shadow:0 10px 30px rgba(0,0,0,.3); }
.modal-box h5 { font-size:17px; font-weight:700; margin-bottom:4px; }
.modal-subtitle { font-size:12px; color:#6c757d; margin-bottom:18px; }
.modal-total-display { background:#f0f4ff; border:1px solid #c8d8ff; border-radius:8px; padding:12px 16px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; }
.modal-total-display .label  { font-size:13px; color:#6c757d; }
.modal-total-display .amount { font-size:22px; font-weight:700; color:#0d6efd; }
.payment-methods { display:flex; gap:10px; margin-bottom:16px; }
.payment-method-btn { flex:1; padding:14px 10px; border:2px solid #ddd; border-radius:8px; background:#fff; cursor:pointer; text-align:center; transition:all .2s; font-size:13px; font-weight:600; color:#333; }
.payment-method-btn:hover  { border-color:#0d6efd; background:#f0f4ff; }
.payment-method-btn.selected { border-color:#0d6efd; background:#e8f0fe; color:#0d6efd; }
.payment-method-btn .icon  { font-size:26px; display:block; margin-bottom:6px; }
.transfer-info        { display:none; background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:10px 12px; font-size:12px; margin-bottom:14px; color:#856404; }
.transfer-info.show   { display:block; }
.modal-input-group    { margin-bottom:12px; }
.modal-input-group label { font-size:13px; font-weight:600; margin-bottom:5px; display:block; color:#333; }
.modal-kembalian-row  { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; padding:10px 12px; background:#f8fff8; border:1px solid #d4edda; border-radius:6px; }
.modal-actions        { display:flex; gap:10px; }
.modal-actions button { flex:1; padding:12px; border:none; border-radius:6px; font-size:14px; font-weight:600; cursor:pointer; transition:all .2s; }
.btn-cancel-modal     { background:#f8f9fa; color:#333; border:1px solid #ddd !important; }
.btn-cancel-modal:hover { background:#e9ecef; }
.btn-confirm-pay      { background:#0d6efd; color:white; }
.btn-confirm-pay:hover { background:#0b5ed7; }

@media (max-width:1200px) { .pos-container { flex-direction:column; } .pos-left { flex:1 1 auto; } }
@media (max-width:768px)  { .trx-header { height:auto; padding:8px 12px; } }
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

    {{-- KOLOM KIRI --}}
    <div class="pos-left">

        {{-- Simpan warehouse_id sebagai hidden input --}}
        <input type="hidden" id="warehouse_id" value="{{ $activeWarehouse->id }}">

        <div class="alert alert-info py-2" style="font-size:13px">
            Gudang Aktif: <strong>{{ $activeWarehouse->name }}</strong>
        </div>

        <input type="text" id="barcode" class="form-control mb-2" placeholder="Scan barcode lalu Enter">
        <input type="text" id="search"  class="form-control mb-2" placeholder="Cari nama / barcode produk">

        <label class="form-label">Hasil Pencarian</label>
        <div class="pos-box mb-3">
            <table class="table table-sm pos-table">
                <thead>
                    <tr>
                        <th>No</th><th>Barcode</th><th>Nama</th><th>Satuan</th><th>Stok</th>
                    </tr>
                </thead>
                <tbody id="searchResult"></tbody>
            </table>
        </div>

        {{-- MEMBER --}}
        <div class="mb-2">
            <label class="form-label">Member</label>
            <input type="text" id="member" class="form-control locked"
                   placeholder="Klik untuk input member" readonly onclick="unlockMember()">
            <div id="memberResult" class="border mt-1" style="max-height:150px;overflow:auto"></div>
            <div id="memberInfo" class="mt-2 member-info"></div>
        </div>

        {{-- HISTORI HARI INI --}}
        <div class="mt-3">
            <label class="form-label fw-bold">Transaksi Hari Ini</label>
            <div class="pos-box" style="max-height:200px">
                <table class="table table-sm pos-table">
                    <thead>
                        <tr>
                            <th>No</th><th>Transaksi</th><th>Jam</th><th>Total</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayTransactions as $t)
                        <tr style="font-size:12px; cursor:{{ $t->status=='pending' ? 'pointer' : 'default' }};"
                            onclick="{{ $t->status=='pending' ? "openPending({$t->id})" : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $t->trx_number }}</td>
                            <td>{{ $t->created_at->format('H:i') }}</td>
                            <td>Rp {{ number_format($t->total) }}</td>
                            <td>
                                @if($t->status=='paid')
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
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
    </div>

    {{-- KOLOM KANAN --}}
    <div class="pos-right">

        <div class="cart-section">
            <div class="cart-table-header">
                <table class="table table-sm mb-0">
                    <colgroup>
                        <col style="width:36px"><col><col style="width:115px">
                        <col style="width:195px"><col style="width:120px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>*</th><th>Nama</th><th>Satuan</th><th>Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="cart-table-body">
                <table class="table table-bordered table-sm mb-0">
                    <colgroup>
                        <col style="width:36px"><col><col style="width:115px">
                        <col style="width:195px"><col style="width:120px">
                    </colgroup>
                    <tbody id="cartBody">
                        @php $total = 0; @endphp
                        @foreach($trx->items as $i)
                        @php $sub = $i->price * $i->qty; $total += $sub; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $i->unit->product->name }}
                                <br><small class="text-muted">{{ $i->unit->barcode ?? '-' }}</small>
                            </td>
                            <td>
                                <select class="form-select form-select-sm unit-select"
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
                                    <button class="btn btn-sm btn-outline-secondary" onclick="minusQty({{ $i->id }})">−</button>
                                    <input type="number" class="form-control form-control-sm qty-input"
                                           value="{{ $i->qty }}"
                                           onchange="updateQtyManual({{ $i->id }},this.value)">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="plusQty({{ $i->id }})">+</button>
                                    <button class="btn btn-sm btn-danger" onclick="removeItem({{ $i->id }})">🗑</button>
                                </div>
                            </td>
                            <td class="text-end fw-semibold">Rp {{ number_format($sub) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="cart-footer">
            <div class="total-row">
                <span style="font-size:15px;color:#6c757d;font-weight:600;">Total</span>
                <span class="big-total" id="totalText"
                      data-total="{{ $total }}" data-original="{{ $total }}">
                    Rp {{ number_format($total) }}
                </span>
            </div>

            <div class="total-row">
                <span style="font-size:14px;">Diskon (Rp):</span>
                <input type="number" id="discount_rp" class="form-control locked"
                       style="width:120px" placeholder="Diskon (Rp)" readonly
                       onclick="unlockDiscountRp()">
            </div>

            <div class="total-row">
                <span style="font-size:14px;">Diskon (%):</span>
                <input type="number" id="discount_percent" class="form-control locked"
                       style="width:120px" placeholder="Diskon (%)" readonly
                       onclick="unlockDiscountPercent()">
            </div>

            <input type="number" id="paid" class="form-control form-control-lg"
                   placeholder="Masukkan jumlah bayar">

            <div class="total-row mt-3">
                <span style="font-size:18px;">Kembalian:</span>
                <span id="changeText" class="big-total" style="color:#28a745">Rp 0</span>
            </div>

            <button id="btnPay" class="btn btn-primary btn-lg w-100 mt-3">
                Simpan / Bayar
            </button>
        </div>

    </div>
</div>
</div>

{{-- MODAL METODE PEMBAYARAN --}}
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
            <input type="number" id="modalPaid" class="form-control form-control-lg"
                   placeholder="Masukkan jumlah bayar">
        </div>

        <div class="modal-kembalian-row">
            <span style="font-size:15px;font-weight:600;">Kembalian</span>
            <span id="modalChangeText" class="big-total" style="color:#28a745;font-size:20px;">Rp 0</span>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel-modal" onclick="closePaymentModal()">✕ Batal</button>
            <button class="btn-confirm-pay"  onclick="confirmPay()">✓ Proses Bayar</button>
        </div>
    </div>
</div>

<script>
let TRX  = {{ $trx->id }};
const csrf = '{{ csrf_token() }}';

// Ambil warehouse_id dari hidden input
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
// SEARCH PRODUK — kirim warehouse_id
// ============================================
document.getElementById('search').addEventListener('keyup', function (e) {
    const q = e.target.value.trim();
    if (q.length < 2) {
        document.getElementById('searchResult').innerHTML = '';
        return;
    }
    // Kirim warehouse_id, BUKAN location
    fetch(`/pos/search?q=${encodeURIComponent(q)}&warehouse_id=${getWarehouseId()}`)
        .then(r => r.json())
        .then(items => {
            let html = '';
            items.forEach((p, i) => {
                html += `<tr style="cursor:pointer" onclick="add(${p.id})">
                    <td>${i+1}</td>
                    <td>${p.barcode ?? '-'}</td>
                    <td>${p.name}</td>
                    <td>${p.unit}</td>
                    <td>${p.stock}</td>
                </tr>`;
            });
            document.getElementById('searchResult').innerHTML = html || '<tr><td colspan="5" class="text-center text-muted">Tidak ada hasil</td></tr>';
        });
});

// ============================================
// SCAN BARCODE — kirim warehouse_id
// ============================================
document.getElementById('barcode').addEventListener('keypress', function (e) {
    if (e.key !== 'Enter') return;
    const code = this.value.trim();
    if (!code) return;

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
    });
});

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
            warehouse_id     : getWarehouseId(),   // warehouse_id bukan location
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
            const doc         = new DOMParser().parseFromString(html, 'text/html');
            const totalEl     = doc.querySelector('#totalText');
            const originalTotal = Math.round(Number(totalEl.dataset.total));

            document.querySelector('#cartBody').innerHTML = doc.querySelector('#cartBody').innerHTML;
            document.getElementById('totalText').dataset.original = originalTotal;
            document.getElementById('totalText').dataset.total    = originalTotal;
            document.getElementById('totalText').innerText        = 'Rp ' + originalTotal.toLocaleString('id-ID');

            applyDiscountLive();
            updateKembalian();
        });
}

// ============================================
// DISKON LIVE
// ============================================
function applyDiscountLive() {
    const totalEl   = document.getElementById('totalText');
    const totalAwal = Math.round(Number(totalEl.dataset.original));
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
function getQty(id)   {
    return Number(document.querySelector(`input[onchange="updateQtyManual(${id},this.value)"]`).value);
}

// UPDATE QTY MANUAL — kirim warehouse_id
function updateQtyManual(itemId, qty, overridePassword = null) {
    fetch('/pos/update-qty-manual', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({
            trx_id           : TRX,
            item_id          : itemId,
            qty              : qty,
            warehouse_id     : getWarehouseId(),   // warehouse_id bukan location
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

// UPDATE UNIT — kirim warehouse_id
function updateUnit(itemId, unitId) {
    fetch('/pos/update-unit', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({
            trx_id         : TRX,
            item_id        : itemId,
            product_unit_id: unitId,
            warehouse_id   : getWarehouseId()    // warehouse_id bukan location
        })
    }).then(() => loadCart());
}

// ============================================
// REMOVE ITEM
// ============================================
function removeItem(itemId) {
    if (!confirm('Hapus item ini?')) return;
    fetch('/pos/remove-item', {
        method  : 'POST',
        headers : jsonHeaders,
        body    : JSON.stringify({ trx_id: TRX, item_id: itemId })
    }).then(() => loadCart());
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
    const total    = Number(document.getElementById('totalText').dataset.total);
    const paidVal  = document.getElementById('paid').value;
    document.getElementById('modalPaid').value           = paidVal;
    document.getElementById('modalTotalAmount').innerText = 'Rp ' + total.toLocaleString('id-ID');
    updateModalKembalian();
    selectMethod('cash');
    document.getElementById('paymentModal').classList.add('show');
    setTimeout(() => document.getElementById('modalPaid').focus(), 100);
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
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
                alert('Transaksi lunas!\nMetode: ' + methodLabel + '\nKembalian: Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID'));
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
    const q = e.target.value;
    if (q.length < 2) { memberBox.innerHTML = ''; return; }
    fetch(`/pos/search-member?q=${q}`)
        .then(r => r.json())
        .then(items => {
            memberBox.innerHTML = '';
            items.forEach(m => {
                memberBox.innerHTML += `<div class="p-2 border-bottom" style="cursor:pointer" onclick="selectMember(${m.id})">
                    <strong>${m.name}</strong><br><small class="text-muted">${m.phone} | ${m.address}</small>
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
            const memberEl   = document.getElementById('member');
            memberEl.value         = m.name;
            memberEl.dataset.memberId = m.id;
            memberBox.innerHTML    = '';

            memberDiscount = Number(m.discount || 0);
            document.getElementById('discount_rp').value      = '';
            document.getElementById('discount_percent').value = memberDiscount > 0 ? memberDiscount : '';

            const discPctEl = document.getElementById('discount_percent');
            discPctEl.readOnly = false;
            discPctEl.classList.remove('locked');

            memberInfo.innerHTML = `
                <strong>Nama:</strong> ${m.name}
                <br><strong>Level:</strong> ${m.level}
                <br><strong>Discount:</strong> ${m.discount}%
                <br><strong>Status:</strong> ${m.status}
                <br><strong>Points:</strong> ${m.points}`;

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
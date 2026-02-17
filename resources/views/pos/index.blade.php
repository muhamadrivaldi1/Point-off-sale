@extends('layouts.app')
@section('title','Point of Sale')

@section('content')

<style>
* { box-sizing: border-box; }
body { margin:0; padding:0; font-family: Arial, sans-serif; }

/* ✅ CONTAINER UTAMA */
.pos-wrapper {
    padding: 15px;
    max-width: 100%;
}

/* ✅ HEADER TRANSAKSI AKTIF */
.trx-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px 15px;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 15px;
}

.trx-header .trx-number {
    font-size: 16px;
    font-weight: 700;
    color: #0d6efd;
}

.trx-header .trx-time {
    font-size: 11px;
    color: #6c757d;
}

.trx-header .trx-items {
    margin-left: auto;
    font-size: 11px;
    color: #6c757d;
}

/* ✅ LIVE TIME */
.live-time {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #6c757d;
}

.live-time .pulse {
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* ✅ TABS + BUTTON BARU SEJAJAR */
.tabs-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 15px;
    overflow-x: auto;
    padding: 5px 0;
}

.transaction-tabs {
    display: flex;
    gap: 8px;
    flex: 1;
    overflow-x: auto;
}

.transaction-tab {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
    min-width: 140px;
    transition: all 0.2s;
    font-size: 12px;
    flex-shrink: 0;
}

.transaction-tab:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.transaction-tab.active {
    background: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.transaction-tab .tab-title {
    font-weight: 600;
    font-size: 11px;
    margin-bottom: 3px;
}

.transaction-tab .tab-info {
    font-size: 10px;
    opacity: 0.9;
}

.new-transaction-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 16px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}

.new-transaction-btn:hover {
    background: #218838;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.new-transaction-btn svg {
    width: 18px;
    height: 18px;
    stroke: white;
    stroke-width: 2;
    fill: none;
}

/* ✅ LAYOUT 2 KOLOM */
.pos-container {
    display: flex;
    gap: 15px;
}

.pos-left {
    flex: 0 0 350px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
}

.pos-right {
    flex: 1;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    display: flex;
    flex-direction: column;
}

.pos-box {
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: auto;
    max-height: 350px;
}

.pos-table {
    margin: 0;
}

.pos-table th {
    background: #f5f5f5;
    font-size: 13px;
    position: sticky;
    top: 0;
    z-index: 1;
}

.pos-table td {
    vertical-align: middle;
}

.qty-input {
    width: 70px;
    text-align: center;
}

.unit-select {
    width: 100px;
}

.big-total {
    font-size: 22px;
    font-weight: bold;
}

.locked {
    background: #eee;
    cursor: not-allowed;
}

.member-info {
    font-size: 12px;
    color: #555;
    word-break: break-word;
}

/* ✅ CART FOOTER */
.cart-section {
    flex: 1;
    overflow: auto;
    margin-bottom: 15px;
}

.cart-footer {
    border-top: 2px solid #ddd;
    padding-top: 15px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

/* ✅ RESPONSIVE */
@media (max-width: 1200px) {
    .pos-container {
        flex-direction: column;
    }
    .pos-left {
        flex: 1 1 auto;
    }
}

@media (max-width: 768px) {
    .tabs-wrapper {
        flex-direction: column;
        align-items: stretch;
    }
    .transaction-tabs {
        overflow-x: auto;
    }
    .new-transaction-btn {
        width: 100%;
    }
}
</style>

<div class="pos-wrapper">

{{-- ✅ HEADER TRANSAKSI AKTIF --}}
<div class="trx-header">
    <div>
        <div class="trx-number">{{ $trx->trx_number }}</div>
        <div class="trx-time">
            <span id="trxDate">{{ $trx->created_at->format('d M Y') }}</span> • 
            <span id="trxTime">{{ $trx->created_at->format('H:i:s') }}</span>
        </div>
    </div>
    <div class="live-time">
        <span class="pulse"></span>
        <span id="liveTime">00:00:00</span>
    </div>
    <div class="trx-items">
        Item: <strong>{{ $trx->items->count() }}</strong>
    </div>
</div>

{{-- ✅ TABS + BUTTON BARU (SEJAJAR) --}}
<div class="tabs-wrapper">
    {{-- Tabs Transaksi --}}
    <div class="transaction-tabs">
        @if($pendingTransactions->count() > 0)
            @foreach($pendingTransactions as $pt)
            <div class="transaction-tab {{ $pt['id'] == $trx->id ? 'active' : '' }}" 
                 onclick="switchTransaction({{ $pt['id'] }})">
                <div class="tab-title">{{ $pt['trx_number'] }}</div>
                <div class="tab-info">{{ $pt['item_count'] }} item • Rp {{ number_format($pt['total']) }}</div>
                <div class="tab-info">{{ $pt['created_at']->diffForHumans() }}</div>
            </div>
            @endforeach
        @else
            <div style="color: #6c757d; font-size: 13px; padding: 10px;">
                Belum ada transaksi pending lainnya
            </div>
        @endif
    </div>
    
    {{-- Button Transaksi Baru --}}
    <button class="new-transaction-btn" onclick="createNewTransaction()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Transaksi Baru
    </button>
</div>

{{-- ✅ LAYOUT 2 KOLOM --}}
<div class="pos-container">

    {{-- KOLOM KIRI --}}
    <div class="pos-left">

        <select id="location" class="form-select mb-2">
            <option value="toko">Ambil dari TOKO</option>
            <option value="gudang">Ambil dari GUDANG</option>
        </select>

        <input type="text" id="barcode" class="form-control mb-2" placeholder="Scan barcode lalu Enter">
        <input type="text" id="search" class="form-control mb-2" placeholder="Cari produk">

        {{-- 🔒 MEMBER --}}
        <div class="mb-2">
            <label class="form-label">Member</label>
            <input type="text" id="member" class="form-control locked" placeholder="Klik untuk input member" readonly onclick="unlockMember()">
            <div id="memberResult" class="border mt-1" style="max-height:150px;overflow:auto"></div>
            <div id="memberInfo" class="mt-2 member-info"></div>
        </div>

        {{-- 🔒 DISCOUNT --}}
        <div class="mb-2">
            <label class="form-label">Discount (%)</label>
            <input type="number" id="discount" class="form-control locked" placeholder="Klik untuk input discount" readonly onclick="unlockDiscount()">
        </div>

        <label class="form-label">Hasil Pencarian</label>
        <div class="pos-box">
            <table class="table table-sm pos-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barcode</th>
                        <th>Nama</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody id="searchResult"></tbody>
            </table>
        </div>

    </div>

    {{-- KOLOM KANAN --}}
    <div class="pos-right">

        <div class="cart-section">
            <table class="table table-bordered table-sm pos-table">
                <thead>
                    <tr>
                        <th style="width:30px">*</th>
                        <th>Nama</th>
                        <th style="width:120px">Satuan</th>
                        <th style="width:200px">Qty</th>
                        <th style="width:130px">Subtotal</th>
                    </tr>
                </thead>
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
                            <select class="form-select form-select-sm unit-select" onchange="updateUnit({{ $i->id }},this.value)">
                                @foreach($i->unit->product->units as $u)
                                <option value="{{ $u->id }}" {{ $u->id == $i->product_unit_id ? 'selected':'' }}>
                                    {{ $u->unit_name }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <button class="btn btn-sm btn-outline-secondary" onclick="minusQty({{ $i->id }})">−</button>
                                <input type="number" class="form-control form-control-sm qty-input" value="{{ $i->qty }}" onchange="updateQtyManual({{ $i->id }},this.value)">
                                <button class="btn btn-sm btn-outline-secondary" onclick="plusQty({{ $i->id }})">+</button>
                                <button class="btn btn-sm btn-danger" onclick="removeItem({{ $i->id }})">🗑</button>
                            </div>
                        </td>
                        <td>Rp {{ number_format($sub) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="cart-footer">
            <div class="total-row">
                <span style="font-size:18px;">Total:</span>
                <span class="big-total" id="totalText" data-total="{{ $total }}">Rp {{ number_format($total) }}</span>
            </div>

            <input type="number" id="paid" class="form-control form-control-lg" placeholder="Masukkan jumlah bayar">

            <div class="total-row mt-3">
                <span style="font-size:18px;">Kembali:</span>
                <span id="changeText" class="big-total" style="color:#28a745">Rp 0</span>
            </div>

            <button id="btnPay" class="btn btn-primary btn-lg w-100 mt-3">
                Simpan / Bayar
            </button>
        </div>

    </div>

</div>

</div>

<script>
let TRX = {{ $trx->id }};
const csrf = '{{ csrf_token() }}';
const loc = document.getElementById('location');
const box = document.getElementById('searchResult');

const jsonHeaders = {
  'Content-Type':'application/json',
  'X-CSRF-TOKEN':csrf,
  'Accept':'application/json'
};

let memberUnlocked = false;
let discountUnlocked = false;

// ✅ LIVE TIME COUNTER
const trxCreatedAt = new Date('{{ $trx->created_at->format('Y-m-d H:i:s') }}');

function updateLiveTime() {
    const now = new Date();
    const diff = Math.floor((now - trxCreatedAt) / 1000); // selisih dalam detik
    
    const hours = Math.floor(diff / 3600);
    const minutes = Math.floor((diff % 3600) / 60);
    const seconds = diff % 60;
    
    const timeString = [
        hours.toString().padStart(2, '0'),
        minutes.toString().padStart(2, '0'),
        seconds.toString().padStart(2, '0')
    ].join(':');
    
    document.getElementById('liveTime').textContent = timeString;
}

// Update setiap detik
setInterval(updateLiveTime, 1000);
updateLiveTime(); // Jalankan sekali saat load

// ✅ UPDATE CURRENT TIME (JAM SISTEM)
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: false 
    });
    document.getElementById('trxTime').textContent = timeString;
}

// Update waktu sistem setiap detik
setInterval(updateCurrentTime, 1000);

// ✅ FUNGSI SWITCH TRANSAKSI
function switchTransaction(trxId) {
    window.location.href = `/pos?trx_id=${trxId}`;
}

// ✅ FUNGSI BUAT TRANSAKSI BARU
function createNewTransaction() {
    window.location.href = '/pos?new_transaction=1';
}

// 🔒 UNLOCK MEMBER
function unlockMember(){
  if(memberUnlocked) return;
  const pwd = prompt("Masukkan password owner:");
  fetch(`/pos/override-owner`,{
    method:'POST', headers:jsonHeaders, body:JSON.stringify({ password: pwd })
  }).then(r=>r.json()).then(r=>{
    if(!r.success){ alert("Password salah"); return; }
    memberUnlocked = true;
    document.getElementById('member').readOnly = false;
    document.getElementById('member').classList.remove('locked');
  });
}

// 🔒 UNLOCK DISCOUNT
function unlockDiscount(){
  if(discountUnlocked) return;
  const pwd = prompt("Masukkan password owner:");
  fetch(`/pos/override-owner`,{
    method:'POST', headers:jsonHeaders, body:JSON.stringify({ password: pwd })
  }).then(r=>r.json()).then(r=>{
    if(!r.success){ alert("Password salah"); return; }
    discountUnlocked = true;
    document.getElementById('discount').readOnly = false;
    document.getElementById('discount').classList.remove('locked');
  });
}

// SAVE MEMBER
document.getElementById('member').addEventListener('change', e=>{
  fetch(`/pos/set-member`,{
    method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, member:e.target.value })
  }).then(()=>loadCart());
});

// SAVE DISCOUNT
document.getElementById('discount').addEventListener('change', e=>{
  fetch(`/pos/set-discount`,{
    method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, discount:e.target.value })
  }).then(()=>loadCart());
});

// SEARCH PRODUCT
document.getElementById('search').addEventListener('keyup', e => {
  const q = e.target.value;
  if(q.length < 2){ box.innerHTML=''; return; }
  fetch(`/pos/search?q=${q}&location=${loc.value}`)
  .then(r=>r.json())
  .then(items=>{
    box.innerHTML='';
    let no = 1;
    items.forEach(p=>{
      box.innerHTML += `<tr style="cursor:pointer" onclick="add(${p.id})">
      <td>${no++}</td><td>${p.barcode ?? '-'}</td><td>${p.name}</td><td>${p.unit}</td><td>${p.stock}</td></tr>`;
    });
  });
});

// ADD ITEM
function add(id, overridePassword=null){
  fetch(`/pos/add-item`,{
    method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, product_unit_id:id, location:loc.value, override_password:overridePassword })
  }).then(r=>r.json()).then(r=>{
    if(r.need_override){
      const pwd = prompt("Stok tidak cukup!\nMasukkan password owner:"); if(!pwd) return;
      add(id,pwd); return;
    }
    if(!r.success){ alert(r.message); return; }
    loadCart();
  });
}

// LOAD CART
function loadCart(){
  fetch(`/pos?trx_id=${TRX}`).then(r=>r.text()).then(html=>{
    const doc = new DOMParser().parseFromString(html,'text/html');
    document.querySelector('#cartBody').innerHTML = doc.querySelector('#cartBody').innerHTML;

    let totalEl = doc.querySelector('#totalText');
    let total = Number(totalEl.dataset.total);
    const discount = Number(document.getElementById('discount').value || 0);
    if(discount>0) total = total - (total*discount/100);
    document.getElementById('totalText').innerText = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('totalText').dataset.total = total;
    updateKembalian();
  });
}

// QTY
function plusQty(id){ updateQtyManual(id,getQty(id)+1); }
function minusQty(id){ updateQtyManual(id,Math.max(getQty(id)-1,1)); }
function getQty(id){ return Number(document.querySelector(`input[onchange="updateQtyManual(${id},this.value)"]`).value); }
function updateQtyManual(itemId,qty,overridePassword=null){
  fetch(`/pos/update-qty-manual`,{
    method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, item_id:itemId, qty:qty, location:loc.value, override_password:overridePassword })
  }).then(r=>r.json()).then(r=>{
    if(r.need_override){ const pwd=prompt("Stok tidak cukup!\nMasukkan password owner:"); if(!pwd) return; updateQtyManual(itemId,qty,pwd); return; }
    loadCart();
  });
}

// REMOVE
function removeItem(itemId){ if(!confirm('Hapus item ini?')) return;
  fetch(`/pos/remove-item`,{ method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, item_id:itemId }) }).then(()=>loadCart());
}

// KEMBALIAN
document.getElementById('paid').addEventListener('input', updateKembalian);
function updateKembalian(){
  const total = Number(document.getElementById('totalText').dataset.total);
  const bayar = Number(document.getElementById('paid').value || 0);
  document.getElementById('changeText').innerText = 'Rp ' + Math.max(bayar - total,0).toLocaleString('id-ID');
}

// BAYAR / SIMPAN
document.getElementById('btnPay').addEventListener('click', async () => {
    const total = Number(document.getElementById('totalText').dataset.total);
    const bayar = Number(document.getElementById('paid').value || 0);
    const memberId = document.getElementById('member').dataset.memberId || null;

    const strukWindow = window.open('', '_blank');

    try {
        const res = await fetch('/pos/pay', {
            method: 'POST',
            headers: jsonHeaders,
            body: JSON.stringify({
                trx_id: TRX,
                paid: bayar,
                member_id: memberId
                // ❌ HAPUS used_points karena poin hanya untuk hadiah
            })
        });
        const r = await res.json();

        if(r.success){
            if(r.paid_off){
                alert('Transaksi lunas! Kembalian: Rp ' + Math.max(bayar - total,0).toLocaleString('id-ID'));
                strukWindow.location.href = `/transactions/${r.trx_id}/struk`;
                setTimeout(() => {
                    window.location.href = '/pos?new_transaction=1';
                }, 500);
            } else {
                alert('Transaksi pending, total tersisa: Rp ' + (total - bayar).toLocaleString('id-ID'));
                strukWindow.close();
            }
        } else {
            alert(r.message || 'Gagal menyimpan transaksi');
            strukWindow.close();
        }
    } catch(e){
        alert('Terjadi error: '+e.message);
        strukWindow.close();
    }
});

// MEMBER SEARCH
const memberBox=document.getElementById('memberResult');
const memberInfo=document.getElementById('memberInfo');
document.getElementById('member').addEventListener('keyup', e=>{
  if(!memberUnlocked) return;
  const q=e.target.value; if(q.length<2){ memberBox.innerHTML=''; return; }
  fetch(`/pos/search-member?q=${q}`).then(r=>r.json()).then(items=>{
    memberBox.innerHTML='';
    items.forEach(m=>{
      memberBox.innerHTML += `<div class="p-2 border-bottom" style="cursor:pointer" onclick="selectMember(${m.id})">
      <strong>${m.name}</strong><br><small class="text-muted">${m.phone} | ${m.address}</small></div>`;
    });
  });
});

function selectMember(id){
  fetch(`/pos/get-member?id=${id}`).then(r=>r.json()).then(m=>{
    const memberEl = document.getElementById('member');
    memberEl.value=m.name;
    memberEl.dataset.memberId = m.id;
    memberBox.innerHTML='';
    memberInfo.innerHTML=`<strong>ID:</strong> ${m.id}<br><strong>Nama:</strong> ${m.name}<br><strong>Phone:</strong> ${m.phone}<br><strong>Address:</strong> ${m.address}<br><strong>Level:</strong> ${m.level}<br><strong>Discount:</strong> ${m.discount}%<br><strong>Total Spent:</strong> Rp ${Number(m.total_spent).toLocaleString('id-ID')}<br><strong>Status:</strong> ${m.status}<br><strong>Points:</strong> ${m.points} (Untuk Tukar Hadiah)`;
    fetch(`/pos/set-member`,{ method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, member_id:m.id }) }).then(()=>loadCart());
    if(m.discount>0){ 
        document.getElementById('discount').value=m.discount; 
        fetch(`/pos/set-discount`,{ method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, discount:m.discount }) }).then(()=>loadCart()); 
    }
  });
}
</script>

@endsection
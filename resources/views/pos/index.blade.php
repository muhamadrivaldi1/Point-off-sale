@extends('layouts.app')

@section('title','Point of Sale')

@section('content')
<div class="row">

    {{-- ================= LEFT ================= --}}
    <div class="col-md-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                Scan / Cari Produk
            </div>
            <div class="card-body">

                {{-- STATUS --}}
                <span class="badge mb-2
                    @if($trx->status==='pending') bg-warning text-dark
                    @elseif($trx->status==='paid') bg-success
                    @else bg-danger @endif">
                    Status: {{ strtoupper($trx->status) }}
                </span>

                {{-- MEMBER --}}
                <div class="mb-3">
                    <label class="form-label">Member</label>
                    <select id="memberSelect" class="form-select" {{ $trx->status!=='pending' ? 'disabled' : '' }}>
                        <option value="">-- Tidak Ada --</option>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}" data-points="{{ $m->points }}"
                                {{ $trx->member_id==$m->id ? 'selected' : '' }}>
                                {{ $m->name }} | Poin: {{ $m->points }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GUNAKAN POIN --}}
                <div class="mb-3">
                    <label class="form-label">Gunakan Poin</label>
                    <input type="number" id="usePoints" class="form-control"
                           placeholder="Masukkan jumlah poin yang ingin digunakan"
                           min="0" max="{{ $trx->member?->points ?? 0 }}"
                           {{ $trx->status!=='pending' ? 'disabled' : '' }}>
                    <small class="text-muted">Member memiliki: {{ $trx->member?->points ?? 0 }} poin</small>
                </div>

                {{-- LOCATION --}}
                <div class="mb-3">
                    <label class="form-label">Ambil Stok Dari</label>
                    <select id="location" class="form-select" {{ $trx->status!=='pending' ? 'disabled' : '' }}>
                        <option value="toko" {{ $trx->items->first()?->location === 'toko' ? 'selected' : '' }}>Toko</option>
                        <option value="gudang" {{ $trx->items->first()?->location === 'gudang' ? 'selected' : '' }}>Gudang</option>
                    </select>
                </div>

                {{-- BARCODE --}}
                <input type="text" id="barcode" class="form-control mb-2"
                    placeholder="Scan barcode lalu Enter"
                    {{ $trx->status!=='pending' ? 'disabled' : '' }}>

                {{-- SEARCH --}}
                <input type="text" id="search" class="form-control"
                    placeholder="Cari produk (min 2 huruf)"
                    {{ $trx->status!=='pending' ? 'disabled' : '' }}>

                <div id="searchResult" class="list-group small mt-2"></div>

            </div>
        </div>
    </div>

    {{-- ================= RIGHT ================= --}}
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                Keranjang
            </div>
            <div class="card-body">

                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th width="120">Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        @php $total=0; @endphp
                        @forelse($trx->items as $i)
                            @php
                                $sub = ($i->price - ($i->discount ?? 0)) * $i->qty;
                                $total += $sub;
                            @endphp
                            <tr id="item-{{ $i->id }}">
                                <td>{{ $i->unit->product->name }} ({{ $i->unit->unit_name }})</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger" onclick="qty({{ $i->id }},'minus')" {{ $trx->status!=='pending'?'disabled':'' }}>−</button>
                                    {{ $i->qty }}
                                    <button class="btn btn-sm btn-success" onclick="qty({{ $i->id }},'plus')" {{ $trx->status!=='pending'?'disabled':'' }}>+</button>
                                </td>
                                <td>Rp {{ number_format($i->price) }}</td>
                                <td>Rp {{ number_format($sub) }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteItem({{ $i->id }})" title="Hapus" {{ $trx->status!=='pending'?'disabled':'' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada item</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total</span>
                    <strong id="totalText" data-total="{{ $total }}">Rp {{ number_format($total) }}</strong>
                </div>

                <input type="number" id="paid" class="form-control mb-2" placeholder="Jumlah bayar (boleh kosong / 0)">

                <div class="d-flex justify-content-between mb-3">
                    <span>Kembalian</span>
                    <strong id="changeText">Rp 0</strong>
                </div>

                @if($trx->status==='pending')
                    <div class="d-grid gap-2">
                        <button id="btnPay" class="btn btn-primary">Simpan / Bayar</button>
                        <button class="btn btn-outline-danger" onclick="cancel()">Cancel</button>
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>

{{-- ================= SCRIPT ================= --}}
<script>
const TRX = {{ $trx->id }};
const csrf = '{{ csrf_token() }}';
const box = document.getElementById('searchResult');
const loc = document.getElementById('location');
const paid = document.getElementById('paid');
const totalEl = document.getElementById('totalText');
const usePointsEl = document.getElementById('usePoints');
const memberSelect = document.getElementById('memberSelect');
const pointValue = 1000; // 1 poin = Rp 1000

const jsonHeaders = {
    'Content-Type':'application/json',
    'X-CSRF-TOKEN':csrf,
    'Accept':'application/json'
};

// ===== SEARCH =====
document.getElementById('search').addEventListener('keyup', e => {
    const query = e.target.value;
    if(query.length < 2){ box.innerHTML = ''; return; }

    fetch(`{{ route('pos.search') }}?q=${query}&location=${loc.value}`, {headers: {'Accept':'application/json'}})
        .then(res => res.json())
        .then(items => {
            box.innerHTML = '';
            items.forEach(p => {
                box.innerHTML += `
<button class="list-group-item list-group-item-action d-flex justify-content-between align-items-start"
onclick="add(${p.id})">
    <div>
        <strong>${p.name}</strong><br>
        <small>Stok: ${p.stock} | Harga: Rp ${Number(p.price).toLocaleString()}</small>
    </div>
</button>`;
            });
        })
        .catch(err => { console.error(err); });
});

// ===== BARCODE =====
document.getElementById('barcode').addEventListener('keydown', e => {
    if(e.key !== 'Enter') return;

    fetch("{{ route('pos.scan') }}", {
        method: 'POST',
        headers: jsonHeaders,
        body: JSON.stringify({code: e.target.value, location: loc.value})
    })
    .then(res => res.json())
    .then(r => {
        if(!r.success){ alert(r.message || 'Produk tidak ditemukan'); return; }
        add(r.id);
    })
    .catch(err => { console.error(err); alert('Gagal scan barcode'); });

    e.target.value = '';
});

// ===== ADD ITEM =====
function add(id){
    fetch("{{ route('pos.addItem') }}", {
        method:'POST',
        headers: jsonHeaders,
        body:JSON.stringify({trx_id:TRX, product_unit_id:id, location:loc.value})
    })
    .then(r => r.json())
    .then(r => {
        if(!r.success){
            if(r.need_override) {
                const pw = prompt('Stok kurang! Masukkan password owner/admin:');
                if(pw){
                    overrideAdd(id, pw);
                }
            } else alert(r.message || 'Gagal menambahkan item');
            return;
        }
        loadCart();
    })
    .catch(err => { console.error(err); alert(err.message || 'Gagal menambahkan item'); });
}

// ===== OVERRIDE ADD =====
function overrideAdd(id, password){
    fetch("{{ route('pos.addItem') }}", {
        method:'POST',
        headers: jsonHeaders,
        body:JSON.stringify({trx_id:TRX, product_unit_id:id, location:loc.value, override_password: password})
    })
    .then(r => r.json())
    .then(r => {
        if(!r.success){ alert(r.message || 'Password salah atau gagal menambahkan item'); return; }
        loadCart();
    })
    .catch(err => { console.error(err); alert('Gagal override stok'); });
}

// ===== UPDATE QTY =====
function qty(id, type){
    fetch("{{ route('pos.updateQty') }}", {
        method:'POST',
        headers: jsonHeaders,
        body:JSON.stringify({trx_id:TRX, item_id:id, type:type, location:loc.value})
    })
    .then(r => r.json())
    .then(r => {
        if(!r.success){ 
            if(r.message?.includes('override')){
                const pw = prompt('Stok kurang! Masukkan password owner/admin:');
                if(pw) overrideQty(id, type, pw);
            } else alert(r.message || 'Gagal update qty'); 
            return;
        }
        loadCart();
    })
    .catch(err => { console.error(err); alert(err.message || 'Gagal update qty'); });
}

// ===== OVERRIDE QTY =====
function overrideQty(id, type, password){
    fetch("{{ route('pos.updateQty') }}", {
        method:'POST',
        headers: jsonHeaders,
        body:JSON.stringify({trx_id:TRX, item_id:id, type:type, location:loc.value, override_password: password})
    })
    .then(r => r.json())
    .then(r => { if(r.success) loadCart(); else alert(r.message || 'Gagal override qty'); })
    .catch(err => { console.error(err); alert('Gagal override qty'); });
}

// ===== DELETE ITEM =====
function deleteItem(id){
    if(!confirm('Yakin ingin hapus item ini?')) return;

    fetch("{{ route('pos.updateQty') }}", {
        method:'POST',
        headers: jsonHeaders,
        body: JSON.stringify({trx_id:TRX, item_id:id, type:'delete', location:loc.value})
    })
    .then(r => r.json())
    .then(r => {
        if(r.success) loadCart();
        else alert(r.message || 'Gagal hapus item');
    })
    .catch(err => { console.error(err); alert('Gagal hapus item'); });
}

// ===== LOAD CART =====
function loadCart(){
    fetch("{{ route('pos') }}?trx_id="+TRX)
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html,'text/html');
            const newBody = doc.querySelector('#cartBody');
            if(newBody){
                document.querySelector('#cartBody').innerHTML = newBody.innerHTML;

                const newTotal = doc.querySelector('#totalText').dataset.total;
                totalEl.dataset.total = newTotal;
                totalEl.innerText = doc.querySelector('#totalText').innerText;

                const selected = memberSelect.selectedOptions[0];
                usePointsEl.max = selected?.dataset.points || 0;
                usePointsEl.value = 0;
                document.querySelector('small.text-muted').innerText = `Member memiliki: ${usePointsEl.max} poin`;
                updateKembalian();
            } else console.error('loadCart failed: #cartBody not found');
        })
        .catch(err => { console.error(err); alert('Gagal load cart'); });
}

// ===== MEMBER CHANGE =====
memberSelect?.addEventListener('change', () => {
    const selectedOption = memberSelect.selectedOptions[0];
    const points = selectedOption?.dataset.points || 0;
    usePointsEl.max = points;
    usePointsEl.value = 0;
    document.querySelector('small.text-muted').innerText = `Member memiliki: ${points} poin`;
    updateKembalian();
});

// ===== TOTAL & KEMBALIAN =====
function updateTotalAfterPoints() {
    const points = Number(usePointsEl.value || 0);
    const total = Number(totalEl.dataset.total);
    const discount = points * pointValue;
    return Math.max(total - discount, 0);
}

function updateKembalian() {
    const newTotal = updateTotalAfterPoints();
    const bayar = Number(paid.value || 0);
    document.getElementById('changeText').innerText = 'Rp ' + Math.max(bayar - newTotal, 0).toLocaleString();
}

usePointsEl?.addEventListener('input', updateKembalian);
paid.addEventListener('input', updateKembalian);

// ===== PAY =====
document.getElementById('btnPay')?.addEventListener('click', () => {
    fetch("{{ route('pos.pay') }}", {
        method:'POST',
        headers: jsonHeaders,
        body:JSON.stringify({
            trx_id:TRX,
            paid:Number(paid.value||0),
            member_id:memberSelect.value || null,
            used_points: Number(usePointsEl.value||0)
        })
    })
    .then(r => r.json())
    .then(r => {
        if(!r.success){ alert(r.message); return; }
        if(r.paid_off) window.location.href = `/transactions/${r.trx_id}/struk`;
        else {
            alert('Transaksi disimpan sebagai PENDING');
            loadCart();
        }
    })
    .catch(err => { console.error(err); alert('Gagal melakukan pembayaran'); });
});

// ===== CANCEL =====
function cancel(){
    if(!confirm('Batalkan transaksi?')) return;

    fetch("{{ route('pos.cancel') }}", {
        method:'POST',
        headers: jsonHeaders
    })
    .then(() => loadCart())
    .catch(err => { console.error(err); alert('Gagal membatalkan transaksi'); });
}
</script>
@endsection

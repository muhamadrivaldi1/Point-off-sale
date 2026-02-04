@extends('layouts.app')

@section('title','Point of Sale')

@section('content')
<div class="row">

    <!-- LEFT: Scan / Cari Produk -->
    <div class="col-md-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                Scan / Cari Produk
            </div>
            <div class="card-body">

                {{-- Scan Barcode --}}
                <div class="mb-3">
                    <label class="form-label">Scan Barcode</label>
                    <input type="text"
                           id="barcode"
                           class="form-control"
                           placeholder="Scan barcode lalu Enter"
                           autofocus>
                </div>

                {{-- Cari Produk --}}
                <div class="mb-3">
                    <label class="form-label">Cari Manual</label>
                    <input type="text"
                           id="search"
                           class="form-control"
                           placeholder="Ketik nama produk">
                </div>

                {{-- Hasil Search --}}
                <div id="searchResult" class="list-group small"></div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Keranjang & Bayar -->
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                Keranjang Belanja
            </div>
            <div class="card-body">

                {{-- Daftar Member --}}
                <div class="mb-2">
                    <label class="form-label">Member</label>
                    <select id="member_id" class="form-select">
                        <option value="">-- Tidak Ada --</option>
                        @foreach(App\Models\Member::all() as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }} ({{ $member->points }} pts)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tabel Keranjang --}}
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th width="110">Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trx->items as $item)
                        <tr>
                            <td>{{ $item->unit->product->name }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-danger"
                                        onclick="updateQty({{ $item->id }},'minus')">−</button>
                                    <span class="btn btn-light">{{ $item->qty }}</span>
                                    <button class="btn btn-outline-success"
                                        onclick="updateQty({{ $item->id }},'plus')">+</button>
                                </div>
                            </td>
                            <td>Rp {{ number_format($item->price) }}</td>
                            <td>Rp {{ number_format($item->subtotal) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Belum ada barang
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <hr>

                {{-- Voucher Demo --}}
                <div class="mb-2">
                    <label class="form-label">Voucher Diskon</label>
                    <input type="text"
                           id="voucher"
                           class="form-control"
                           placeholder="Contoh: DISKON10">
                    <small class="text-muted">
                        * Demo: DISKON10 = potong 10%
                    </small>
                </div>

                {{-- Total --}}
                <div class="d-flex justify-content-between mb-1">
                    <span>Total</span>
                    <strong id="totalText">Rp {{ number_format($trx->total) }}</strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>Setelah Diskon</span>
                    <strong id="finalTotal">Rp {{ number_format($trx->total) }}</strong>
                </div>

                {{-- Bayar --}}
                <div>
                    <label class="form-label">Jumlah Bayar</label>
                    <input type="number"
                           id="paid"
                           class="form-control mb-2"
                           min="0"
                           {{ $trx->items->isEmpty() ? 'disabled' : '' }}>

                    <form method="POST" action="{{ route('pos.pay') }}">
                        @csrf
                        <input type="hidden" name="paid" id="paidHidden">
                        <input type="hidden" name="member_id" id="memberHidden">

                        <button type="submit"
                                class="btn btn-primary w-100"
                                {{ $trx->items->isEmpty() ? 'disabled' : '' }}>
                            Bayar
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
const csrf = '{{ csrf_token() }}'
const resultBox = document.getElementById('searchResult')
const baseTotal = {{ $trx->total }}

// SCAN BARCODE
document.getElementById('barcode').addEventListener('keypress', e => {
    if(e.key==='Enter'){
        resultBox.innerHTML=''
        fetch("{{ route('pos.scan') }}", {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body: JSON.stringify({ barcode:e.target.value })
        })
        .then(r=>r.json())
        .then(showResult)
        .catch(()=>alert('Produk tidak ditemukan'))
        e.target.value=''
    }
})

// SEARCH
document.getElementById('search').addEventListener('keyup', e => {
    const q = e.target.value
    if(q.length<2) return
    fetch(`{{ route('pos.search') }}?q=${q}`)
        .then(r=>r.json())
        .then(list=>{
            resultBox.innerHTML=''
            list.forEach(showResult)
        })
})

// SHOW RESULT
function showResult(data){
    let stok = data.stocks.map(s=>
        `<span class="badge bg-secondary me-1">${s.location}: ${s.qty}</span>`
    ).join('')
    resultBox.innerHTML += `
        <button type="button" class="list-group-item list-group-item-action"
            onclick="addItem(${data.id})">
            <div class="fw-bold">${data.name}</div>
            <small>Rp ${Number(data.price).toLocaleString()}</small>
            <div class="mt-1">${stok}</div>
        </button>
    `
}

// ADD ITEM
function addItem(id){
    fetch("{{ route('pos.addItem') }}", {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({ product_unit_id:id })
    }).then(()=>location.reload())
}

// UPDATE QTY
function updateQty(itemId,type){
    fetch("{{ route('pos.updateQty') }}", {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({ item_id:itemId, type:type })
    }).then(()=>location.reload())
}

// VOUCHER DEMO
document.getElementById('voucher').addEventListener('keyup', e=>{
    let total = baseTotal
    if(e.target.value.toUpperCase() === 'DISKON10'){
        total = total - (total*0.1)
    }
    document.getElementById('finalTotal').innerText =
        'Rp ' + Math.round(total).toLocaleString()
})

// SYNC PAID
document.getElementById('paid')?.addEventListener('input', e=>{
    document.getElementById('paidHidden').value = e.target.value
})

// SYNC MEMBER
document.getElementById('member_id')?.addEventListener('change', e=>{
    document.getElementById('memberHidden').value = e.target.value
})

// SUCCESS ALERT
@if(session('success'))
alert("Transaksi berhasil!\nNo Invoice: {{ session('invoice') }}")
window.location.href = "{{ route('transactions.index') }}"
@endif
</script>
@endsection

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

            <div class="mb-3">
                <label class="form-label">Ambil Stok Dari</label>
                <select id="location" class="form-select">
                    <option value="toko">Toko</option>
                    <option value="gudang">Gudang</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Scan Barcode</label>
                <input type="text" id="barcode" class="form-control"
                       placeholder="Scan barcode lalu Enter">
            </div>

            <div class="mb-3">
                <label class="form-label">Cari Produk</label>
                <input type="text" id="search" class="form-control"
                       placeholder="Ketik nama produk">
            </div>

            <div id="searchResult" class="list-group small"></div>
        </div>
    </div>
</div>

{{-- ================= RIGHT ================= --}}
<div class="col-md-5">
<div class="card shadow-sm">
<div class="card-header bg-success text-white">
    Keranjang Belanja
</div>
<div class="card-body">

<table class="table table-sm align-middle">
<thead>
<tr>
    <th>Produk</th>
    <th width="120">Qty</th>
    <th>Harga</th>
    <th>Subtotal</th>
</tr>
</thead>
<tbody>
@forelse($trx->items as $item)
<tr>
<td>
    {{ $item->unit->product->name }}<br>
    <small class="text-muted">{{ $item->unit->unit_name }}</small>
</td>
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
    Belum ada item
</td>
</tr>
@endforelse
</tbody>
</table>

<hr>

{{-- TOTAL --}}
<div class="d-flex justify-content-between mb-1">
    <span>Total</span>
    <strong id="totalText"
        data-total="{{ $trx->total }}">
        Rp {{ number_format($trx->total) }}
    </strong>
</div>

{{-- DISKON --}}
<div class="mb-2">
    <label class="form-label">Diskon (Rp)</label>
    <input type="number" id="discount"
           class="form-control"
           value="0"
           {{ $trx->items->isEmpty() ? 'disabled' : '' }}>
</div>

{{-- GRAND TOTAL --}}
<div class="d-flex justify-content-between mb-2">
    <span>Grand Total</span>
    <strong id="grandText">Rp {{ number_format($trx->total) }}</strong>
</div>

{{-- BAYAR --}}
<input type="number"
       id="paid"
       class="form-control mb-2"
       placeholder="Jumlah bayar"
       {{ $trx->items->isEmpty() ? 'disabled' : '' }}>

{{-- KEMBALIAN --}}
<div class="d-flex justify-content-between mb-3">
    <span>Kembalian</span>
    <strong id="changeText">Rp 0</strong>
</div>

<input type="hidden" id="paidHidden">
<input type="hidden" id="discountHidden">
<input type="hidden" id="changeHidden">

<button id="btnPay"
    class="btn btn-primary w-100"
    {{ $trx->items->isEmpty() ? 'disabled' : '' }}>
    Bayar
</button>

</div>
</div>
</div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
const csrf = '{{ csrf_token() }}'
const box  = document.getElementById('searchResult')
const loc  = document.getElementById('location')

const total    = Number(document.getElementById('totalText').dataset.total)
const discount = document.getElementById('discount')
const paid     = document.getElementById('paid')

function hitung(){
    let disc  = Number(discount.value || 0)
    let grand = Math.max(total - disc, 0)
    let bayar = Number(paid.value || 0)
    let change = Math.max(bayar - grand, 0)

    document.getElementById('grandText').innerText =
        'Rp ' + grand.toLocaleString()

    document.getElementById('changeText').innerText =
        'Rp ' + change.toLocaleString()

    document.getElementById('paidHidden').value = bayar
    document.getElementById('discountHidden').value = disc
    document.getElementById('changeHidden').value = change
}

discount?.addEventListener('input', hitung)
paid?.addEventListener('input', hitung)

// SCAN
document.getElementById('barcode').addEventListener('keypress', e=>{
if(e.key === 'Enter'){
fetch("{{ route('pos.scan') }}",{
method:'POST',
headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
body:JSON.stringify({barcode:e.target.value,location:loc.value})
})
.then(r=>r.json()).then(show)
e.target.value=''
}})

// SEARCH
document.getElementById('search').addEventListener('keyup', e=>{
if(e.target.value.length < 2) return
fetch(`{{ route('pos.search') }}?q=${e.target.value}&location=${loc.value}`)
.then(r=>r.json()).then(list=>{
box.innerHTML=''
list.forEach(show)
})
})

// SHOW
function show(d){
box.innerHTML += `
<button type="button" class="list-group-item list-group-item-action"
onclick="addItem(${d.id})">
<strong>${d.name}</strong><br>
Rp ${Number(d.price).toLocaleString()}
<span class="float-end text-muted">Stok: ${d.stock}</span>
</button>`
}

// ADD
function addItem(id){
fetch("{{ route('pos.addItem') }}",{
method:'POST',
headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
body:JSON.stringify({product_unit_id:id,location:loc.value})
}).then(()=>location.reload())
}

// UPDATE QTY
function updateQty(id,type){
fetch("{{ route('pos.updateQty') }}",{
method:'POST',
headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
body:JSON.stringify({item_id:id,type:type,location:loc.value})
}).then(()=>location.reload())
}

document.getElementById('btnPay')?.addEventListener('click', function () {

    const bayar  = Number(document.getElementById('paidHidden').value)
    const disc   = Number(document.getElementById('discountHidden').value)
    const change = Number(document.getElementById('changeHidden').value)

    if (bayar <= 0) {
        alert('Jumlah bayar belum diisi')
        return
    }

    fetch("{{ route('pos.pay') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({
            paid: bayar,
            discount: disc,
            change: change
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            alert(
                `Pembayaran berhasil!\n` +
                `ID Transaksi : ${res.trx_id}\n` +
                `Invoice       : ${res.trx_number}`
            )

            // 👉 redirect ke struk (opsional)
            window.location.href =
                `/transactions/${res.trx_id}/struk`
        } else {
            alert('Pembayaran gagal')
        }
    })
    .catch(err => {
        console.error(err)
        alert('Terjadi kesalahan server')
    })
})

</script>
@endsection

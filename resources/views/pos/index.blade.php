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
                    <label class="form-label">Scan Barcode</label>
                    <input type="text"
                           id="barcode"
                           class="form-control"
                           placeholder="Scan barcode lalu Enter"
                           autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cari Produk</label>
                    <input type="text"
                           id="search"
                           class="form-control"
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

                {{-- MEMBER --}}
                <div class="mb-3">
                    <label class="form-label">Member</label>
                    <select id="member_id" class="form-select">
                        <option value="">-- Non Member --</option>
                        @foreach(App\Models\Member::all() as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }} ({{ $member->points }} pts)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- CART --}}
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
                                <small class="text-muted">
                                    {{ $item->unit->unit_name }}
                                </small>
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
                            <td colspan="4"
                                class="text-center text-muted">
                                Belum ada item
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <strong>Total</strong>
                    <strong>Rp {{ number_format($trx->total) }}</strong>
                </div>

                {{-- BAYAR --}}
                <input type="number"
                       id="paid"
                       class="form-control mb-2"
                       placeholder="Jumlah bayar"
                       {{ $trx->items->isEmpty() ? 'disabled' : '' }}>

                <form method="POST" action="{{ route('pos.pay') }}">
                    @csrf
                    <input type="hidden" name="paid" id="paidHidden">
                    <input type="hidden" name="member_id" id="memberHidden">

                    <button class="btn btn-primary w-100"
                        {{ $trx->items->isEmpty() ? 'disabled' : '' }}>
                        Bayar
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL SUCCESS ================= --}}
@if(session()->has('success'))
<div class="modal fade"
     id="successModal"
     tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    Transaksi Berhasil
                </h5>
            </div>
            <div class="modal-body">
                <p class="mb-1">
                    Transaksi berhasil disimpan.
                </p>
                <p class="mb-0">
                    <strong>Invoice:</strong>
                    {{ session('invoice') ?? '-' }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-success"
                        id="btnOk">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ================= SCRIPT ================= --}}
<script>
const csrf = '{{ csrf_token() }}'
const box  = document.getElementById('searchResult')

// SCAN BARCODE
document.getElementById('barcode').addEventListener('keypress', e=>{
    if(e.key === 'Enter'){
        fetch("{{ route('pos.scan') }}",{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':csrf
            },
            body:JSON.stringify({ barcode:e.target.value })
        })
        .then(r=>r.json())
        .then(show)
        .catch(()=>alert('Produk tidak ditemukan'))

        e.target.value=''
    }
})

// SEARCH
document.getElementById('search').addEventListener('keyup', e=>{
    if(e.target.value.length < 2) return

    fetch(`{{ route('pos.search') }}?q=${e.target.value}`)
        .then(r=>r.json())
        .then(list=>{
            box.innerHTML=''
            list.forEach(show)
        })
})

// SHOW RESULT
function show(d){
    box.innerHTML += `
        <button type="button"
            class="list-group-item list-group-item-action"
            onclick="addItem(${d.id})">
            <strong>${d.name}</strong><br>
            Rp ${Number(d.price).toLocaleString()}
        </button>
    `
}

// ADD ITEM
function addItem(id){
    fetch("{{ route('pos.addItem') }}",{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':csrf
        },
        body:JSON.stringify({ product_unit_id:id })
    }).then(()=>location.reload())
}

// UPDATE QTY
function updateQty(id,type){
    fetch("{{ route('pos.updateQty') }}",{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':csrf
        },
        body:JSON.stringify({ item_id:id, type:type })
    }).then(()=>location.reload())
}

// SYNC INPUT
document.getElementById('paid')?.addEventListener('input', e=>{
    document.getElementById('paidHidden').value = e.target.value
})

document.getElementById('member_id')?.addEventListener('change', e=>{
    document.getElementById('memberHidden').value = e.target.value
})

// SHOW MODAL SUCCESS
@if(session()->has('success'))
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Transaksi Berhasil</h5>
            </div>
            <div class="modal-body">
                <p><strong>No Invoice:</strong> {{ session('invoice') }}</p>
                <p><strong>Total:</strong> Rp {{ number_format(session('total')) }}</p>
                <p><strong>Bayar:</strong> Rp {{ number_format(session('paid')) }}</p>
                <p><strong>Kembalian:</strong> Rp {{ number_format(session('change')) }}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnOk">
                    OK & Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@if(session()->has('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('successModal'))
    modal.show()

    document.getElementById('btnOk').onclick = function () {
        window.open("{{ route('transactions.struk', session('trx_id')) }}", "_blank")
        window.location.href = "{{ route('pos') }}"
    }
})
</script>
@endif
</script>
@endsection

@extends('layouts.app')

@section('content')

<div style="max-width:900px;margin:auto;font-family:Arial, Helvetica, sans-serif">

<h2 style="margin-bottom:20px">Detail Kredit</h2>

{{-- ALERT --}}
@if(session('success'))

<div id="alert-success" style="background:#d1fae5;color:#065f46;padding:12px;border-radius:6px;margin-bottom:20px">
{{ session('success') }}
</div>

@endif

@if(session('error'))

<div style="background:#fee2e2;color:#7f1d1d;padding:12px;border-radius:6px;margin-bottom:20px">
{{ session('error') }}
</div>
@endif

{{-- CARD INFO --}}

<div style="background:#f9fafb;border:1px solid #e5e7eb;padding:20px;border-radius:10px;margin-bottom:25px">

<div style="display:flex;justify-content:space-between;margin-bottom:8px">
<strong>Transaksi</strong>
<span>{{ $trx->trx_number }}</span>
</div>

<div style="display:flex;justify-content:space-between;margin-bottom:8px">
<strong>Member</strong>
<span>{{ $trx->member->name ?? '-' }}</span>
</div>

<hr style="margin:15px 0">

<div style="display:flex;justify-content:space-between;margin-bottom:6px">
<strong>Total</strong>
<span>Rp {{ number_format($trx->total) }}</span>
</div>

<div style="display:flex;justify-content:space-between;margin-bottom:6px">
<strong>Total Terbayar</strong>
<span style="color:#16a34a">
Rp {{ number_format($totalTerbayar) }}
</span>
</div>

<div style="display:flex;justify-content:space-between;font-size:18px">
<strong>Sisa Hutang</strong>
<span style="color:#dc2626;font-weight:bold">
Rp {{ number_format($sisa) }}
</span>
</div>

</div>

{{-- FORM CICILAN --}}
@if($sisa > 0)

<div style="background:white;border:1px solid #e5e7eb;padding:20px;border-radius:10px;margin-bottom:25px">

<h3 style="margin-bottom:15px">Bayar Cicilan</h3>

<form method="POST" action="{{ route('pos.kredit.partial') }}">
@csrf

<input type="hidden" name="trx_id" value="{{ $trx->id }}">

<div style="margin-bottom:12px">
<label>Jumlah Bayar</label>
<input 
type="number" 
name="amount" 
max="{{ $sisa }}" 
required
style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px">
</div>

<div style="margin-bottom:12px">
<label>Metode Pembayaran</label>
<select 
name="method"
style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px">
<option value="cash">Cash</option>
<option value="transfer">Transfer</option>
<option value="qris">QRIS</option>
</select>
</div>

<div style="margin-bottom:12px">
<label>Password Owner</label>
<input 
type="password" 
name="password" 
required
style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px">
</div>

<button 
type="submit"
style="background:#2563eb;color:white;border:none;padding:10px 16px;border-radius:6px;cursor:pointer">
Bayar Cicilan </button>

</form>

</div>

@endif

{{-- RIWAYAT PEMBAYARAN --}}

<div style="background:white;border:1px solid #e5e7eb;padding:20px;border-radius:10px">

<h3 style="margin-bottom:15px">Riwayat Pembayaran</h3>

<table style="width:100%;border-collapse:collapse">

<thead style="background:#f3f4f6">
<tr>
<th style="padding:10px;text-align:left">Hai & Tanggal</th>
<th style="padding:10px;text-align:left">Jumlah</th>
<th style="padding:10px;text-align:left">Metode</th>
</tr>
</thead>

<tbody>

@forelse($trx->payments as $p)

<tr style="border-bottom:1px solid #eee">

<td style="padding:10px">
{{ \Carbon\Carbon::parse($p->paid_at)->locale('id')->translatedFormat('l, d M Y H:i') }}
</td>

<td style="padding:10px">
Rp {{ number_format($p->amount) }}
</td>

<td style="padding:10px;text-transform:capitalize">
{{ $p->method }}
</td>

</tr>

@empty

<tr>
<td colspan="3" style="padding:15px;text-align:center;color:#888">
Belum ada pembayaran
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>

@endsection

<script>

document.addEventListener("DOMContentLoaded", function () {

    let alert = document.getElementById("alert-success");

    if(alert){

        setTimeout(function(){

            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";

            setTimeout(function(){
                alert.remove();
            },500);

        },3000); // 3 detik

    }

});

</script>

@extends('layouts.app')

@section('title', 'Struk ' . $trx->trx_number)

@section('content')

@php $s = \App\Models\StrukSetting::getSetting(); @endphp

<style>
.struk{
max-width:220px;
margin:auto;
padding:8px;
background:#fff;
font-family:monospace;
font-size:11px;
box-shadow:0 0 8px rgba(0,0,0,.15);
text-align:left;
}

@page{ size:58mm auto; margin:0 }

@media print{
body *{ visibility:hidden }
.struk,.struk *{ visibility:visible }

.struk{
position:absolute;
left:50%;
top:0;
transform:translateX(-50%);
width:58mm;
max-width:58mm;
padding:6px;
font-size:10px;
box-shadow:none
}

.d-print-none{ display:none!important }
}

.text-center{text-align:center}
.text-end{text-align:right}

hr{
border-top:1px dashed #000;
margin:4px 0
}

.kredit-box{
border:1px solid #000;
padding:4px 5px;
margin:4px 0;
text-align:center;
font-weight:bold;
font-size:11px;
letter-spacing:.3px
}

.footer{
display:flex;
justify-content:space-between;
margin-top:4px
}

.footer .left{text-align:left}
.footer .right{text-align:right}
</style>


<div class="struk">

{{-- HEADER --}}
<div class="text-center">

<strong>{{ strtoupper($s->nama_toko) }}</strong><br>

@if($s->tagline){{ $s->tagline }}<br>@endif
@if($s->alamat){{ $s->alamat }}<br>@endif
@if($s->kota){{ strtoupper($s->kota) }}<br>@endif

@if($s->tampil_npwp && $s->npwp)
NPWP: {{ $s->npwp }}<br>
@endif

@if($s->telepon)
HP. {{ $s->telepon }}<br>
@endif

@if($s->email)
{{ $s->email }}<br>
@endif

@if($s->website)
{{ $s->website }}<br>
@endif

</div>

<hr>

{{-- INFO TRANSAKSI --}}
@php

$isKredit = $trx->payment_method === 'kredit';

$metodeLabel = match($trx->payment_method) {
'transfer' => 'Transfer Bank',
'qris' => 'QRIS',
'kredit' => 'KREDIT',
default => 'Cash / Tunai',
};

@endphp

<div>

No  : {{ $trx->trx_number }}<br>

Tgl : {{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}<br>

Kasir: {{ $trx->user->name ?? auth()->user()->name }}<br>

@if($s->tampil_member && $trx->member)
Member: {{ $trx->member->name }}<br>
Level : {{ $trx->member->level }}<br>
@endif

Bayar : {{ $metodeLabel }}<br>

@if($isKredit)
Status: *** BELUM LUNAS ***<br>
@endif

</div>

<hr>

{{-- ITEM --}}
@php
$subtotalBersih = 0;
@endphp

<table style="width:100%">

@foreach($trx->items as $item)

@php
$harga = $item->price;
$qty = $item->qty;
$sub = $harga * $qty;
$subtotalBersih += $sub;
@endphp

<tr>
<td colspan="3">
{{ $item->unit->product->name }}
<small>({{ $item->unit->unit_name }})</small>
</td>
</tr>

<tr>
<td>{{ $qty }} x</td>
<td class="text-end">{{ number_format($harga) }}</td>
<td class="text-end">{{ number_format($sub) }}</td>
</tr>

@endforeach

</table>

<hr>

{{-- RINGKASAN --}}
@php

$diskon = $trx->discount ?? 0;

$total = max($subtotalBersih - $diskon,0);

$sudahBayar = $trx->paid ?? 0;

$sisa = max($total - $sudahBayar,0);

@endphp

<table style="width:100%">

<tr>
<td>Subtotal</td>
<td class="text-end">{{ number_format($subtotalBersih) }}</td>
</tr>

@if($diskon>0)
<tr>
<td>Diskon</td>
<td class="text-end">-{{ number_format($diskon) }}</td>
</tr>
@endif

<tr>
<td><strong>Total</strong></td>
<td class="text-end"><strong>{{ number_format($total) }}</strong></td>
</tr>

@if($isKredit)

<tr>
<td>Dibayar</td>
<td class="text-end">{{ number_format($sudahBayar) }}</td>
</tr>

<tr>
<td><strong>Sisa Hutang</strong></td>
<td class="text-end"><strong>{{ number_format($sisa) }}</strong></td>
</tr>

@else

<tr>
<td>Dibayar</td>
<td class="text-end">{{ number_format($sudahBayar) }}</td>
</tr>

<tr>
<td>Kembali</td>
<td class="text-end">{{ number_format($trx->change ?? 0) }}</td>
</tr>

@endif

</table>


{{-- NOTA KREDIT --}}
@if($isKredit)

<hr>

<div class="kredit-box">

*** NOTA KREDIT / HUTANG ***<br>

{{ $s->teks_kredit ?? 'Harap dilunasi secepatnya' }}<br>

Total: Rp {{ number_format($total) }}

</div>

@endif


{{-- POIN --}}
@if($s->tampil_poin && $trx->member && !$isKredit)

<hr>

<div>

Poin Didapat : +{{ floor($subtotalBersih/10000) }} pts<br>

Total Poin   : {{ $trx->member->points }} pts

</div>

@endif


{{-- FOOTER TEXT --}}
@if(($s->tampil_footer_text ?? true) && $s->footer_text)

<hr>

<div class="text-center" style="font-size:10px;white-space:pre-line">

{{ $s->footer_text }}

</div>

@endif


{{-- TANDA TANGAN --}}
@if($s->tampil_footer_ttd)

<hr><br>

<div class="footer">

<div class="left">
{{ $s->label_tanda_terima ?? 'Tanda Terima' }}
</div>

<div class="right">
{{ $s->label_hormat_kami ?? 'Hormat Kami' }}
</div>

</div>

<br><br>

<small class="text-center" style="display:block;margin-top:2px">

{{ strtoupper($s->nama_toko) }}<br>

{{ strtoupper($s->kota) }}

</small>

@endif

</div>


<div class="text-center mt-3 d-print-none">

<button onclick="window.print()" class="btn btn-primary btn-sm">
🖨️ Print Struk
</button>

<button onclick="window.close()" class="btn btn-secondary btn-sm ms-2">
✕ Tutup
</button>

</div>


<script>

window.onload=function(){

window.print();

document.body.focus();

document.body.addEventListener('keydown',function(e){

if(e.key==='Enter'||e.key==='Escape'){
window.close();
}

});

}

</script>

@endsection
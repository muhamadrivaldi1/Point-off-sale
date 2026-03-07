<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Struk Kredit</title>

<style>

body{
font-family: monospace;
width:300px;
margin:auto;
}

.center{
text-align:center;
}

hr{
border-top:1px dashed #000;
}

</style>
</head>

<body onload="window.print()">

<div class="center">
<h3>TOKO ANDA</h3>
<p>Struk Pelunasan Kredit</p>
</div>

<hr>

<p>No Transaksi : {{ $trx->trx_number }}</p>
<p>Member       : {{ $trx->member->name ?? '-' }}</p>
<p>Tanggal      : {{ now()->format('d-m-Y H:i') }}</p>

<hr>

<p>Total        : Rp {{ number_format($trx->total) }}</p>

@php
$paid = $trx->payments->sum('amount');
@endphp

<p>Terbayar     : Rp {{ number_format($paid) }}</p>

<hr>

<p><strong>Status : LUNAS</strong></p>

<hr>

<div class="center">
Terima Kasih
<br>
Sudah Berbelanja
</div>

</body>
</html>
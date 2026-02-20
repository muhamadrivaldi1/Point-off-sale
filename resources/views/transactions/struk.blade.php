@extends('layouts.app')

@section('title', 'Struk ' . $trx->trx_number)

@section('content')

<style>
/* ================= TAMPILAN DI LAYAR ================= */
.struk {
    max-width: 220px;
    margin: auto;
    padding: 8px;
    background: #fff;
    font-family: monospace;
    font-size: 11px;
    box-shadow: 0 0 8px rgba(0,0,0,.15);
}

/* ================= PRINT ================= */
@page {
    size: 58mm auto;
    margin: 0;
}

@media print {
    body * { visibility: hidden; }
    .struk, .struk * { visibility: visible; }
    .struk {
        position: absolute;
        left: 0;
        top: 0;
        width: 58mm;
        max-width: 58mm;
        padding: 6px;
        font-size: 10px;
        box-shadow: none;
    }
    .d-print-none { display: none !important; }
}
.text-center { text-align: center; }
.text-end { text-align: right; }
hr { border-top: 1px dashed #000; margin: 4px 0; }
</style>

<div class="struk">

    {{-- HEADER --}}
    <div class="text-center">
        <strong>MINIMARKET MAJU JAYA</strong><br>
        Jl. Contoh No. 123<br>
        Telp: 0851-8322-7741
    </div>

    <hr>

    {{-- INFO TRANSAKSI --}}
    <div>
        No&nbsp;&nbsp;: {{ $trx->trx_number }}<br>
        Tgl&nbsp;&nbsp;: {{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}<br>
        Kasir: {{ $trx->user->name ?? auth()->user()->name }}<br>
        @if($trx->member)
            Member: {{ $trx->member->name }}<br>
            Level : {{ $trx->member->level }}<br>
        @endif
        @if(!empty($trx->payment_method))
            Bayar : {{ $trx->payment_method === 'transfer' ? 'Transfer' : 'Cash' }}<br>
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
                $hargaSatuan = $item->price;
                $qty         = $item->qty;
                $subtotalItem = $hargaSatuan * $qty;
                $subtotalBersih += $subtotalItem;
            @endphp
            <tr>
                <td colspan="3">{{ $item->unit->product->name }}
                    <small>({{ $item->unit->unit_name }})</small>
                </td>
            </tr>
            <tr>
                <td>{{ $qty }} x</td>
                <td class="text-end">{{ number_format($hargaSatuan) }}</td>
                <td class="text-end">{{ number_format($subtotalItem) }}</td>
            </tr>
        @endforeach
    </table>

    <hr>

    {{-- RINGKASAN PEMBAYARAN --}}
    @php
        // Hitung diskon persen dari data transaksi
        // $trx->discount = nilai rupiah diskon yang disimpan di DB
        $diskonRupiah = $trx->discount ?? 0;

        // Hitung % diskon dari subtotal
        $diskonPersen = 0;
        if($subtotalBersih > 0 && $diskonRupiah > 0){
            $diskonPersen = round(($diskonRupiah / $subtotalBersih) * 100, 2);
        }

        // Jika ada member dengan diskon, pakai diskon member sebagai acuan %
        if($trx->member && $trx->member->discount > 0 && $diskonRupiah > 0){
            $diskonPersen = $trx->member->discount;
        }

        $totalBayar = $subtotalBersih - $diskonRupiah;
        if($totalBayar < 0) $totalBayar = 0;
    @endphp

    <table style="width:100%">
        <tr>
            <td>Subtotal</td>
            <td class="text-end">{{ number_format($subtotalBersih) }}</td>
        </tr>

        @if($diskonRupiah > 0)
        <tr>
            <td>
                Diskon
                @if($diskonPersen > 0)
                    ({{ $diskonPersen }}%)
                @endif
                @if($trx->member && $trx->member->discount > 0)
                    <small>[Member {{ $trx->member->level }}]</small>
                @endif
            </td>
            <td class="text-end">-{{ number_format($diskonRupiah) }}</td>
        </tr>
        @endif

        <tr>
            <td><strong>Total Bayar</strong></td>
            <td class="text-end"><strong>{{ number_format($totalBayar) }}</strong></td>
        </tr>

        <tr>
            <td>Bayar</td>
            <td class="text-end">{{ number_format($trx->paid) }}</td>
        </tr>

        <tr>
            <td>Kembali</td>
            <td class="text-end">{{ number_format(max($trx->change, 0)) }}</td>
        </tr>
    </table>

    @if($trx->member)
    <hr>
    <div>
        Poin Didapat : +{{ floor($subtotalBersih / 10000) }} pts<br>
        Total Poin&nbsp;&nbsp;&nbsp;: {{ $trx->member->points }} pts
    </div>
    @endif

    <hr>

    {{-- FOOTER --}}
    <div class="text-center">
        TERIMA KASIH 🙏<br>
        ATAS KUNJUNGAN ANDA<br>
        <small>
            BARANG YANG SUDAH DIBELI<br>
            TIDAK DAPAT DITUKAR / DIKEMBALIKAN
        </small>
    </div>

</div>

{{-- TOMBOL PRINT --}}
<div class="text-center mt-3 d-print-none">
    <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Print Struk</button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm ms-2">✕ Tutup</button>
</div>

<script>
    // Auto print saat halaman dibuka
    window.onload = function(){ window.print(); }
</script>

@endsection
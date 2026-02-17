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
hr { border-top:1px dashed #000; }
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
        No: {{ $trx->trx_number }}<br>
        Tgl: {{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}<br>
        Kasir: {{ auth()->user()->name }}<br>
        @if($trx->member)
            Member: {{ $trx->member->name }}<br>
            Poin Digunakan: {{ $trx->used_points ?? 0 }}
        @endif
    </div>

    <hr>

    {{-- ITEM --}}
    <table style="width:100%">
        @php 
            $totalDiskonItem = 0;
        @endphp

        @foreach($trx->items as $item)
            @php
                $diskonItem = ($item->discount ?? 0) * $item->qty;
                $totalDiskonItem += $diskonItem;
            @endphp

            <tr>
                <td colspan="3">{{ $item->unit->product->name }}</td>
            </tr>
            <tr>
                <td>{{ $item->qty }} x</td>
                <td class="text-end">{{ number_format($item->price) }}</td>
                <td class="text-end">{{ number_format($item->price * $item->qty) }}</td>
            </tr>

            @if($diskonItem > 0)
            <tr>
                <td colspan="2">Diskon Item</td>
                <td class="text-end">-{{ number_format($diskonItem) }}</td>
            </tr>
            @endif
        @endforeach
    </table>

    {{-- DISKON POIN MEMBER --}}
    @php $pointDiscount = 0; @endphp
    @if($trx->member && ($trx->used_points ?? 0) > 0)
        @php $pointDiscount = ($trx->used_points ?? 0) * 1000; @endphp
        <table style="width:100%">
            <tr>
                <td>Diskon Poin ({{ $trx->used_points }} pts)</td>
                <td class="text-end">-{{ number_format($pointDiscount) }}</td>
            </tr>
        </table>
    @endif

    <hr>

    {{-- TOTAL BAYAR --}}
    @php
        $totalSebelumDiskon = $trx->total + $totalDiskonItem + $pointDiscount;
        $totalBayar = $totalSebelumDiskon - $totalDiskonItem - $pointDiscount;
    @endphp

    <table style="width:100%">
        <tr>
            <td>Total Sebelum Diskon</td>
            <td class="text-end">{{ number_format($totalSebelumDiskon) }}</td>
        </tr>

        @if($totalDiskonItem > 0)
        <tr>
            <td>Diskon Item</td>
            <td class="text-end">-{{ number_format($totalDiskonItem) }}</td>
        </tr>
        @endif

        @if($pointDiscount > 0)
        <tr>
            <td>Diskon Poin</td>
            <td class="text-end">-{{ number_format($pointDiscount) }}</td>
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
            <td class="text-end">{{ number_format($trx->change) }}</td>
        </tr>
    </table>

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

@endsection
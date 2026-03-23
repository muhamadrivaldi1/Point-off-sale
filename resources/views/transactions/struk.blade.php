@extends('layouts.app')

@section('title', 'Struk ' . $trx->trx_number)

@section('content')

@php
    $s = \App\Models\StrukSetting::getSetting();
    
    // Logika Perhitungan Pembayaran
    $isKredit = $trx->payment_method === 'kredit' || $trx->status === 'kredit';
    
    // Ambil total yang sudah dibayar (DP + Cicilan)
    $totalSudahMasuk = $trx->payments ? $trx->payments->sum('amount') : ($trx->paid ?? 0);
    
    // Sisa Hutang adalah Total Akhir dikurangi yang sudah masuk
    $sisaHutang = max($trx->total - $totalSudahMasuk, 0);
@endphp

<style>
.struk {
    max-width: 220px;
    margin: auto;
    padding: 10px;
    background: #fff;
    font-family: monospace;
    font-size: 11px;
    box-shadow: 0 0 8px rgba(0,0,0,.15);
    text-align: left;
    color: #000;
}

@page { size: 58mm auto; margin: 0 }

@media print {
    body * { visibility: hidden }
    .struk, .struk * { visibility: visible }
    .struk {
        position: absolute;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        width: 58mm;
        max-width: 58mm;
        padding: 5px;
        font-size: 10px;
        box-shadow: none;
    }
    .d-print-none { display: none !important }
}

.text-center { text-align: center }
.text-end { text-align: right }

hr {
    border-top: 1px dashed #000;
    margin: 5px 0;
}

.kredit-box {
    border: 1px solid #000;
    padding: 5px;
    margin: 8px 0;
    text-align: center;
    font-weight: bold;
    font-size: 10px;
}

.footer {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

table { width: 100%; border-collapse: collapse; }
td { vertical-align: top; }
</style>

<div class="struk">

    {{-- ================= HEADER ================= --}}
    <div class="text-center">
        <strong>{{ strtoupper($s->nama_toko) }}</strong><br>
        @if($s->tagline) {{ $s->tagline }}<br> @endif
        @if($s->alamat) {{ $s->alamat }}<br> @endif
        @if($s->kota) {{ strtoupper($s->kota) }}<br> @endif
        @if($s->tampil_npwp && $s->npwp) NPWP: {{ $s->npwp }}<br> @endif
        @if($s->telepon) HP. {{ $s->telepon }}<br> @endif
        @if($s->email) {{ $s->email }}<br> @endif
        @if($s->website) {{ $s->website }}<br> @endif
    </div>

    <hr>

    {{-- INFO TRANSAKSI --}}
    @php
    $metodeLable = match($trx->payment_method) {
        'transfer' => 'Transfer Bank',
        'qris'     => 'QRIS',
        'kredit'   => 'KREDIT',
        default    => 'Cash / Tunai',
    };
    @endphp

    <div>
        No  : {{ $trx->trx_number }}<br>
        Tgl : {{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}<br>
        Kasir: {{ $trx->user->name ?? auth()->user()->name }}<br>

        {{-- ✅ FIX: Tampilkan member ATAU nama pembeli biasa --}}
        @if($trx->member)
            Member : {{ $trx->member->name }}<br>
        @elseif(!empty($trx->buyer_name))
            Pembeli: {{ $trx->buyer_name }}<br>
        @endif

        Bayar : <strong>{{ strtoupper($metodeLable) }}</strong><br>

        @if($isKredit && $sisaHutang > 0)
            Status: <strong>*** BELUM LUNAS ***</strong><br>
        @elseif($isKredit && $sisaHutang <= 0)
            Status: <strong>*** LUNAS ***</strong><br>
        @endif
    </div>

    <hr>

    {{-- DAFTAR ITEM --}}
    <table>
        @foreach($trx->items as $item)
        <tr>
            <td colspan="3">{{ $item->unit->product->name }} <small>({{ $item->unit->unit_name }})</small></td>
        </tr>
        <tr>
            <td style="width: 25%">{{ $item->qty }} x</td>
            <td class="text-end">{{ number_format($item->price) }}</td>
            <td class="text-end">{{ number_format($item->price * $item->qty) }}</td>
        </tr>
        @endforeach
    </table>

    <hr>

    {{-- RINGKASAN PEMBAYARAN --}}
    <table style="width:100%">
        <tr>
            <td>Subtotal</td>
            <td class="text-end">{{ number_format($trx->total + ($trx->discount ?? 0)) }}</td>
        </tr>

        @if(($trx->discount ?? 0) > 0)
        <tr>
            <td>Diskon</td>
            <td class="text-end">-{{ number_format($trx->discount) }}</td>
        </tr>
        @endif

        <tr>
            <td><strong>Total</strong></td>
            <td class="text-end"><strong>{{ number_format($trx->total) }}</strong></td>
        </tr>

        @if($isKredit)
            <tr>
                <td>DP/Tlh Bayar</td>
                <td class="text-end">{{ number_format($totalSudahMasuk) }}</td>
            </tr>
            <tr>
                <td><strong>Sisa Hutang</strong></td>
                <td class="text-end"><strong>{{ number_format($sisaHutang) }}</strong></td>
            </tr>
        @else
            <tr>
                <td>Dibayar</td>
                <td class="text-end">{{ number_format($trx->paid ?? $trx->total) }}</td>
            </tr>
            <tr>
                <td>Kembali</td>
                <td class="text-end">{{ number_format($trx->change ?? 0) }}</td>
            </tr>
        @endif
    </table>

    {{-- KOTAK KREDIT --}}
    @if($isKredit && $sisaHutang > 0)
        <hr>
        <div class="kredit-box">
            *** NOTA KREDIT / HUTANG ***<br>
            {{ $s->teks_kredit ?? 'Harap dilunasi secepatnya' }}<br>
            {{-- PERBAIKAN DI SINI: Menampilkan sisa hutang, bukan total belanja --}}
            Sisa Tagihan: Rp {{ number_format($sisaHutang) }}
        </div>
    @endif

    <hr>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <div class="text-center" style="width: 45%">
            <small>{{ $s->label_tanda_terima ?? 'Tanda Terima' }}</small><br><br><br>
            ( ....... )
        </div>
        <div class="text-center" style="width: 45%">
            <small>{{ $s->label_hormat_kami ?? 'Hormat Kami' }}</small><br><br><br>
            ( {{ strtoupper($s->nama_toko) }} )
        </div>
    </div>

    <div class="text-center" style="margin-top: 15px;">
        <small><strong>{{ strtoupper($s->nama_toko) }}</strong></small><br>
        <small>{{ strtoupper($s->kota) }}</small>
    </div>

</div>

<div class="text-center mt-3 d-print-none">
    {{-- <button onclick="window.print()" class="btn btn-primary btn-sm">
        🖨️ Print Struk
    </button> --}}

    <button id="btnClose" class="btn btn-secondary btn-sm ms-2">
        ✕ Tutup
    </button>
</div>

<script>
window.onload = function() {
    window.print();

    const handleClose = () => {
        window.close();
        setTimeout(() => {
            if (!window.closed) {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    alert("Gunakan tombol 'Kembali' atau tekan 'Esc' untuk menutup tab.");
                }
            }
        }, 500);
    };

    const btnClose = document.getElementById('btnClose');
    if (btnClose) {
        btnClose.addEventListener('click', function(e) {
            e.preventDefault();
            handleClose();
        });
    }

    document.body.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === 'Escape') {
            e.preventDefault();
            handleClose();
        }
    });
}
</script>
@endsection
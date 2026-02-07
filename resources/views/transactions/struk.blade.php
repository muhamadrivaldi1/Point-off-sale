<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk {{ $trx->trx_number }}</title>

<style>
/* === SET UKURAN KERTAS STRUK === */
@page {
    size: 58mm auto;
    margin: 0;
}

/* === RESET === */
* {
    box-sizing: border-box;
}

body {
    font-family: monospace;
    font-size: 10px;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
}

/* === STRUK CONTAINER === */
.struk {
    width: 58mm;
    padding: 6px 4px;
}

/* === ALIGN === */
.center {
    text-align: center;
}

.right {
    text-align: right;
}

/* === LINE === */
.line {
    border-top: 1px dashed #000;
    margin: 5px 0;
}

/* === TABLE === */
table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 1px 0;
    vertical-align: top;
    word-break: break-word;
}

/* === PRINT ONLY === */
@media screen {
    body {
        background: #f2f2f2;
    }

    .struk {
        background: #fff;
    }
}
</style>
</head>

<body onload="autoPrint()">

<div class="struk">

    <!-- HEADER TOKO -->
    <div class="center">
        <strong>MINIMARKET MAJU JAYA</strong><br>
        Jl. Contoh No. 123<br>
        Telp: 0851-8322-7741
    </div>

    <div class="line"></div>

    <!-- INFO TRANSAKSI -->
    <div>
        No  : {{ $trx->trx_number }}<br>
        Tgl : {{ $trx->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}<br>
        Kasir : {{ auth()->user()->name }}
    </div>

    <div class="line"></div>

    <!-- ITEM -->
    <table>
        @foreach($trx->items as $item)
        <tr>
            <td colspan="3">
                {{ $item->unit->product->name }}
            </td>
        </tr>
        <tr>
            <td>{{ $item->qty }} x</td>
            <td class="right">{{ number_format($item->price) }}</td>
            <td class="right">{{ number_format($item->subtotal) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <!-- TOTAL -->
    <table>
        <tr>
            <td>Total</td>
            <td class="right">{{ number_format($trx->total) }}</td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td class="right">{{ number_format($trx->paid) }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="right">{{ number_format($trx->change) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- FOOTER -->
    <div class="center">
        TERIMA KASIH 🙏<br>
        ATAS KUNJUNGAN ANDA<br><br>
        <small>
            BARANG YANG SUDAH DIBELI<br>
            TIDAK DAPAT DITUKAR / DIKEMBALIKAN<br>
        </small>
    </div>

</div>

<script>
function autoPrint(){
    window.print();

    setTimeout(() => {
        window.close();
        // atau redirect otomatis:
        // window.location.href = "/pos";
    }, 500);
}
</script>

</body>
</html>

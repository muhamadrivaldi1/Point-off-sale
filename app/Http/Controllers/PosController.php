<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class PosController extends Controller
{
    /* ===============================
       HALAMAN POS
    =============================== */
    public function index()
    {
        return view('pos.index');
    }

    /* ===============================
       MULAI TRANSAKSI BARU
    =============================== */
    public function start()
    {
        $trx = Transaction::create([
            'trx_number' => 'TRX-' . now()->format('YmdHis'),
            'user_id' => Auth::id(),
            'total' => 0,
            'status' => 'pending'
        ]);

        return response()->json($trx);
    }

    /* ===============================
       SCAN BARCODE
    =============================== */
    public function scan(Request $request)
    {
        $unit = ProductUnit::with(['product','priceRules','stock'])
            ->where('barcode', $request->barcode)
            ->first();

        if (!$unit) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        $stokToko = $unit->stock
            ->where('location', 'toko')
            ->sum('qty');

        return response()->json([
            'unit' => $unit,
            'stok' => $stokToko
        ]);
    }

    /* ===============================
       HITUNG HARGA BERTINGKAT
    =============================== */
    private function getPriceByQty(ProductUnit $unit, int $qty)
    {
        return $unit->priceRules()
            ->where('min_qty', '<=', $qty)
            ->orderBy('min_qty', 'desc')
            ->value('price') ?? $unit->price;
    }

    /* ===============================
       TAMBAH ITEM KE TRANSAKSI
    =============================== */
    public function addItem(Request $request)
    {
        $trx = Transaction::findOrFail($request->transaction_id);
        $unit = ProductUnit::with('stock','priceRules')->findOrFail($request->product_unit_id);

        $stokToko = $unit->stock
            ->where('location','toko')
            ->sum('qty');

        // stok habis → perlu ACC
        if ($stokToko < $request->qty && !session('stock_override')) {
            return response()->json([
                'need_approval' => true,
                'message' => 'Stok tidak cukup, perlu ACC supervisor'
            ], 403);
        }

        $price = $this->getPriceByQty($unit, $request->qty);

        $item = TransactionItem::create([
            'transaction_id' => $trx->id,
            'product_unit_id' => $unit->id,
            'qty' => $request->qty,
            'price' => $price,
            'subtotal' => $price * $request->qty,
            'verified' => false
        ]);

        $trx->update([
            'total' => $trx->items()->sum('subtotal')
        ]);

        return response()->json($item);
    }

    /* ===============================
       VERIFIKASI BARANG
    =============================== */
    public function verify(Request $request)
    {
        TransactionItem::whereIn('id', $request->items)
            ->update(['verified' => true]);

        return response()->json(['success' => true]);
    }

    /* ===============================
       PEMBAYARAN
    =============================== */
    public function pay(Request $request)
    {
        DB::transaction(function () use ($request) {
            $trx = Transaction::with('items')->findOrFail($request->transaction_id);

            foreach ($trx->items as $item) {
                Stock::where('product_unit_id', $item->product_unit_id)
                    ->where('location', 'toko')
                    ->decrement('qty', $item->qty);
            }

            $trx->update([
                'paid' => $request->paid,
                'change' => $request->paid - $trx->total,
                'status' => 'paid'
            ]);
        });

        session()->forget('stock_override');

        return response()->json(['success' => true]);
    }
}

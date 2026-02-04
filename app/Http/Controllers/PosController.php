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
        $trx = Transaction::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'status'  => 'pending'
            ],
            [
                'trx_number' => 'TRX-' . now()->format('YmdHis'),
                'total'  => 0,
                'paid'   => 0,
                'change' => 0
            ]
        );

        $trx->load('items.unit.product');

        return view('pos.index', compact('trx'));
    }

    /* ===============================
       SCAN BARCODE
    =============================== */
    public function scan(Request $request)
    {
        $request->validate(['barcode' => 'required']);

        $unit = ProductUnit::with('product', 'stock')
            ->where('barcode', $request->barcode)
            ->firstOrFail();

        return response()->json([
            'id'     => $unit->id,
            'name'   => $unit->product->name,
            'price'  => $unit->price,
            'stocks' => $unit->stock->map(fn($s) => [
                'location' => $s->location,
                'qty' => $s->qty
            ])
        ]);
    }

    /* ===============================
       SEARCH MANUAL
    =============================== */
    public function search(Request $request)
    {
        return ProductUnit::with('product', 'stock')
            ->whereHas(
                'product',
                fn($q) =>
                $q->where('name', 'like', '%' . $request->q . '%')
            )
            ->limit(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->product->name,
                'price' => $u->price,
                'stocks' => $u->stock->map(fn($s) => [
                    'location' => $s->location,
                    'qty' => $s->qty
                ])
            ]);
    }

    /* ===============================
       ADD ITEM
    =============================== */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id'
        ]);

        $trx = Transaction::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $unit = ProductUnit::with('stock')->findOrFail($request->product_unit_id);

        $stokToko = $unit->stock()
            ->where('location', 'toko')
            ->sum('qty');

        $item = TransactionItem::firstOrCreate(
            [
                'transaction_id' => $trx->id,
                'product_unit_id' => $unit->id
            ],
            [
                'qty' => 0,
                'price' => $unit->price,
                'subtotal' => 0
            ]
        );

        if ($item->qty + 1 > $stokToko) {
            abort(422, 'Stok tidak cukup');
        }

        $item->qty++;
        $item->subtotal = $item->qty * $item->price;
        $item->save();

        $trx->update([
            'total' => $trx->items()->sum('subtotal')
        ]);

        return response()->json(['success' => true]);
    }

    /* ===============================
       UPDATE QTY (+ / -)
    =============================== */
    public function updateQty(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:transaction_items,id',
            'type' => 'required|in:plus,minus'
        ]);

        $item = TransactionItem::with('unit.stock')->findOrFail($request->item_id);
        $trx  = Transaction::where('id', $item->transaction_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $stokToko = $item->unit->stock()
            ->where('location', 'toko')
            ->sum('qty');

        if ($request->type === 'plus') {
            if ($item->qty + 1 > $stokToko) {
                abort(422, 'Stok tidak cukup');
            }
            $item->qty++;
        } else {
            $item->qty--;
            if ($item->qty <= 0) {
                $item->delete();
                $trx->update(['total' => $trx->items()->sum('subtotal')]);
                return response()->json(['success' => true]);
            }
        }

        $item->update([
            'subtotal' => $item->qty * $item->price
        ]);

        $trx->update([
            'total' => $trx->items()->sum('subtotal')
        ]);

        return response()->json(['success' => true]);
    }

    /* ===============================
       PAY
    =============================== */
    public function pay(Request $request)
    {
        $request->validate([
            'paid' => 'required|numeric|min:0'
        ]);

        $invoice = null;

        DB::transaction(function () use ($request, &$invoice) {

            $trx = Transaction::with('items')
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($trx->items->isEmpty(), 422, 'Keranjang kosong');

            foreach ($trx->items as $item) {
                Stock::where('product_unit_id', $item->product_unit_id)
                    ->where('location', 'toko')
                    ->decrement('qty', $item->qty);
            }

            // generate invoice
            $invoice = 'INV-' . now()->format('YmdHis');

            $trx->update([
                'paid'   => $request->paid,
                'change' => $request->paid - $trx->total,
                'status' => 'paid',
                'invoice' => $invoice
            ]);

            if ($request->member_id) {
                $member = \App\Models\Member::find($request->member_id);
                if ($member) {
                    $earnedPoints = floor($trx->total / 1000);
                    $member->increment('points', $earnedPoints);
                }
            }
        });
        return redirect()
            ->route('pos')
            ->with('success', true)
            ->with('invoice', $invoice);
    }
}

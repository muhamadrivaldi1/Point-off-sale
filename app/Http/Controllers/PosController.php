<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Stock;
use App\Models\PriceRule;
use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if($user->role === 'kasir'){
            $session = CashierSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if(!$session){
                return view('pos.open_session'); // blade untuk input saldo awal
            }
        }

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

    public function openSession(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        CashierSession::create([
            'user_id' => Auth::id(),
            'opening_balance' => $request->opening_balance,
            'status' => 'open',
            'opened_at' => now()
        ]);

        return redirect()->route('pos.index')
            ->with('success', 'Sesi kasir dibuka');
    }

    private function resolvePrice(ProductUnit $unit, int $qty): int
    {
        $rule = PriceRule::where('product_unit_id', $unit->id)
            ->where('min_qty', '<=', $qty)
            ->orderByDesc('min_qty')
            ->first();

        return $rule ? $rule->price : $unit->price;
    }

    public function scan(Request $request)
    {
        $request->validate([
            'barcode'  => 'required',
            'location' => 'required|in:gudang,toko'
        ]);

        $unit = ProductUnit::with('product')
            ->where('barcode', $request->barcode)
            ->firstOrFail();

        $stock = Stock::where('product_unit_id', $unit->id)
            ->where('location', $request->location)
            ->value('qty') ?? 0;

        return response()->json([
            'id'       => $unit->id,
            'name'     => $unit->product->name,
            'price'    => $unit->price,
            'stock'    => $stock,
            'location' => $request->location
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q'        => 'required',
            'location' => 'required|in:gudang,toko'
        ]);

        return ProductUnit::with('product')
            ->whereHas(
                'product',
                fn($q) => $q->where('name', 'like', "%{$request->q}%")
            )
            ->limit(5)
            ->get()
            ->map(function ($u) use ($request) {
                $stock = Stock::where('product_unit_id', $u->id)
                    ->where('location', $request->location)
                    ->value('qty') ?? 0;

                return [
                    'id'       => $u->id,
                    'name'     => $u->product->name,
                    'price'    => $u->price,
                    'stock'    => $stock,
                    'location' => $request->location
                ];
            });
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'location'        => 'required|in:gudang,toko'
        ]);

        $trx = Transaction::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $unit = ProductUnit::findOrFail($request->product_unit_id);

        $stok = Stock::where('product_unit_id', $unit->id)
            ->where('location', $request->location)
            ->value('qty') ?? 0;

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

        if ($item->qty + 1 > $stok) abort(422,'Stok tidak cukup');

        $item->qty++;
        $item->price = $this->resolvePrice($unit, $item->qty);
        $item->subtotal = $item->qty * $item->price;
        $item->save();

        $trx->update(['total' => $trx->items()->sum('subtotal')]);

        return response()->json(['success' => true]);
    }

    public function updateQty(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|exists:transaction_items,id',
            'type'     => 'required|in:plus,minus',
            'location' => 'required|in:gudang,toko'
        ]);

        $item = TransactionItem::with('unit')->findOrFail($request->item_id);
        $trx  = Transaction::where('id', $item->transaction_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $stok = Stock::where('product_unit_id', $item->product_unit_id)
            ->where('location', $request->location)
            ->value('qty') ?? 0;

        if ($request->type === 'plus') {
            if ($item->qty + 1 > $stok) abort(422,'Stok tidak cukup');
            $item->qty++;
        } else {
            $item->qty--;
            if ($item->qty <= 0){
                $item->delete();
                $trx->update(['total' => $trx->items()->sum('subtotal')]);
                return response()->json(['success'=>true]);
            }
        }

        $item->price = $this->resolvePrice($item->unit, $item->qty);
        $item->subtotal = $item->qty * $item->price;
        $item->save();

        $trx->update(['total' => $trx->items()->sum('subtotal')]);

        return response()->json(['success' => true]);
    }

    public function pay(Request $request)
    {
        $request->validate(['paid'=>'required|numeric|min:0']);

        $trx = DB::transaction(function() use ($request){

            $trx = Transaction::with('items')
                ->where('user_id', Auth::id())
                ->where('status','pending')
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($trx->items->isEmpty(),422,'Keranjang kosong');

            foreach($trx->items as $item){
                Stock::where('product_unit_id',$item->product_unit_id)
                    ->where('location','toko')
                    ->decrement('qty',$item->qty);
            }

            $change = $request->paid - $trx->items()->sum('subtotal');

            $trx->update([
                'paid' => $request->paid,
                'change' => $change,
                'status' => 'paid',
                'trx_number' => 'TRX-' . now()->format('YmdHis')
            ]);

            return $trx;
        });

        return response()->json([
            'success'=>true,
            'trx_id'=>$trx->id,
            'trx_number'=>$trx->trx_number
        ]);
    }
}

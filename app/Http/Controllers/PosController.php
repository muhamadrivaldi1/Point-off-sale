<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Stock;
use App\Models\ProductPrice;
use App\Models\CashierSession;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    // ================= HELPER TRANSAKSI AKTIF =================
    private function getActiveTransaction(Request $request): Transaction
    {
        return Transaction::where('id', $request->trx_id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'kasir') {
            $session = CashierSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if (!$session) {
                return view('pos.open_session');
            }
        }

        if ($request->filled('trx_id')) {
            $trx = Transaction::where('id', $request->trx_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->firstOrFail();
        } else {
            $trx = Transaction::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$trx) {
                $trx = Transaction::create([
                    'user_id'    => $user->id,
                    'status'     => 'pending',
                    'trx_number' => 'TRX-' . now()->format('YmdHis'),
                    'total'      => 0,
                    'paid'       => 0,
                    'change'     => 0,
                    'discount'   => 0
                ]);
            }
        }

        $trx->load('items.unit.product', 'member');

        foreach ($trx->items as $item) {
            $item->subtotal = ($item->price - ($item->discount ?? 0)) * $item->qty;
        }

        $subtotal = $trx->items->sum('subtotal');

        $discountValue = 0;
        if ($trx->member_id) {
            $member = Member::find($trx->member_id);
            if ($member) {
                $discountValue = ($subtotal * $member->discount) / 100;
            }
        }

        $trx->total = max($subtotal - $discountValue, 0);

        $members = Member::where('status', 'aktif')->get();

        return view('pos.index', compact('trx', 'members'));
    }

    // ================= SEARCH =================
    public function search(Request $request)
    {
        $request->validate([
            'q'        => 'required|string|min:2',
            'location' => 'required|in:toko,gudang'
        ]);

        $location = strtolower(trim($request->location));

        $units = ProductUnit::with('product')
            ->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->limit(10)
            ->get();

        return response()->json(
            $units->map(function ($unit) use ($location) {
                $stock = Stock::where('product_unit_id', $unit->id)
                    ->where('location', $location)
                    ->value('qty') ?? 0;

                return [
                    'id'    => $unit->id,
                    'name'  => $unit->product->name . ' (' . $unit->unit_name . ')',
                    'price' => $unit->price,
                    'stock' => $stock
                ];
            })
        );
    }

    // ================= SCAN BARCODE =================
    public function scan(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
            'location' => 'required|in:toko,gudang'
        ]);

        $location = strtolower(trim($request->location));
        $code     = $request->code;

        // cari ProductUnit berdasarkan barcode (misal field `barcode` di tabel product_units)
        $unit = ProductUnit::with('product')
            ->where('barcode', $code)
            ->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 200);
        }

        // cek stok
        $stok = Stock::where('product_unit_id', $unit->id)
            ->where('location', $location)
            ->value('qty') ?? 0;

        return response()->json([
            'success' => true,
            'id'      => $unit->id,
            'name'    => $unit->product->name,
            'unit'    => $unit->unit_name,
            'price'   => $unit->price,
            'stock'   => $stok,
            'location' => $location
        ]);
    }


    // ================= HARGA BY QTY =================
    private function resolvePrice(ProductUnit $unit, int $qty): float
    {
        $rule = ProductPrice::where('unit_id', $unit->id)
            ->where('min_qty', '<=', $qty)
            ->orderByDesc('min_qty')
            ->first();

        return $rule?->price ?? $unit->price;
    }

    // ================= ADD ITEM =================
    public function addItem(Request $request)
    {
        $request->validate([
            'trx_id'           => 'required|exists:transactions,id',
            'product_unit_id'  => 'required|exists:product_units,id',
            'location'         => 'required|in:gudang,toko',
            'override_password' => 'nullable|string'
        ]);

        $trx      = $this->getActiveTransaction($request);
        $unit     = ProductUnit::findOrFail($request->product_unit_id);
        $location = strtolower(trim($request->location));

        $stok = Stock::where('product_unit_id', $unit->id)
            ->where('location', $location)
            ->value('qty') ?? 0;

        $item = TransactionItem::updateOrCreate(
            [
                'transaction_id'  => $trx->id,
                'product_unit_id' => $unit->id,
                'location'        => $location
            ],
            [
                'qty'      => 0,
                'price'    => $unit->price,
                'discount' => 0,
                'subtotal' => 0
            ]
        );

        // ===== LOGIKA STOK & OVERRIDE =====
        $needOverride = ($item->qty + 1 > $stok);
        if ($needOverride) {
            $overridePassword = $request->override_password ?? '';
            $validOverride = $overridePassword === env('POS_OVERRIDE_PASSWORD');
            if (!$validOverride) {
                return response()->json([
                    'success' => false,
                    'need_override' => true,
                    'message' => 'Stok kurang, butuh password override'
                ], 200);
            }
        }

        $item->qty++;
        $item->price    = $this->resolvePrice($unit, $item->qty);
        $item->subtotal = ($item->price - $item->discount) * $item->qty;
        $item->save();

        return response()->json([
            'success' => true,
            'item' => [
                'id'       => $item->id,
                'name'     => $unit->product->name,
                'unit'     => $unit->unit_name,
                'qty'      => $item->qty,
                'price'    => $item->price,
                'subtotal' => $item->subtotal,
                'location' => $item->location
            ]
        ]);
    }

    // ================= UPDATE QTY =================
    public function updateQty(Request $request)
    {
        $request->validate([
            'trx_id'   => 'required|exists:transactions,id',
            'item_id'  => 'required|exists:transaction_items,id',
            'type'     => 'required|in:plus,minus,delete',
            'location' => 'required|in:gudang,toko'
        ]);

        $trx  = $this->getActiveTransaction($request);
        $item = TransactionItem::with('unit')->findOrFail($request->item_id);

        abort_if($item->transaction_id !== $trx->id, 403);

        $location = strtolower(trim($request->location));

        // ===== DELETE ITEM LANGSUNG =====
        if ($request->type === 'delete') {
            $item->delete();
            return response()->json(['success' => true]);
        }

        // ===== TAMBAH QTY =====
        if ($request->type === 'plus') {
            $stock = Stock::where('product_unit_id', $item->product_unit_id)
                ->where('location', $location)
                ->first();

            $needOverride = !$stock || ($item->qty + 1 > $stock->qty);
            if ($needOverride) {
                $overridePassword = $request->override_password ?? '';
                $validOverride = $overridePassword === env('POS_OVERRIDE_PASSWORD');
                if (!$validOverride) {
                    return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi, butuh password override'], 200);
                }
            }

            $item->qty++;
        }

        // ===== KURANGI QTY =====
        if ($request->type === 'minus') {
            $item->qty--;
            if ($item->qty <= 0) {
                $item->delete();
                return response()->json(['success' => true]);
            }
        }

        $item->price    = $this->resolvePrice($item->unit, $item->qty);
        $item->subtotal = ($item->price - $item->discount) * $item->qty;
        $item->location = $location;
        $item->save();

        return response()->json(['success' => true]);
    }

    // ================= PAY =================
    public function pay(Request $request)
    {
        $request->validate([
            'trx_id'      => 'required|exists:transactions,id',
            'paid'        => 'nullable|numeric|min:0',
            'member_id'   => 'nullable|exists:members,id',
            'used_points' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $trx = Transaction::with('items.unit.product')
                ->lockForUpdate()
                ->findOrFail($request->trx_id);

            if ($trx->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Transaksi sudah selesai'], 400);
            }

            $total = 0;
            $validItems = [];

            // cek stok tiap item
            foreach ($trx->items as $item) {
                $location = strtolower(trim($item->location));

                $stock = Stock::where('product_unit_id', $item->product_unit_id)
                    ->where('location', $location)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->qty < $item->qty) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$item->unit->product->name} di {$location} hanya tersedia " . ($stock?->qty ?? 0)
                    ], 400);
                }

                $subtotal = ($item->price - ($item->discount ?? 0)) * $item->qty;
                $total += $subtotal;

                $validItems[] = ['item' => $item, 'stock' => $stock];
            }

            // ===== PAKAI POIN MEMBER =====
            if ($request->member_id && ($request->used_points ?? 0) > 0) {
                $member = Member::find($request->member_id);
                if ($member) {
                    $usedPoints = min($member->points, (int) $request->used_points);
                    $member->decrement('points', $usedPoints);
                    $pointValue = 1000; // 1 poin = Rp 1000
                    $total -= $usedPoints * $pointValue;
                    $total = max($total, 0);
                    $trx->used_points = $usedPoints;
                }
            }

            $paid = (int) ($request->paid ?? 0);

            // ===== TRANSAKSI BELUM LUNAS =====
            if ($paid < $total) {
                $trx->update([
                    'member_id'   => $request->member_id,
                    'total'       => $total,
                    'paid'        => $paid,
                    'change'      => 0,
                    'status'      => 'pending',
                    'used_points' => $request->used_points ?? 0
                ]);
                DB::commit();

                return response()->json(['success' => true, 'paid_off' => false, 'trx_id' => $trx->id]);
            }

            // ===== TRANSAKSI LUNAS =====
            foreach ($validItems as $row) {
                $row['stock']->decrement('qty', $row['item']->qty);
            }

            $trx->update([
                'member_id' => $request->member_id,
                'total'     => $total,
                'paid'      => $paid,
                'change'    => $paid - $total,
                'status'    => 'paid',
                'used_points' => $request->used_points ?? 0
            ]);

            // tambahkan poin untuk member
            if ($request->member_id) {
                $point = floor($total / 10000);
                Member::where('id', $request->member_id)->increment('points', $point);
            }

            DB::commit();

            return response()->json(['success' => true, 'paid_off' => true, 'trx_id' => $trx->id]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ================= CART JSON =================
    public function cart(Request $request)
    {
        $trx = $this->getActiveTransaction($request);
        $items = $trx->items()->with('unit.product')->get();

        $cart = $items->map(fn($i) => [
            'id'       => $i->id,
            'name'     => $i->unit->product->name,
            'unit'     => $i->unit->unit_name,
            'qty'      => $i->qty,
            'price'    => $i->price,
            'subtotal' => ($i->price - $i->discount) * $i->qty,
            'location' => $i->location
        ]);

        return response()->json([
            'items' => $cart,
            'total' => $cart->sum('subtotal')
        ]);
    }
}

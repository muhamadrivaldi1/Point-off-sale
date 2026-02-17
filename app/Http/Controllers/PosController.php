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

    // ================= GENERATE TRX NUMBER UNIQUELY =================
    private function generateTrxNumber(): string
    {
        $date = now()->format('Ymd');
        $micro = microtime(true);
        $microSeconds = sprintf('%06d', ($micro - floor($micro)) * 1000000);
        $random = mt_rand(100, 999);

        return "TRX-{$date}-{$microSeconds}{$random}";
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cek session kasir
        if ($user->role === 'kasir') {
            $session = CashierSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if (!$session) {
                return view('pos.open_session');
            }
        }

        // Ambil transaksi aktif jika ada
        $trx = null;
        if ($request->filled('trx_id')) {
            $trx = Transaction::where('id', $request->trx_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();
        }

        // Jika tidak ada trx_id, atau trx tidak ditemukan, buat transaksi baru
        if (!$trx) {
            $trx = DB::transaction(function () use ($user) {
                return Transaction::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'trx_number' => $this->generateTrxNumber(),
                    'total' => 0,
                    'paid' => 0,
                    'change' => 0,
                    'discount' => 0
                ]);
            });
        }

        $trx->load('items.unit.product', 'member');

        // Hitung subtotal tiap item
        foreach ($trx->items as $item) {
            $item->subtotal = ($item->price - ($item->discount ?? 0)) * $item->qty;
        }

        // Hitung total transaksi
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

        $pendingTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('items')
            ->latest()
            ->get()
            ->map(function ($t) {
                $itemCount = $t->items->count();
                $totalAmount = $t->items->sum(fn($item) => ($item->price - ($item->discount ?? 0)) * $item->qty);
                return [
                    'id' => $t->id,
                    'trx_number' => $t->trx_number,
                    'item_count' => $itemCount,
                    'total' => $totalAmount,
                    'created_at' => $t->created_at
                ];
            });

        return view('pos.index', compact('trx', 'members', 'pendingTransactions'));
    }

    // ================= SEARCH PRODUK =================
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'location' => 'required|in:toko,gudang'
        ]);

        $location = strtolower(trim($request->location));

        $units = ProductUnit::with('product')
            ->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->limit(10)
            ->get();

        return response()->json(
            $units->map(fn($unit) => [
                'id' => $unit->id,
                'barcode' => $unit->barcode,
                'name' => $unit->product->name,
                'unit' => $unit->unit_name,
                'price' => $unit->price,
                'stock' => Stock::where('product_unit_id', $unit->id)
                                ->where('location', $location)
                                ->value('qty') ?? 0
            ])
        );
    }

    // ================= SCAN BARCODE =================
    public function scan(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'location' => 'required|in:toko,gudang'
        ]);

        $location = strtolower(trim($request->location));
        $unit = ProductUnit::with('product')
            ->where('barcode', $request->code)
            ->first();

        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan']);
        }

        $stock = Stock::where('product_unit_id', $unit->id)
            ->where('location', $location)
            ->value('qty') ?? 0;

        return response()->json([
            'success' => true,
            'id' => $unit->id,
            'name' => $unit->product->name,
            'unit' => $unit->unit_name,
            'price' => $unit->price,
            'stock' => $stock,
            'location' => $location
        ]);
    }

    // ================= RESOLVE PRICE =================
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
            'trx_id' => 'required|exists:transactions,id',
            'product_unit_id' => 'required|exists:product_units,id',
            'location' => 'required|in:gudang,toko',
            'override_password' => 'nullable|string'
        ]);

        $trx = $this->getActiveTransaction($request);
        $unit = ProductUnit::findOrFail($request->product_unit_id);
        $location = strtolower(trim($request->location));
        $stok = Stock::where('product_unit_id', $unit->id)
            ->where('location', $location)
            ->value('qty') ?? 0;

        $item = TransactionItem::updateOrCreate(
            ['transaction_id' => $trx->id, 'product_unit_id' => $unit->id, 'location' => $location],
            ['qty' => 0, 'price' => $unit->price, 'discount' => 0, 'subtotal' => 0]
        );

        $needOverride = ($item->qty + 1 > $stok);
        if ($needOverride) {
            $overridePassword = $request->override_password ?? '';
            $validOverride = $overridePassword === env('POS_OVERRIDE_PASSWORD');
            if (!$validOverride) {
                return response()->json([
                    'success' => false,
                    'need_override' => true,
                    'message' => 'Stok kurang, butuh password override'
                ]);
            }
        }

        $item->qty++;
        $item->price = $this->resolvePrice($unit, $item->qty);
        $item->subtotal = ($item->price - $item->discount) * $item->qty;
        $item->save();

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $unit->product->name,
                'unit' => $unit->unit_name,
                'qty' => $item->qty,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
                'location' => $item->location
            ]
        ]);
    }

    // ================= UPDATE QTY =================
    public function updateQty(Request $request)
    {
        $request->validate([
            'trx_id' => 'required|exists:transactions,id',
            'item_id' => 'required|exists:transaction_items,id',
            'type' => 'required|in:plus,minus,delete',
            'location' => 'required|in:gudang,toko',
            'override_password' => 'nullable|string'
        ]);

        $trx = $this->getActiveTransaction($request);
        $item = TransactionItem::with('unit')->findOrFail($request->item_id);
        abort_if($item->transaction_id !== $trx->id, 403);

        $location = strtolower(trim($request->location));

        if ($request->type === 'delete') {
            $item->delete();
            return response()->json(['success' => true]);
        }

        $stock = Stock::where('product_unit_id', $item->product_unit_id)
            ->where('location', $location)
            ->first();

        if ($request->type === 'plus') {
            $needOverride = !$stock || ($item->qty + 1 > $stock->qty);
            if ($needOverride) {
                $overridePassword = $request->override_password ?? '';
                $validOverride = $overridePassword === env('POS_OVERRIDE_PASSWORD');
                if (!$validOverride) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi, butuh password override'
                    ]);
                }
            }
            $item->qty++;
        }

        if ($request->type === 'minus') {
            $item->qty--;
            if ($item->qty <= 0) {
                $item->delete();
                return response()->json(['success' => true]);
            }
        }

        $item->price = $this->resolvePrice($item->unit, $item->qty);
        $item->subtotal = ($item->price - $item->discount) * $item->qty;
        $item->location = $location;
        $item->save();

        return response()->json(['success' => true]);
    }

    // ================= PAY =================
    public function pay(Request $request)
    {
        $request->validate([
            'trx_id' => 'required|exists:transactions,id',
            'paid' => 'nullable|numeric|min:0',
            'member_id' => 'nullable|exists:members,id'
        ]);

        DB::beginTransaction();
        try {
            $trx = Transaction::with('items.unit.product', 'member')
                ->lockForUpdate()
                ->findOrFail($request->trx_id);

            if ($trx->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Transaksi sudah selesai'], 400);
            }

            $subtotal = 0;
            $validItems = [];

            foreach ($trx->items as $item) {
                $stock = Stock::where('product_unit_id', $item->product_unit_id)
                    ->where('location', $item->location)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->qty < $item->qty) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$item->unit->product->name} di {$item->location} hanya tersedia " . ($stock?->qty ?? 0)
                    ], 400);
                }

                $itemSubtotal = ($item->price - ($item->discount ?? 0)) * $item->qty;
                $subtotal += $itemSubtotal;
                $validItems[] = ['item' => $item, 'stock' => $stock];
            }

            $discountAmount = 0;
            $member = null;
            if ($request->member_id) {
                $member = Member::find($request->member_id);
                if ($member && $member->discount > 0) {
                    $discountAmount = ($subtotal * $member->discount) / 100;
                }
            }

            $total = $subtotal - $discountAmount;
            $paid = (float) ($request->paid ?? 0);

            if (round($paid, 0) < round($total, 0)) {
                $trx->update([
                    'member_id' => $request->member_id,
                    'total' => $total,
                    'paid' => $paid,
                    'change' => 0,
                    'status' => 'pending',
                    'discount' => $discountAmount
                ]);

                DB::commit();
                return response()->json(['success' => true, 'paid_off' => false, 'trx_id' => $trx->id]);
            }

            foreach ($validItems as $row) {
                $row['stock']->decrement('qty', $row['item']->qty);
            }

            $trx->update([
                'member_id' => $request->member_id,
                'total' => $total,
                'paid' => $paid,
                'change' => $paid - $total,
                'status' => 'paid',
                'discount' => $discountAmount
            ]);

            if ($member) {
                $earnedPoints = floor($subtotal / 10000);
                if ($earnedPoints > 0) $member->increment('points', $earnedPoints);

                $member->increment('total_spent', $total);

                if ($member->discount == 0) {
                    if ($member->total_spent >= 5000000) $member->update(['level' => 'Gold', 'discount' => 5]);
                    elseif ($member->total_spent >= 1000000) $member->update(['level' => 'Silver', 'discount' => 2]);
                    else $member->update(['level' => 'Basic', 'discount' => 0]);
                } else {
                    if ($member->total_spent >= 5000000) $member->update(['level' => 'Gold']);
                    elseif ($member->total_spent >= 1000000) $member->update(['level' => 'Silver']);
                    else $member->update(['level' => 'Basic']);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'paid_off' => true, 'trx_id' => $trx->id]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ================= CART =================
    public function cart(Request $request)
    {
        $trx = $this->getActiveTransaction($request);
        $items = $trx->items()->with('unit.product')->get();

        $cart = $items->map(fn($i) => [
            'id' => $i->id,
            'name' => $i->unit->product->name,
            'unit' => $i->unit->unit_name,
            'qty' => $i->qty,
            'price' => $i->price,
            'subtotal' => ($i->price - $i->discount) * $i->qty,
            'location' => $i->location
        ]);

        return response()->json(['items' => $cart, 'total' => $cart->sum('subtotal')]);
    }

    // ================= UPDATE UNIT =================
    public function updateUnit(Request $request)
    {
        $request->validate([
            'trx_id' => 'required|exists:transactions,id',
            'item_id' => 'required|exists:transaction_items,id',
            'product_unit_id' => 'required|exists:product_units,id',
            'location' => 'required|in:gudang,toko',
            'override_password' => 'nullable|string'
        ]);

        $trx = $this->getActiveTransaction($request);
        $item = TransactionItem::findOrFail($request->item_id);
        abort_if($item->transaction_id !== $trx->id, 403);

        $unit = ProductUnit::with('product')->findOrFail($request->product_unit_id);
        $location = strtolower(trim($request->location));

        $stock = Stock::where('product_unit_id', $unit->id)
            ->where('location', $location)
            ->first();

        $needOverride = !$stock || ($item->qty > $stock->qty);
        if ($needOverride) {
            $overridePassword = $request->override_password ?? '';
            $validOverride = $overridePassword === env('POS_OVERRIDE_PASSWORD');
            if (!$validOverride) {
                return response()->json([
                    'success' => false,
                    'need_override' => true,
                    'message' => 'Stok tidak cukup, butuh password override'
                ]);
            }
        }

        $item->product_unit_id = $unit->id;
        $item->price = $unit->price;
        $item->subtotal = ($unit->price - $item->discount) * $item->qty;
        $item->location = $location;
        $item->save();

        return response()->json(['success' => true]);
    }

    // ================= UPDATE QTY MANUAL =================
    public function updateQtyManual(Request $request)
    {
        $request->validate([
            'trx_id' => 'required|exists:transactions,id',
            'item_id' => 'required|exists:transaction_items,id',
            'qty' => 'required|integer|min:1',
            'location' => 'required|in:gudang,toko',
            'override_password' => 'nullable|string'
        ]);

        $trx = $this->getActiveTransaction($request);
        $item = TransactionItem::with('unit')->findOrFail($request->item_id);
        abort_if($item->transaction_id !== $trx->id, 403);

        $location = strtolower(trim($request->location));
        $stock = Stock::where('product_unit_id', $item->product_unit_id)
            ->where('location', $location)
            ->first();

        $needOverride = !$stock || ($request->qty > $stock->qty);
        if ($needOverride) {
            $overridePassword = $request->override_password ?? '';
            $validOverride = $overridePassword === env('POS_OVERRIDE_PASSWORD');
            if (!$validOverride) {
                return response()->json([
                    'success' => false,
                    'need_override' => true,
                    'message' => 'Stok tidak cukup, butuh password override'
                ]);
            }
        }

        $item->qty = $request->qty;
        $item->price = $this->resolvePrice($item->unit, $item->qty);
        $item->subtotal = ($item->price - $item->discount) * $item->qty;
        $item->location = $location;
        $item->save();

        return response()->json(['success' => true]);
    }

    // ================= REMOVE ITEM =================
    public function removeItem(Request $request)
    {
        $item = TransactionItem::find($request->item_id);
        if ($item) $item->delete();
        return response()->json(['success' => true]);
    }

    // ================= OVERRIDE OWNER =================
    public function overrideOwner(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        return response()->json(['success' => $request->password === env('POS_OVERRIDE_PASSWORD')]);
    }

    // ================= SEARCH MEMBER =================
    public function searchMember(Request $r)
    {
        $q = $r->q;
        return DB::table('members')
            ->where(fn($query) => $query->where('name','like',"%$q%")
                                         ->orWhere('phone','like',"%$q%")
                                         ->orWhere('address','like',"%$q%"))
            ->limit(10)
            ->get();
    }

    // ================= GET MEMBER =================
    public function getMember(Request $r)
    {
        return DB::table('members')->where('id', $r->id)->first();
    }

    // ================= SET MEMBER =================
    public function setMember(Request $r)
    {
        $trx = Transaction::find($r->trx_id);
        if (!$trx) return response()->json(['success' => false]);

        $member = Member::find($r->member_id);
        if (!$member) return response()->json(['success' => false]);

        $trx->member_id = $member->id;
        $trx->save();

        return response()->json(['success' => true]);
    }

    // ================= CLEANUP EMPTY PENDING =================
    public function cleanupEmptyPending()
    {
        $deleted = Transaction::where('status', 'pending')
            ->whereDoesntHave('items')
            ->where('total', 0)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "$deleted transaksi pending kosong telah dihapus"
        ]);
    }
}

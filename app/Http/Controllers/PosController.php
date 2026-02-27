<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Stock;
use App\Models\ProductPrice;
use App\Models\CashierSession;
use App\Models\Member;
use App\Models\Warehouse;
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

    // ================= GENERATE TRX NUMBER =================
    private function generateTrxNumber(): string
    {
        $date     = now()->format('Ymd');
        $micro    = microtime(true);
        $microSec = sprintf('%06d', ($micro - floor($micro)) * 1000000);
        $random   = mt_rand(100, 999);
        return "TRX-{$date}-{$microSec}{$random}";
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $user            = Auth::user();
        $activeWarehouse = Warehouse::where('is_active', true)->first();

        if (!$activeWarehouse) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Aktifkan gudang terlebih dahulu sebelum menggunakan POS');
        }

        $warehouses = Warehouse::orderBy('id')->get();

        if ($user->role === 'kasir') {
            $session = CashierSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();
            if (!$session) {
                return view('pos.open_session');
            }
        }

        $trx = null;
        if ($request->filled('trx_id')) {
            $trx = Transaction::where('id', $request->trx_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();
        }

        if (!$trx) {
            $trx = DB::transaction(function () use ($user) {
                return Transaction::create([
                    'user_id'    => $user->id,
                    'status'     => 'pending',
                    'trx_number' => $this->generateTrxNumber(),
                    'total'      => 0,
                    'paid'       => 0,
                    'change'     => 0,
                    'discount'   => 0,
                ]);
            });
        }

        $trx->load('items.unit.product', 'member');

        foreach ($trx->items as $item) {
            $item->subtotal = ($item->price - ($item->discount ?? 0)) * $item->qty;
        }

        $subtotal = $trx->items->sum('subtotal');

        if ($trx->total <= 0) {
            if ($trx->member_id) {
                $member = Member::find($trx->member_id);
                if ($member) {
                    $trx->discount = round(($subtotal * $member->discount) / 100, 0);
                }
            }
            $trx->total = max($subtotal - ($trx->discount ?? 0), 0);
        }

        $members = Member::where('status', 'aktif')->get();

        $pendingTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('items')
            ->latest()
            ->get()
            ->map(function ($t) {
                return [
                    'id'         => $t->id,
                    'trx_number' => $t->trx_number,
                    'item_count' => $t->items->count(),
                    'total'      => $t->items->sum(fn($i) => ($i->price - ($i->discount ?? 0)) * $i->qty),
                    'created_at' => $t->created_at,
                ];
            });

        $todayTransactions = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->where(function ($q) {
                $q->whereIn('status', ['paid', 'kredit'])->orWhereHas('items');
            })
            ->latest()
            ->take(10)
            ->get();

        $warehousesJson = $warehouses->map(function ($w, $i) {
            return [
                'id'    => $w->id,
                'label' => 'Stok ' . chr(65 + $i),
                'name'  => $w->name,
            ];
        })->values()->toArray();

        return view('pos.index', compact(
            'trx',
            'members',
            'pendingTransactions',
            'todayTransactions',
            'activeWarehouse',
            'warehouses',
            'warehousesJson'
        ));
    }

    // ================= SEARCH PRODUK =================
    public function search(Request $request)
    {
        $request->validate([
            'q'            => 'required|string|min:1',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $keyword    = trim($request->q);
        $warehouses = Warehouse::orderBy('id')->get();

        $units = ProductUnit::with('product')
            ->where(function ($query) use ($keyword) {
                $query->where('barcode', 'like', "%{$keyword}%")
                      ->orWhereHas('product', function ($q) use ($keyword) {
                          $q->where('name', 'like', "%{$keyword}%");
                      });
            })
            ->limit(20)
            ->get();

        return response()->json(
            $units->map(function ($unit) use ($warehouses) {
                $stocks = [];
                foreach ($warehouses as $wh) {
                    $stocks[] = Stock::where('product_unit_id', $unit->id)
                        ->where('warehouse_id', $wh->id)
                        ->value('qty') ?? 0;
                }

                return [
                    'id'      => $unit->id,
                    'barcode' => $unit->barcode,
                    'name'    => $unit->product->name,
                    'unit'    => $unit->unit_name,
                    'price'   => $unit->price,
                    'stocks'  => $stocks,
                ];
            })
        );
    }

    // ================= SCAN BARCODE =================
    public function scan(Request $request)
    {
        $request->validate([
            'code'         => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $unit = ProductUnit::with('product')
            ->where('barcode', $request->code)
            ->first();

        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan']);
        }

        $stock = Stock::where('product_unit_id', $unit->id)
            ->where('warehouse_id', $request->warehouse_id)
            ->value('qty') ?? 0;

        return response()->json([
            'success'      => true,
            'id'           => $unit->id,
            'name'         => $unit->product->name,
            'unit'         => $unit->unit_name,
            'price'        => $unit->price,
            'stock'        => $stock,
            'warehouse_id' => $request->warehouse_id,
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

    // ================= CEK STOK — PERLU OVERRIDE? =================
    // Stok 0 = DIIZINKAN langsung (owner mungkin belum update sistem).
    // Stok > 0 tapi kurang dari qty yang diminta = butuh override password.
    private function stockNeedsOverride(int $stok, int $newQty): bool
    {
        // Jika stok 0 atau tidak ada di sistem → izinkan saja (tanpa override)
        if ($stok <= 0) {
            return false;
        }
        // Stok ada, tapi kurang dari yang diminta → butuh override
        return $newQty > $stok;
    }

    // ================= ADD ITEM =================
    public function addItem(Request $request)
    {
        $request->validate([
            'trx_id'            => 'required|exists:transactions,id',
            'product_unit_id'   => 'required|exists:product_units,id',
            'warehouse_id'      => 'required|exists:warehouses,id',
            'override_password' => 'nullable|string',
        ]);

        $trx         = $this->getActiveTransaction($request);
        $unit        = ProductUnit::findOrFail($request->product_unit_id);
        $warehouseId = $request->warehouse_id;

        $stok = Stock::where('product_unit_id', $unit->id)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0;

        // Cari item yang sudah ada di keranjang
        $existingItem = TransactionItem::where('transaction_id', $trx->id)
            ->where('product_unit_id', $unit->id)
            ->first();

        $newQty = ($existingItem ? $existingItem->qty : 0) + 1;

        // Cek apakah butuh override
        if ($this->stockNeedsOverride($stok, $newQty)) {
            $valid = ($request->override_password ?? '') === env('POS_OVERRIDE_PASSWORD');
            if (!$valid) {
                return response()->json([
                    'success'       => false,
                    'need_override' => true,
                    'message'       => "Stok {$unit->product->name} hanya tersisa {$stok}. Butuh password override.",
                ]);
            }
        }

        // FIX DOUBLE-ADD: DB::transaction + lockForUpdate
        $item = null;
        DB::transaction(function () use ($trx, $unit, $warehouseId, &$item) {
            $item = TransactionItem::where('transaction_id', $trx->id)
                ->where('product_unit_id', $unit->id)
                ->lockForUpdate()
                ->first();

            if ($item) {
                $item->qty      = $item->qty + 1;
                $item->price    = $this->resolvePrice($unit, $item->qty);
                $item->subtotal = ($item->price - ($item->discount ?? 0)) * $item->qty;
                $item->warehouse_id = $warehouseId;
                $item->save();
            } else {
                $price = $this->resolvePrice($unit, 1);
                $item  = TransactionItem::create([
                    'transaction_id'  => $trx->id,
                    'product_unit_id' => $unit->id,
                    'warehouse_id'    => $warehouseId,
                    'qty'             => 1,
                    'price'           => $price,
                    'discount'        => 0,
                    'subtotal'        => $price,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'item'    => [
                'id'           => $item->id,
                'name'         => $unit->product->name,
                'unit'         => $unit->unit_name,
                'qty'          => $item->qty,
                'price'        => $item->price,
                'subtotal'     => $item->subtotal,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }

    // ================= UPDATE QTY MANUAL =================
    public function updateQtyManual(Request $request)
    {
        $request->validate([
            'trx_id'            => 'required|exists:transactions,id',
            'item_id'           => 'required|exists:transaction_items,id',
            'qty'               => 'required|integer|min:1',
            'warehouse_id'      => 'required|exists:warehouses,id',
            'override_password' => 'nullable|string',
        ]);

        $trx  = $this->getActiveTransaction($request);
        $item = TransactionItem::with('unit')->findOrFail($request->item_id);
        abort_if($item->transaction_id !== $trx->id, 403);

        $warehouseId = $request->warehouse_id;
        $stok        = Stock::where('product_unit_id', $item->product_unit_id)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0;

        // Stok 0 = langsung izinkan. Stok > 0 tapi kurang = butuh override.
        if ($this->stockNeedsOverride($stok, $request->qty)) {
            $valid = ($request->override_password ?? '') === env('POS_OVERRIDE_PASSWORD');
            if (!$valid) {
                return response()->json([
                    'success'       => false,
                    'need_override' => true,
                    'message'       => "Stok hanya tersisa {$stok}. Butuh password override.",
                ]);
            }
        }

        $item->qty          = $request->qty;
        $item->price        = $this->resolvePrice($item->unit, $item->qty);
        $item->subtotal     = ($item->price - ($item->discount ?? 0)) * $item->qty;
        $item->warehouse_id = $warehouseId;
        $item->save();

        return response()->json(['success' => true]);
    }

    // ================= UPDATE UNIT =================
    public function updateUnit(Request $request)
    {
        $request->validate([
            'trx_id'            => 'required|exists:transactions,id',
            'item_id'           => 'required|exists:transaction_items,id',
            'product_unit_id'   => 'required|exists:product_units,id',
            'warehouse_id'      => 'required|exists:warehouses,id',
            'override_password' => 'nullable|string',
        ]);

        $trx  = $this->getActiveTransaction($request);
        $item = TransactionItem::findOrFail($request->item_id);
        abort_if($item->transaction_id !== $trx->id, 403);

        $unit        = ProductUnit::with('product')->findOrFail($request->product_unit_id);
        $warehouseId = $request->warehouse_id;

        $stok = Stock::where('product_unit_id', $unit->id)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0;

        if ($this->stockNeedsOverride($stok, $item->qty)) {
            $valid = ($request->override_password ?? '') === env('POS_OVERRIDE_PASSWORD');
            if (!$valid) {
                return response()->json([
                    'success'       => false,
                    'need_override' => true,
                    'message'       => "Stok hanya tersisa {$stok}. Butuh password override.",
                ]);
            }
        }

        $item->product_unit_id = $unit->id;
        $item->price           = $unit->price;
        $item->subtotal        = ($unit->price - ($item->discount ?? 0)) * $item->qty;
        $item->warehouse_id    = $warehouseId;
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

    // ================= PAY =================
    public function pay(Request $request)
    {
        $request->validate([
            'trx_id'         => 'required|exists:transactions,id',
            'paid'           => 'nullable|numeric|min:0',
            'member_id'      => 'nullable|exists:members,id',
            'payment_method' => 'nullable|in:cash,transfer,qris,kredit',
            'frontend_total' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $trx = Transaction::with('items.unit.product', 'member')
                ->lockForUpdate()
                ->findOrFail($request->trx_id);

            if ($trx->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Transaksi sudah selesai'], 400);
            }

            $activeWarehouseId = Warehouse::where('is_active', true)->value('id') ?? 1;
            $paymentMethod     = $request->payment_method ?? 'cash';
            $isKredit          = $paymentMethod === 'kredit';

            $subtotal   = 0;
            $validItems = [];

            foreach ($trx->items as $item) {
                $warehouseId = ($item->warehouse_id && $item->warehouse_id > 0)
                    ? $item->warehouse_id
                    : $activeWarehouseId;

                $stock = Stock::where('product_unit_id', $item->product_unit_id)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                // Jika stok tidak ada record atau stok = 0:
                // → IZINKAN transaksi (owner mungkin lupa update), tapi tidak kurangi stok
                // Jika stok ada (> 0) tapi kurang dari qty:
                // → TOLAK (seharusnya sudah di-handle di addItem, ini double-check)
                if ($stock && $stock->qty > 0 && $stock->qty < $item->qty) {
                    // Coba cari di gudang lain sebagai fallback
                    $altStock = Stock::where('product_unit_id', $item->product_unit_id)
                        ->where('qty', '>=', $item->qty)
                        ->lockForUpdate()
                        ->first();

                    if (!$altStock) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stok {$item->unit->product->name} hanya tersedia {$stock->qty}. Transaksi dibatalkan.",
                        ], 400);
                    }

                    $stock = $altStock;
                }

                $subtotal    += ($item->price - ($item->discount ?? 0)) * $item->qty;
                $validItems[] = ['item' => $item, 'stock' => $stock];
            }

            // Hitung diskon
            $discountAmount = 0;
            $member         = null;

            if ($request->member_id) {
                $member = Member::find($request->member_id);
                if ($member && $member->discount > 0) {
                    $discountAmount = round(($subtotal * $member->discount) / 100, 0);
                }
            }

            $savedDiscount = round((float)($trx->discount ?? 0), 0);
            if ($savedDiscount > $discountAmount) {
                $discountAmount = $savedDiscount;
            }

            if ($request->filled('frontend_total')) {
                $total          = (int) round((float) $request->frontend_total, 0);
                $discountAmount = (int) max(round($subtotal - $total, 0), 0);
            } else {
                $total = (int) round($subtotal - $discountAmount, 0);
            }

            $paid = (int) round((float)($request->paid ?? 0), 0);

            // ===== KREDIT: simpan langsung tanpa bayar =====
            if ($isKredit) {
                // Kurangi stok (hanya yang stoknya > 0)
                foreach ($validItems as $row) {
                    $s = $row['stock'];
                    if ($s && $s->qty > 0) {
                        $s->decrement('qty', min($row['item']->qty, $s->qty));
                    }
                }

                $trx->update([
                    'member_id'      => $request->member_id,
                    'total'          => $total,
                    'paid'           => 0,
                    'change'         => 0,
                    'status'         => 'kredit',
                    'discount'       => $discountAmount,
                    'payment_method' => 'kredit',
                ]);

                // Update poin & level member (belum earned karena belum bayar)
                if ($member) {
                    $member->increment('total_spent', $total);
                    if ($member->total_spent >= 5000000)     $member->update(['level' => 'Gold',   'discount' => 5]);
                    elseif ($member->total_spent >= 1000000) $member->update(['level' => 'Silver', 'discount' => 2]);
                    else                                      $member->update(['level' => 'Basic',  'discount' => 0]);
                }

                DB::commit();
                return response()->json(['success' => true, 'is_kredit' => true, 'trx_id' => $trx->id]);
            }

            // ===== PEMBAYARAN BIASA =====
            if ($paid < $total) {
                $trx->update([
                    'member_id'      => $request->member_id,
                    'total'          => $total,
                    'paid'           => $paid,
                    'change'         => 0,
                    'status'         => 'pending',
                    'discount'       => $discountAmount,
                    'payment_method' => $paymentMethod,
                ]);
                DB::commit();
                return response()->json(['success' => true, 'paid_off' => false, 'trx_id' => $trx->id]);
            }

            // Kurangi stok (hanya yang stoknya > 0)
            foreach ($validItems as $row) {
                $s = $row['stock'];
                if ($s && $s->qty > 0) {
                    $s->decrement('qty', min($row['item']->qty, $s->qty));
                }
            }

            $trx->update([
                'member_id'      => $request->member_id,
                'total'          => $total,
                'paid'           => $paid,
                'change'         => $paid - $total,
                'status'         => 'paid',
                'discount'       => $discountAmount,
                'payment_method' => $paymentMethod,
            ]);

            if ($member) {
                $earnedPoints = floor($subtotal / 10000);
                if ($earnedPoints > 0) $member->increment('points', $earnedPoints);
                $member->increment('total_spent', $total);

                if ($member->total_spent >= 5000000)     $member->update(['level' => 'Gold',   'discount' => 5]);
                elseif ($member->total_spent >= 1000000) $member->update(['level' => 'Silver', 'discount' => 2]);
                else                                      $member->update(['level' => 'Basic',  'discount' => 0]);
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
        $trx   = $this->getActiveTransaction($request);
        $items = $trx->items()->with('unit.product')->get();

        $cart = $items->map(fn($i) => [
            'id'           => $i->id,
            'name'         => $i->unit->product->name,
            'unit'         => $i->unit->unit_name,
            'qty'          => $i->qty,
            'price'        => $i->price,
            'subtotal'     => ($i->price - ($i->discount ?? 0)) * $i->qty,
            'warehouse_id' => $i->warehouse_id,
        ]);

        return response()->json(['items' => $cart, 'total' => $cart->sum('subtotal')]);
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
            ->where(fn($query) => $query
                ->where('name',    'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%")
                ->orWhere('address', 'like', "%$q%"))
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

    // ================= SET DISCOUNT =================
    public function setDiscount(Request $r)
    {
        $trx = Transaction::find($r->trx_id);
        if (!$trx) return response()->json(['success' => false]);

        $trx->discount_percent = $r->discount ?? 0;

        $subtotal      = $trx->items->sum(fn($item) => ($item->price - ($item->discount ?? 0)) * $item->qty);
        $trx->discount = round($subtotal * ($r->discount ?? 0) / 100, 0);
        $trx->save();

        return response()->json(['success' => true]);
    }

    // ================= REOPEN PAID / KREDIT TRANSACTION =================
    public function reopenTransaction(Request $request)
    {
        $request->validate([
            'trx_id'   => 'required|exists:transactions,id',
            'password' => 'required|string',
        ]);

        if ($request->password !== env('POS_OVERRIDE_PASSWORD')) {
            return response()->json(['success' => false, 'message' => 'Password salah']);
        }

        $trx = Transaction::find($request->trx_id);

        if (!$trx) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
        }

        if (!in_array($trx->status, ['paid', 'kredit'])) {
            return response()->json(['success' => false, 'message' => 'Transaksi bukan berstatus paid atau kredit']);
        }

        DB::transaction(function () use ($trx) {
            // Kembalikan stok yang sudah dikurangi
            foreach ($trx->items as $item) {
                $warehouseId = $item->warehouse_id
                    ?? Warehouse::where('is_active', true)->value('id')
                    ?? 1;

                $stock = Stock::where('product_unit_id', $item->product_unit_id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($stock) {
                    $stock->increment('qty', $item->qty);
                }
            }

            // Kembalikan poin & total_spent member
            if ($trx->member_id) {
                $member = Member::find($trx->member_id);
                if ($member) {
                    if ($trx->status === 'paid') {
                        $earnedPoints = floor($trx->total / 10000);
                        if ($earnedPoints > 0 && $member->points >= $earnedPoints) {
                            $member->decrement('points', $earnedPoints);
                        }
                    }
                    if ($member->total_spent >= $trx->total) {
                        $member->decrement('total_spent', $trx->total);
                    }
                    if ($member->total_spent >= 5000000)     $member->update(['level' => 'Gold',   'discount' => 5]);
                    elseif ($member->total_spent >= 1000000) $member->update(['level' => 'Silver', 'discount' => 2]);
                    else                                      $member->update(['level' => 'Basic',  'discount' => 0]);
                }
            }

            $trx->update([
                'status' => 'pending',
                'paid'   => 0,
                'change' => 0,
            ]);
        });

        return response()->json(['success' => true, 'trx_id' => $trx->id]);
    }

    // ================= CLEANUP EMPTY PENDING =================
    public function cleanupEmptyPending()
    {
        $deleted = Transaction::where('status', 'pending')
            ->whereDoesntHave('items')
            ->where('total', 0)
            ->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}
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
        $date       = now()->format('Ymd');
        $micro      = microtime(true);
        $microSec   = sprintf('%06d', ($micro - floor($micro)) * 1000000);
        $random     = mt_rand(100, 999);
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

        // Cek sesi kasir
        if ($user->role === 'kasir') {
            $session = CashierSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();
            if (!$session) {
                return view('pos.open_session');
            }
        }

        // Ambil transaksi aktif
        $trx = null;
        if ($request->filled('trx_id')) {
            $trx = Transaction::where('id', $request->trx_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();
        }

        // Buat baru jika tidak ada
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
                $q->where('status', 'paid')->orWhereHas('items');
            })
            ->latest()
            ->take(10)
            ->get();

        return view('pos.index', compact(
            'trx',
            'members',
            'pendingTransactions',
            'todayTransactions',
            'activeWarehouse'
        ));
    }

    // ================= SEARCH PRODUK =================
    // Menerima warehouse_id, bukan location
    public function search(Request $request)
    {
        $request->validate([
            'q'            => 'required|string|min:1',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $keyword     = trim($request->q);
        $warehouseId = $request->warehouse_id;

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
            $units->map(function ($unit) use ($warehouseId) {
                $stock = Stock::where('product_unit_id', $unit->id)
                    ->where('warehouse_id', $warehouseId)
                    ->value('qty') ?? 0;

                return [
                    'id'      => $unit->id,
                    'barcode' => $unit->barcode,
                    'name'    => $unit->product->name,
                    'unit'    => $unit->unit_name,
                    'price'   => $unit->price,
                    'stock'   => $stock,
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

        $item = TransactionItem::updateOrCreate(
            [
                'transaction_id'  => $trx->id,
                'product_unit_id' => $unit->id,
                'warehouse_id'    => $warehouseId,
            ],
            ['qty' => 0, 'price' => $unit->price, 'discount' => 0, 'subtotal' => 0]
        );

        if ($item->qty + 1 > $stok) {
            $valid = ($request->override_password ?? '') === env('POS_OVERRIDE_PASSWORD');
            if (!$valid) {
                return response()->json([
                    'success'       => false,
                    'need_override' => true,
                    'message'       => 'Stok tidak cukup, butuh password override',
                ]);
            }
        }

        $item->qty++;
        $item->price    = $this->resolvePrice($unit, $item->qty);
        $item->subtotal = ($item->price - $item->discount) * $item->qty;
        $item->save();

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
        $stock       = Stock::where('product_unit_id', $item->product_unit_id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stock || $request->qty > $stock->qty) {
            $valid = ($request->override_password ?? '') === env('POS_OVERRIDE_PASSWORD');
            if (!$valid) {
                return response()->json([
                    'success'       => false,
                    'need_override' => true,
                    'message'       => 'Stok tidak cukup, butuh password override',
                ]);
            }
        }

        $item->qty          = $request->qty;
        $item->price        = $this->resolvePrice($item->unit, $item->qty);
        $item->subtotal     = ($item->price - $item->discount) * $item->qty;
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

        $stock = Stock::where('product_unit_id', $unit->id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stock || $item->qty > $stock->qty) {
            $valid = ($request->override_password ?? '') === env('POS_OVERRIDE_PASSWORD');
            if (!$valid) {
                return response()->json([
                    'success'       => false,
                    'need_override' => true,
                    'message'       => 'Stok tidak cukup, butuh password override',
                ]);
            }
        }

        $item->product_unit_id = $unit->id;
        $item->price           = $unit->price;
        $item->subtotal        = ($unit->price - $item->discount) * $item->qty;
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
            'payment_method' => 'nullable|in:cash,transfer',
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

            // Ambil warehouse aktif sebagai fallback
            $activeWarehouseId = Warehouse::where('is_active', true)->value('id') ?? 1;

            $subtotal   = 0;
            $validItems = [];

            foreach ($trx->items as $item) {

                // ✅ FIX: Gunakan warehouse_id item, fallback ke warehouse aktif jika NULL atau 0
                $warehouseId = ($item->warehouse_id && $item->warehouse_id > 0)
                    ? $item->warehouse_id
                    : $activeWarehouseId;

                $stock = Stock::where('product_unit_id', $item->product_unit_id)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                // Jika masih tidak ketemu, coba cari stok manapun untuk produk ini
                if (!$stock) {
                    $stock = Stock::where('product_unit_id', $item->product_unit_id)
                        ->where('qty', '>', 0)
                        ->lockForUpdate()
                        ->first();
                }

                if (!$stock || $stock->qty < $item->qty) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$item->unit->product->name} hanya tersedia " . ($stock?->qty ?? 0),
                    ], 400);
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

            // Pending jika belum lunas
            if ($paid < $total) {
                $trx->update([
                    'member_id'      => $request->member_id,
                    'total'          => $total,
                    'paid'           => $paid,
                    'change'         => 0,
                    'status'         => 'pending',
                    'discount'       => $discountAmount,
                    'payment_method' => $request->payment_method ?? 'cash',
                ]);
                DB::commit();
                return response()->json(['success' => true, 'paid_off' => false, 'trx_id' => $trx->id]);
            }

            // Lunas: kurangi stok
            foreach ($validItems as $row) {
                $row['stock']->decrement('qty', $row['item']->qty);
            }

            $trx->update([
                'member_id'      => $request->member_id,
                'total'          => $total,
                'paid'           => $paid,
                'change'         => $paid - $total,
                'status'         => 'paid',
                'discount'       => $discountAmount,
                'payment_method' => $request->payment_method ?? 'cash',
            ]);

            // Update poin & level member
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
            'subtotal'     => ($i->price - $i->discount) * $i->qty,
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

        $subtotal    = $trx->items->sum(fn($item) => ($item->price - ($item->discount ?? 0)) * $item->qty);
        $trx->discount = round($subtotal * ($r->discount ?? 0) / 100, 0);
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

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}
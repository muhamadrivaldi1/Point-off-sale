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
use App\Models\User;
use App\Models\KreditPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PosController extends Controller
{
    // ================= HELPER TRANSAKSI AKTIF =================
    // Mengembalikan null jika tidak ditemukan — TIDAK pakai firstOrFail
    private function getActiveTransaction(Request $request): ?Transaction
    {
        if (!$request->filled('trx_id')) return null;

        return Transaction::where('id', $request->trx_id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();
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

        // Jika ada request new_transaction, buat transaksi baru lalu redirect
        if ($request->has('new_transaction')) {
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
            return redirect("/pos?trx_id={$trx->id}");
        }

        $trx = null;

        // Coba ambil dari trx_id di URL — hanya yang pending milik user ini
        if ($request->filled('trx_id')) {
            $trx = Transaction::where('id', $request->trx_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();
        }

        // Tidak ditemukan? Ambil pending terakhir milik user
        if (!$trx) {
            $trx = Transaction::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        // Masih tidak ada? Buat baru
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

    // ================= CEK STOK =================
    // Stok 0 / tidak ada = langsung izinkan.
    // Stok > 0 tapi kurang dari qty = butuh override.
    private function stockNeedsOverride(int $stok, int $newQty): bool
    {
        if ($stok <= 0) return false;
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

        $trx = $this->getActiveTransaction($request);
        if (!$trx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau sudah selesai. Silakan mulai transaksi baru.',
            ], 404);
        }

        $unit        = ProductUnit::findOrFail($request->product_unit_id);
        $warehouseId = $request->warehouse_id;

        $stok = Stock::where('product_unit_id', $unit->id)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0;

        $existingItem = TransactionItem::where('transaction_id', $trx->id)
            ->where('product_unit_id', $unit->id)
            ->first();

        $newQty = ($existingItem ? $existingItem->qty : 0) + 1;

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

        $item = null;
        DB::transaction(function () use ($trx, $unit, $warehouseId, &$item) {
            $item = TransactionItem::where('transaction_id', $trx->id)
                ->where('product_unit_id', $unit->id)
                ->lockForUpdate()
                ->first();

            if ($item) {
                $item->qty          = $item->qty + 1;
                $item->price        = $this->resolvePrice($unit, $item->qty);
                $item->subtotal     = ($item->price - ($item->discount ?? 0)) * $item->qty;
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

        $trx = $this->getActiveTransaction($request);
        if (!$trx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau sudah selesai.',
            ], 404);
        }

        $item = TransactionItem::with('unit')->find($request->item_id);
        if (!$item || $item->transaction_id !== $trx->id) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
        }

        $warehouseId = $request->warehouse_id;
        $stok        = Stock::where('product_unit_id', $item->product_unit_id)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0;

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

        $trx = $this->getActiveTransaction($request);
        if (!$trx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau sudah selesai.',
            ], 404);
        }

        $item = TransactionItem::find($request->item_id);
        if (!$item || $item->transaction_id !== $trx->id) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
        }

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
            'kredit_data'    => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $trx = Transaction::with('items.unit.product', 'member')
                ->lockForUpdate()
                ->findOrFail($request->trx_id);

            if ($trx->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah selesai atau bukan pending.',
                ], 400);
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

                if ($stock && $stock->qty > 0 && $stock->qty < $item->qty) {
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

            // ===== KREDIT: simpan tanpa bayar =====
            if ($isKredit) {
                foreach ($validItems as $row) {
                    $s = $row['stock'];
                    if ($s && $s->qty > 0) {
                        $s->decrement('qty', min($row['item']->qty, $s->qty));
                    }
                }

                // Ambil data kredit dari frontend
                $kd = $request->input('kredit_data', []);

                $trx->update([
                    'member_id'         => $request->member_id,
                    'total'             => $total,
                    'paid'              => 0,
                    'change'            => 0,
                    'status'            => 'kredit',
                    'discount'          => $discountAmount,
                    'payment_method'    => 'kredit',
                    'due_date'          => isset($kd['jatuh_tempo']) && $kd['jatuh_tempo'] ? $kd['jatuh_tempo'] : null,
                    'debtor_name'       => isset($kd['nama_peminjam']) ? trim($kd['nama_peminjam']) : null,
                    'debtor_phone'      => isset($kd['telepon']) ? trim($kd['telepon']) : null,
                    'payment_plan'      => $kd['cara_bayar'] ?? null,
                    'installment_count' => ($kd['cara_bayar'] ?? null) === 'cicilan' ? ($kd['cicilan'] ?? null) : null,
                    'kredit_notes'      => isset($kd['catatan']) ? trim($kd['catatan']) : null,
                ]);

                if ($member) {
                    $member->increment('total_spent', $total);
                    if ($member->total_spent >= 5000000)     $member->update(['level' => 'Gold',   'discount' => 5]);
                    elseif ($member->total_spent >= 1000000) $member->update(['level' => 'Silver', 'discount' => 2]);
                    else                                      $member->update(['level' => 'Basic',  'discount' => 0]);
                }

                $dpAmount = isset($kd['dp']) ? (int) round((float) $kd['dp'], 0) : 0;
                if ($dpAmount > 0 && $dpAmount <= $total) {
                    KreditPayment::create([
                        'transaction_id' => $trx->id,
                        'amount'         => $dpAmount,
                        'method'         => $kd['dp_method'] ?? 'cash',
                        'note'           => 'DP / Uang Muka',
                        'paid_at'        => now(),
                        'created_by'     => Auth::id(),
                    ]);
                }

                DB::commit();
                return response()->json([
                    'success'   => true,
                    'is_kredit' => true,
                    'trx_id'    => $trx->id,
                    'dp'        => $dpAmount,
                ]);
            }

            // ===== PEMBAYARAN BIASA: belum lunas =====
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

            // ===== PEMBAYARAN BIASA: lunas =====
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
        $trx = $this->getActiveTransaction($request);
        if (!$trx) {
            return response()->json(['items' => [], 'total' => 0]);
        }

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

    // ================= REOPEN PAID / KREDIT =================
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
            // Kembalikan stok
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

    // ================= KREDIT: TAMPILKAN =================
    public function showKredit($trx_id)
    {
        $trx = Transaction::with([
            'items.unit.product.units',
            'member',
            'payments',
        ])->findOrFail($trx_id);

        abort_if(!in_array($trx->status, ['kredit', 'paid']), 404);

        $totalTerbayar = $trx->payments->sum('amount');
        $sisa          = max($trx->total - $totalTerbayar, 0);

        return view('pos.kredit', compact('trx', 'totalTerbayar', 'sisa'));
    }

    // ================= KREDIT: SIMPAN CATATAN =================
    public function saveKreditNotes(Request $request)
    {
        $request->validate([
            'trx_id' => 'required|exists:transactions,id',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $trx = Transaction::findOrFail($request->trx_id);
        $trx->update(['notes' => $request->notes]);

        return redirect()
            ->route('pos.kredit.show', $trx->id)
            ->with('success', 'Catatan berhasil disimpan');
    }

    // ================= KREDIT: LUNASI =================
    public function lunasiKredit(Request $request)
    {
        $request->validate([
            'trx_id'   => 'required|exists:transactions,id',
            'password' => 'required|string',
            'method'   => 'required|in:cash,transfer,qris',
            'note'     => 'nullable|string|max:500',
        ]);

        $owner = User::where('role', 'owner')->first();

        if (!$owner || !Hash::check($request->password, $owner->password)) {
            return back()->with('error', 'Password owner salah!');
        }

        $trx = Transaction::with('payments')->findOrFail($request->trx_id);

        if ($trx->status !== 'kredit') {
            return back()->with('error', 'Transaksi bukan kredit atau sudah lunas.');
        }

        $totalTerbayar = $trx->payments->sum('amount');
        $sisa          = max($trx->total - $totalTerbayar, 0);

        KreditPayment::create([
            'transaction_id' => $trx->id,
            'amount'         => $sisa,
            'method'         => $request->method,
            'note'           => $request->note,
            'paid_at'        => now(),
            'created_by'     => Auth::id(),
        ]);

        $trx->update([
            'status'         => 'paid',
            'payment_method' => $request->method,
            'paid_at'        => now(),
        ]);

        return redirect()
            ->route('pos.kredit.show', $trx->id)
            ->with('success', 'Kredit berhasil dilunasi');
    }

    // ================= KREDIT: BAYAR SEBAGIAN =================
    public function partialPayKredit(Request $request)
    {
        $request->validate([
            'trx_id'   => 'required|exists:transactions,id',
            'password' => 'required|string',
            'amount'   => 'required|numeric|min:1',
            'method'   => 'required|in:cash,transfer,qris',
            'note'     => 'nullable|string|max:500',
        ]);

        $owner = User::where('role', 'owner')->first();

        if (!$owner || !Hash::check($request->password, $owner->password)) {
            return back()->with('error', 'Password owner salah!');
        }

        DB::transaction(function () use ($request, &$trx) {
            $trx = Transaction::lockForUpdate()->findOrFail($request->trx_id);

            if ($trx->status !== 'kredit') {
                abort(400, 'Transaksi bukan kredit');
            }

            $totalTerbayar = $trx->payments()->sum('amount');
            $sisa          = max($trx->total - $totalTerbayar, 0);
            $amountPaid    = min($request->amount, $sisa);

            KreditPayment::create([
                'transaction_id' => $trx->id,
                'amount'         => $amountPaid,
                'method'         => $request->method,
                'note'           => $request->note,
                'paid_at'        => now(),
                'created_by'     => Auth::id(),
            ]);

            // refresh relasi agar frontend dapat data baru
            $trx->load('payments');

            $totalBaru = $trx->payments->sum('amount');
            $sisaBaru  = max($trx->total - $totalBaru, 0);

            if ($sisaBaru <= 0) {
                $trx->update([
                    'status'         => 'paid',
                    'payment_method' => $request->method,
                    'paid_at'        => now(),
                ]);
            }
        });

        return redirect()
            ->route('pos.kredit.show', $request->trx_id)
            ->with('success', 'Pembayaran berhasil disimpan');
    }

    // ================= KREDIT: INDEX =================
    public function kreditIndex()
    {
        $kredits = Transaction::with('member', 'payments')
            ->where('status', 'kredit')
            ->latest()
            ->get();

        return view('pos.kredit_index', compact('kredits'));
    }

    // ================= KREDIT: PRINT =================
    public function printKredit($trx_id)
    {
        return redirect()->to('/transactions/' . $trx_id . '/struk');
    }

    public function bayarTagihan(\Illuminate\Http\Request $request)
    {
        try {
            $trx = \App\Models\Transaction::create([
                'trx_number'     => 'TGH-' . date('Ymd') . '-' . str_pad(
                    \App\Models\Transaction::whereDate('created_at', today())
                        ->where('status', 'bayar_tagihan')->count() + 1,
                    3,
                    '0',
                    STR_PAD_LEFT
                ),
                'user_id'        => auth()->id(),
                'warehouse_id'   => auth()->user()->active_warehouse_id ?? 1,
                'status'         => 'bayar_tagihan',
                'total'          => $request->total,
                'payment_method' => $request->metode_bayar,
                'notes'          => [
                    'kategori'    => $request->kategori,
                    'nominal'     => $request->nominal,
                    'biaya_admin' => $request->biaya_admin ?? 0,
                    'no_rekening' => $request->no_rekening,
                    'periode'     => $request->periode,
                    'nama_bayar'  => $request->nama_bayar,
                    'catatan'     => $request->catatan,
                ],
            ]);

            return response()->json([
                'success'    => true,
                'trx_id'     => $trx->id,
                'trx_number' => $trx->trx_number,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function tagihanToday()
    {
        $list = \App\Models\Transaction::where('status', 'bayar_tagihan')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($t) {
                $notes = $t->notes ?? [];
                return [
                    'trx_number'   => $t->trx_number,
                    'kategori'     => $notes['kategori']    ?? '—',
                    'total'        => $t->total,
                    'metode_bayar' => $t->payment_method,
                    'no_rekening'  => $notes['no_rekening'] ?? '',
                    'nama_bayar'   => $notes['nama_bayar']  ?? '',
                    'time'         => $t->created_at->format('H:i'),
                ];
            });

        return response()->json(['tagihanList' => $list]);
    }
}

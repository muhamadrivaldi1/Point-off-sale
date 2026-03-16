<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LIST RETUR PEMBELIAN
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $returns = PurchaseReturn::with('purchase')
            ->latest()
            ->paginate(10);

        return view('purchase_returns.index', compact('returns'));
    }


    /*
    |--------------------------------------------------------------------------
    | FORM RETUR DARI PURCHASE ORDER
    |--------------------------------------------------------------------------
    */
    public function create($po)
    {
        $po = PurchaseOrder::with('items.unit.product')->findOrFail($po);

        return view('purchase_returns.create', compact('po'));
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN RETUR
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Hanya Owner yang dapat mengajukan retur pembelian.');
        }

        $request->validate([
            'purchase_id'             => 'required|exists:purchase_orders,id',
            'items'                   => 'required|array',
            'items.*.qty'             => 'nullable|numeric|min:0',
            'items.*.product_unit_id' => 'required',
        ]);

        $count = 0;

        try {
            DB::transaction(function () use ($request, &$count) {

                // Ambil warehouse aktif sekali saja di luar loop
                $activeWarehouseId = \App\Models\Warehouse::where('is_active', 1)->value('id');

                foreach ($request->items as $item) {
                    if (!isset($item['qty']) || $item['qty'] <= 0) continue;

                    // 1. Ambil stok — cek location='toko' ATAU warehouse aktif
                    $stock = Stock::where('product_unit_id', $item['product_unit_id'])
                        ->where(function ($q) use ($activeWarehouseId) {
                            $q->where('location', 'toko');
                            if ($activeWarehouseId) {
                                $q->orWhere('warehouse_id', $activeWarehouseId);
                            }
                        })
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception('Stok produk tidak ditemukan.');
                    }

                    if ($stock->qty < $item['qty']) {
                        throw new \Exception('Stok tidak mencukupi. Tersedia: ' . $stock->qty . ', diminta: ' . $item['qty']);
                    }

                    $before = $stock->qty;

                    // 2. Potong stok
                    $stock->qty -= $item['qty'];
                    $stock->save();

                    $after = $stock->qty;

                    // 3. Catat mutasi stok
                    StockMutation::create([
                        'unit_id'      => $item['product_unit_id'],
                        'user_id'      => Auth::id(),
                        'type'         => 'out',
                        'status'       => 'retur_pembelian',
                        'qty'          => $item['qty'],
                        'stock_before' => $before,
                        'stock_after'  => $after,
                        'reference'    => 'RET-PUR-' . $request->purchase_id,
                        'description'  => 'Retur pembelian ke supplier',
                    ]);

                    // 4. Simpan retur dengan status approved langsung
                    PurchaseReturn::create([
                        'purchase_id'     => $request->purchase_id,
                        'product_unit_id' => $item['product_unit_id'],
                        'qty'             => $item['qty'],
                        'reason'          => $item['reason'] ?? 'Tanpa alasan',
                        'user_id'         => Auth::id(),
                        'status'          => 'approved',
                    ]);

                    $count++;
                }
            });

            if ($count == 0) {
                return redirect()->back()->with('error', 'Masukkan setidaknya 1 jumlah barang.');
            }

            return redirect()->route('purchase_returns.index')
                ->with('success', 'Berhasil meretur ' . $count . ' item. Stok sudah terpotong.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE RETUR
    |--------------------------------------------------------------------------
    */
    public function approve($id)
    {
        try {

            DB::transaction(function () use ($id) {

                $return = PurchaseReturn::lockForUpdate()->findOrFail($id);

                if ($return->status != 'pending') {
                    throw new \Exception('Retur sudah diproses');
                }

                $stock = Stock::where('product_unit_id', $return->product_unit_id)
                    ->where('location', 'toko')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($stock->qty < $return->qty) {
                    throw new \Exception('Stok tidak mencukupi');
                }

                $before = $stock->qty;

                $stock->qty -= $return->qty;
                $stock->save();

                $after = $stock->qty;

                StockMutation::create([
                    'unit_id' => $return->product_unit_id,
                    'user_id' => Auth::id(),
                    'type' => 'out',
                    'status' => 'retur_pembelian',
                    'qty' => $return->qty,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reference' => 'RET-PUR-' . $return->purchase_id,
                    'description' => 'Retur pembelian ke supplier'
                ]);

                $return->update([
                    'status' => 'approved'
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Retur pembelian disetujui'
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT RETUR
    |--------------------------------------------------------------------------
    */
    public function reject($id)
    {
        $return = PurchaseReturn::findOrFail($id);

        if ($return->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Retur sudah diproses'
            ], 422);
        }

        $return->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Retur pembelian ditolak'
        ]);
    }
}

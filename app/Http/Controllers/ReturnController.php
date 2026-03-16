<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ReturnItem;
use App\Models\Stock;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    /**
     * Halaman daftar transaksi yang bisa diretur
     */
    public function index()
    {
        $transactions = Transaction::with([
            'items.unit.product',
            'items.returns'
        ])
        ->where('status', 'paid')
        ->latest()
        ->paginate(10);

        return view('returns.index', compact('transactions'));
    }


    /**
     * Kasir mengajukan retur
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_item_id' => 'required|exists:transaction_items,id',
            'qty' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:255'
        ]);

        try {

            DB::transaction(function () use ($request) {

                $item = TransactionItem::with('unit')
                    ->lockForUpdate()
                    ->findOrFail($request->transaction_item_id);

                if ($request->qty > $item->qty) {
                    throw new \Exception('Jumlah retur melebihi jumlah pembelian');
                }

                // cek retur pending
                if ($item->returns()->where('status', 'pending')->exists()) {
                    throw new \Exception('Item ini sudah memiliki pengajuan retur yang menunggu persetujuan');
                }

                // hitung total yang sudah diretur
                $returnedQty = $item->returns()
                    ->where('status', 'approved')
                    ->sum('qty');

                $availableQty = $item->qty - $returnedQty;

                if ($request->qty > $availableQty) {
                    throw new \Exception("Jumlah retur melebihi sisa barang yang bisa diretur. Tersedia: {$availableQty}");
                }

                ReturnItem::create([
                    'transaction_id'      => $item->transaction_id,
                    'transaction_item_id' => $item->id,
                    'product_unit_id'     => $item->product_unit_id,
                    'qty'                 => $request->qty,
                    'price'               => $item->price,
                    'subtotal'            => $request->qty * $item->price,
                    'reason'              => $request->reason,
                    'user_id'             => Auth::id(),
                    'status'              => 'pending'
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan retur berhasil dikirim. Menunggu persetujuan owner.'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    /**
     * Owner menyetujui retur
     */
    public function approve($id)
    {
        $this->onlyOwner();

        try {

            DB::transaction(function () use ($id) {

                $return = ReturnItem::where('status', 'pending')
                    ->lockForUpdate()
                    ->findOrFail($id);

                $return->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);


                /**
                 * Ambil stok produk
                 */
                $stock = Stock::where('product_unit_id', $return->product_unit_id)
                    ->where('location', 'toko')
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw new \Exception('Data stok tidak ditemukan.');
                }

                $beforeStock = $stock->qty;


                /**
                 * Tambah stok karena barang diretur
                 */
                $stock->qty += $return->qty;
                $stock->save();

                $afterStock = $stock->qty;


                /**
                 * Catat mutasi stok
                 */
                StockMutation::create([
                    'unit_id'       => $return->product_unit_id,
                    'user_id'       => Auth::id(),
                    'type'          => 'in',
                    'status'        => 'retur_penjualan',
                    'qty'           => $return->qty,
                    'stock_before'  => $beforeStock,
                    'stock_after'   => $afterStock,
                    'reference'     => 'RET-' . $return->transaction_id,
                    'description'   => 'Retur penjualan disetujui'
                ]);


                /**
                 * Update item transaksi
                 */
                $item = TransactionItem::lockForUpdate()
                    ->find($return->transaction_item_id);

                if ($item) {

                    $item->qty -= $return->qty;

                    if ($item->qty < 0) {
                        $item->qty = 0;
                    }

                    $item->subtotal = $item->qty * $item->price;
                    $item->save();

                    /**
                     * Update total transaksi
                     */
                    $trx = Transaction::lockForUpdate()
                        ->find($return->transaction_id);

                    if ($trx) {
                        $trx->total = $trx->items()->sum('subtotal');
                        $trx->save();
                    }
                }

            });

            return response()->json([
                'success' => true,
                'message' => 'Retur berhasil disetujui'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    /**
     * Owner menolak retur
     */
    public function reject($id)
    {
        $this->onlyOwner();

        try {

            $return = ReturnItem::where('status', 'pending')
                ->findOrFail($id);

            $return->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Retur ditolak'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Hanya owner yang boleh approve/reject
     */
    private function onlyOwner()
    {
        abort_if(Auth::user()->role !== 'owner', 403, 'Akses ditolak');
    }
}
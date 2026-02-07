<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ReturnItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    // Halaman daftar transaksi
    public function index()
    {
        // Ambil semua transaksi yang sudah dibayar, beserta item & retur
        $transactions = Transaction::with(['items.unit.product', 'items.returns'])
            ->where('status', 'paid')
            ->latest()
            ->paginate(10);

        return view('returns.index', compact('transactions'));
    }

    // Kasir ajukan retur
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

                // Cek apakah qty retur valid
                if ($request->qty > $item->qty) {
                    throw new \Exception('Jumlah retur melebihi jumlah pembelian');
                }

                // Cek apakah sudah ada retur pending untuk item ini
                if ($item->returns()->where('status', 'pending')->exists()) {
                    throw new \Exception('Item ini sudah memiliki pengajuan retur yang menunggu persetujuan');
                }

                // Hitung total qty yang sudah diretur (approved)
                $totalReturned = $item->returns()->where('status', 'approved')->sum('qty');
                $availableQty = $item->qty - $totalReturned;

                if ($request->qty > $availableQty) {
                    throw new \Exception("Jumlah retur melebihi sisa barang yang bisa diretur. Tersedia: {$availableQty}");
                }

                // Simpan pengajuan retur dengan status pending
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

                // JANGAN update stok atau item di sini
                // Stok akan diupdate saat owner approve
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

    // Owner approve retur
    public function approve($id)
    {
        $this->onlyOwner();

        try {
            DB::transaction(function () use ($id) {
                $return = ReturnItem::where('status', 'pending')
                    ->lockForUpdate()
                    ->findOrFail($id);

                // Update status retur menjadi approved
                $return->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);

                // Update stok toko (kembalikan barang)
                Stock::where('product_unit_id', $return->product_unit_id)
                    ->where('location', 'toko')
                    ->increment('qty', $return->qty);

                // Update transaction item (kurangi qty)
                $item = TransactionItem::lockForUpdate()->find($return->transaction_item_id);
                
                if ($item) {
                    $item->qty -= $return->qty;
                    $item->subtotal = $item->qty * $item->price;
                    
                    if ($item->qty <= 0) {
                        $item->delete();
                    } else {
                        $item->save();
                    }

                    // Update total transaksi
                    $trx = Transaction::find($return->transaction_id);
                    $trx->total = $trx->items()->sum('subtotal');
                    $trx->save();
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

    // Owner reject retur
    public function reject($id)
    {
        $this->onlyOwner();

        try {
            $return = ReturnItem::where('status', 'pending')->findOrFail($id);

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

    private function onlyOwner()
    {
        abort_if(Auth::user()->role !== 'owner', 403, 'Akses ditolak');
    }
}
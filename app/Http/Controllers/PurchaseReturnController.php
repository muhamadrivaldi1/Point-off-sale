<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{
    /**
     * Tampilkan Riwayat Retur
     */
    public function index()
    {
        $returns = PurchaseReturn::with(['purchase', 'productUnit.product'])
            ->latest()
            ->paginate(10);

        return view('purchase_returns.index', compact('returns'));
    }

    /**
     * Form Buat Retur
     */
    public function create($id)
    {
        // Load PO beserta item dan relasi produknya
        $po = PurchaseOrder::with('items.unit.product')->findOrFail($id);

        return view('purchase_returns.create', compact('po'));
    }

    /**
     * Simpan Data Retur & Potong Stok
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

        try {
            $processedCount = 0;

            DB::transaction(function () use ($request, &$processedCount) {
                $activeWarehouseId = Warehouse::where('is_active', 1)->value('id');
                
                // --- LANGKAH 1: GROUPING ---
                // Jika user input Roti Sobek di baris 1 (qty:1) dan baris 2 (qty:1), 
                // kita gabungkan jadi satu ID dengan total qty: 2.
                $groupedItems = [];
                foreach ($request->items as $item) {
                    $qty = (float) ($item['qty'] ?? 0);
                    if ($qty <= 0) continue;

                    $unitId = $item['product_unit_id'];
                    if (!isset($groupedItems[$unitId])) {
                        $groupedItems[$unitId] = [
                            'qty'    => 0,
                            'reason' => $item['reason'] ?? 'Tanpa alasan',
                        ];
                    }
                    $groupedItems[$unitId]['qty'] += $qty;
                }

                if (empty($groupedItems)) {
                    throw new \Exception('Masukkan setidaknya satu jumlah barang yang valid.');
                }

                // --- LANGKAH 2: PROSES STOK PER PRODUK ---
                foreach ($groupedItems as $unitId => $data) {
                    $totalQtyToReturn = $data['qty'];

                    // Cari stok di Toko terlebih dahulu (Prioritas Utama)
                    $stock = Stock::where('product_unit_id', $unitId)
                        ->where('location', 'toko')
                        ->lockForUpdate()
                        ->first();

                    // Jika di toko tidak ada, cari di Warehouse Aktif (Prioritas Kedua)
                    if (!$stock && $activeWarehouseId) {
                        $stock = Stock::where('product_unit_id', $unitId)
                            ->where('warehouse_id', $activeWarehouseId)
                            ->lockForUpdate()
                            ->first();
                    }

                    if (!$stock) {
                        throw new \Exception("Stok untuk produk tersebut tidak ditemukan di Toko maupun Gudang.");
                    }

                    if ($stock->qty < $totalQtyToReturn) {
                        throw new \Exception("Stok tidak mencukupi. Tersedia: {$stock->qty}, Retur diminta: {$totalQtyToReturn}");
                    }

                    // --- LANGKAH 3: EKSEKUSI POTONG STOK ---
                    $before = $stock->qty;
                    $stock->qty -= $totalQtyToReturn;
                    $stock->save();
                    $after = $stock->qty;

                    // --- LANGKAH 4: CATAT MUTASI ---
                    StockMutation::create([
                        'unit_id'      => $unitId,
                        'user_id'      => Auth::id(),
                        'type'         => 'out',
                        'status'       => 'retur_pembelian',
                        'qty'          => $totalQtyToReturn,
                        'stock_before' => $before,
                        'stock_after'  => $after,
                        'reference'    => 'RET-PUR-' . $request->purchase_id,
                        'description'  => 'Retur pembelian: ' . $data['reason'],
                    ]);

                    // --- LANGKAH 5: SIMPAN KE TABEL RETUR ---
                    PurchaseReturn::create([
                        'purchase_id'     => $request->purchase_id,
                        'product_unit_id' => $unitId,
                        'qty'             => $totalQtyToReturn,
                        'reason'          => $data['reason'],
                        'user_id'         => Auth::id(),
                        'status'          => 'approved',
                    ]);

                    $processedCount++;
                }
            });

            return redirect()->route('purchase_returns.index')
                ->with('success', "Berhasil memproses retur untuk $processedCount jenis produk.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Approve manual (Jika status awalnya pending)
     */
    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $return = PurchaseReturn::lockForUpdate()->findOrFail($id);

                if ($return->status !== 'pending') {
                    throw new \Exception('Retur sudah diproses sebelumnya.');
                }

                // Logika pemotongan stok sama seperti di store...
                $stock = Stock::where('product_unit_id', $return->product_unit_id)
                    ->where('location', 'toko')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($stock->qty < $return->qty) {
                    throw new \Exception('Stok di Toko tidak mencukupi untuk persetujuan ini.');
                }

                $before = $stock->qty;
                $stock->qty -= $return->qty;
                $stock->save();

                StockMutation::create([
                    'unit_id'      => $return->product_unit_id,
                    'user_id'      => Auth::id(),
                    'type'         => 'out',
                    'status'       => 'retur_pembelian',
                    'qty'          => $return->qty,
                    'stock_before' => $before,
                    'stock_after'  => $stock->qty,
                    'reference'    => 'RET-PUR-' . $return->purchase_id,
                    'description'  => 'Persetujuan retur: ' . $return->reason
                ]);

                $return->update(['status' => 'approved']);
            });

            return response()->json(['success' => true, 'message' => 'Retur disetujui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reject($id)
    {
        $return = PurchaseReturn::findOrFail($id);
        if ($return->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Hanya status pending yang bisa ditolak.'], 422);
        }

        $return->update(['status' => 'rejected']);
        return response()->json(['success' => true, 'message' => 'Retur berhasil ditolak.']);
    }
}
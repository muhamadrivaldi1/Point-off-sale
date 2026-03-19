<?php

namespace App\Observers;

use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\StockMutation;

class PurchaseOrderItemObserver
{
    public function created(PurchaseOrderItem $item)
    {
        $po = $item->purchaseOrder;

        if (!$po) return;

        // ✅ Skip jika masih DRAFT — belum boleh mencatat stok
        if ($po->status === 'draft') return;

        // ✅ Skip jika jenis_transaksi adalah 'PO' (Private Order) — tidak menambah stok
        if ($po->jenis_transaksi === 'PO') return;

        // ✅ PR + status approved/received → catat stok masuk
        $this->tambahStokDanMutasi($item, $po);
    }

    /**
     * Helper: tambah stok dan catat mutasi
     */
    public static function tambahStokDanMutasi(PurchaseOrderItem $item, $po): void
    {
        $stock = Stock::firstOrCreate(
            ['product_unit_id' => $item->product_unit_id],
            ['qty' => 0]
        );

        $before = $stock->qty;
        $after  = $before + $item->qty;

        $stock->update(['qty' => $after]);

        StockMutation::create([
            'unit_id'      => $item->product_unit_id,
            'user_id'      => $po->user_id ?? null,
            'type'         => 'in',
            'status'       => 'pembelian',
            'qty'          => $item->qty,
            'stock_before' => $before,
            'stock_after'  => $after,
            'reference'    => $po->po_number ?? '-',
            'description'  => 'Pembelian Barang',
        ]);
    }
}
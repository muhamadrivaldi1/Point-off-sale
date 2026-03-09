<?php

namespace App\Observers;

use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\StockMutation;

class PurchaseOrderItemObserver
{
    public function created(PurchaseOrderItem $item)
    {
        $stock = Stock::firstOrCreate(
            ['product_unit_id' => $item->product_unit_id],
            ['qty' => 0]
        );

        $before = $stock->qty;
        $after = $before + $item->qty;

        $stock->update([
            'qty' => $after
        ]);

        StockMutation::create([
            'unit_id' => $item->product_unit_id,
            'user_id' => $item->purchaseOrder->user_id ?? null,
            'type' => 'in',
            'qty' => $item->qty,
            'stock_before' => $before,
            'stock_after' => $after,
            'reference' => $item->purchaseOrder->po_number ?? '-',
            'description' => 'Pembelian Barang'
        ]);
    }
}
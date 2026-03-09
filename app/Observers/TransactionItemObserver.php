<?php

namespace App\Observers;

use App\Models\TransactionItem;
use App\Models\Stock;
use App\Models\StockMutation;

class TransactionItemObserver
{

    public function created(TransactionItem $item)
    {
        $stock = Stock::where('product_unit_id',$item->product_unit_id)->first();

        if(!$stock) return;

        $before = $stock->qty;
        $after = $before - $item->qty;

        $stock->update([
            'qty'=>$after
        ]);

        StockMutation::create([
            'unit_id'=>$item->product_unit_id,
            'user_id'=>$item->transaction->user_id,
            'type'=>'out',
            'qty'=>$item->qty,
            'stock_before'=>$before,
            'stock_after'=>$after,
            'reference'=>$item->transaction->trx_number,
            'description'=>'Penjualan POS'
        ]);
    }

}
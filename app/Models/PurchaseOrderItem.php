<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'product_unit_id',
        'qty',
        'price',
        'bonus_nama',
        'bonus_qty',
    ];

    protected $casts = [
        'qty'       => 'decimal:2',
        'price'     => 'decimal:2',
        'bonus_qty' => 'decimal:2',
    ];

    // -----------------------------------------------
    // RELASI
    // -----------------------------------------------

    /**
     * Relasi ke PurchaseOrder
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
    /**
     * Relasi ke ProductUnit (barang yang dibeli)
     * Akses: $item->unit->product->name
     */
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    /**
     * Alias — akses: $item->productUnit->product->name
     */
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}

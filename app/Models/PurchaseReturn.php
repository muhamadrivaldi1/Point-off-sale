<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'purchase_id', 
        'product_unit_id', 
        'qty', 
        'reason', 
        'user_id', 
        'status'
    ];

    /**
     * Relasi ke Purchase Order
     */
    public function purchase(): BelongsTo
    {
        // Pastikan nama modelnya adalah PurchaseOrder
        return $this->belongsTo(PurchaseOrder::class, 'purchase_id');
    }

    /**
     * Relasi ke Product Unit
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
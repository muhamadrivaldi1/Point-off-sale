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
        'diskon_persen',  // ✅ FIX — sebelumnya tidak ada, makanya diskon tidak tersimpan
        'bonus_nama',
        'bonus_qty',
    ];

    protected $casts = [
        'qty'           => 'decimal:2',
        'price'         => 'decimal:2',
        'diskon_persen' => 'decimal:2',  // ✅ FIX — cast supaya konsisten
        'bonus_qty'     => 'decimal:2',
    ];

    // -----------------------------------------------
    // RELASI
    // -----------------------------------------------

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
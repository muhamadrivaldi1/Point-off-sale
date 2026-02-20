<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'is_bkp',
    ];

    protected $casts = [
        'is_bkp' => 'boolean',
    ];

    // Relasi ke product_units
    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    // Relasi ke purchase_order_items (lewat product_units)
    public function purchaseOrderItems()
    {
        return $this->hasManyThrough(PurchaseOrderItem::class, ProductUnit::class);
    }
}
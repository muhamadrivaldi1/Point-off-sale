<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'is_bkp',
        'supplier_id', // TAMBAHKAN INI AGAR BISA DISIMPAN
    ];

    protected $casts = [
        'is_bkp' => 'boolean',
    ];

    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi ke ProductUnit
     */
    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Relasi ke purchase_order_items (lewat product_units)
     */
    public function purchaseOrderItems(): HasManyThrough
    {
        return $this->hasManyThrough(PurchaseOrderItem::class, ProductUnit::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'unit_id',
        'price_type',
        'min_qty',
        'price'
    ];

    /**
     * Relasi ke unit produk
     */
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    /**
     * Relasi ke produk utama
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

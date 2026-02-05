<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion',
        'barcode',
        'price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->hasMany(Stock::class, 'product_unit_id');
    }

    public function priceRules()
    {
        return $this->hasMany(PriceRule::class);
    }

    public function getPriceByQty($qty)
    {
        $rule = $this->priceRules()
            ->where('min_qty', '<=', $qty)
            ->orderBy('min_qty', 'desc')
            ->first();

        return $rule ? $rule->price : $this->price;
    }

    public function stokToko()
    {
        return $this->stock()
            ->where('location', 'toko')
            ->sum('qty');
    }
}

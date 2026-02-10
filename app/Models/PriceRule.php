<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRule extends Model
{
    // Tabel yang digunakan
    protected $table = 'product_prices';

    // Mass assignable fields
    protected $fillable = [
        'unit_id',
        'min_qty',
        'price',
        'price_type'
    ];

    // Relasi ke ProductUnit
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }
}

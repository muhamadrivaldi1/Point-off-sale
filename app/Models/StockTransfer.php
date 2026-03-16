<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'product_unit_id',
        'qty',
        'from_location',
        'to_location',
        'user_id',
        'reference'
    ];

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}

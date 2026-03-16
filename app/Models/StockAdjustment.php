<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = ['product_unit_id', 'location', 'system_qty', 'physical_qty', 'adjustment_qty', 'note', 'user_id'];

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    protected $fillable = [
    'unit_id', 
    'user_id', 
    'type', 
    'status', 
    'qty', 
    'stock_before', 
    'stock_after', 
    'reference', 
    'description'
];

    /**
     * Relasi ke ProductUnit
     */
    public function unit(): BelongsTo
    {
        // Kita gunakan ProductUnit karena itu nama model Anda
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
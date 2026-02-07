<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_unit_id',
        'qty',
        'price',
        'subtotal',
        'verified'
    ];

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(\App\Models\ReturnItem::class, 'transaction_item_id');
    }
    public function hasPendingReturn(): bool
    {
        return $this->returns()->where('status', 'pending')->exists();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnItem extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'transaction_id',
        'transaction_item_id',
        'product_unit_id',
        'qty',
        'price',
        'subtotal',
        'reason',
        'user_id',
        'status',
        'approved_by',
        'approved_at'
    ];

    // Relasi ke transaksi
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke unit produk
    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    // Relasi ke returns (bisa ada banyak retur untuk 1 item)
    public function returns(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'transaction_item_id');
    }

    // Helper untuk cek apakah ada retur pending
    public function hasPendingReturn()
    {
        return $this->returns()->where('status', 'pending')->exists();
    }

    // Helper untuk mendapatkan retur pending
    public function getPendingReturn()
    {
        return $this->returns()->where('status', 'pending')->first();
    }

    // Helper untuk mendapatkan total qty yang sudah diretur (approved)
    public function getTotalReturnedQty()
    {
        return $this->returns()->where('status', 'approved')->sum('qty');
    }

    // Helper untuk mendapatkan qty yang masih bisa diretur
    public function getAvailableReturnQty()
    {
        return $this->qty - $this->getTotalReturnedQty();
    }
}

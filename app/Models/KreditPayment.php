<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KreditPayment extends Model
{
    use HasFactory;

    protected $table = 'kredit_payments';

    protected $fillable = [
        'transaction_id',
        'amount',
        'method',
        'note',
        'paid_at',
        'created_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'cash'     => 'Tunai',
            'transfer' => 'Transfer Bank',
            'qris'     => 'QRIS',
            default    => ucfirst($this->method),
        };
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionRequest extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'message',
        'status',
        'approved_by',
        'approved_at',
        'message',
        'transaction_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

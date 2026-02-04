<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionRequest extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'message',
        'status'
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

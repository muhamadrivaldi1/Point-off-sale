<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'trx_number',
        'user_id',
        'member_id',
        'used_points',
        'point_value',
        'total',
        'discount',
        'paid',
        'change',
        'payment_method',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requests()
    {
        return $this->hasMany(TransactionRequest::class);
    }

    public function payments()
    {
    return $this->hasMany(KreditPayment::class, 'transaction_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    protected $casts = [
        'total'        => 'float',
        'discount'     => 'float',
        'paid'         => 'float',
        'change'       => 'float',
        'point_value'  => 'float',
        'used_points'  => 'integer',
    ];
}

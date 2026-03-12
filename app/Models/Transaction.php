<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'trx_number',
        'user_id',
        'warehouse_id',
        'member_id',
        'used_points',
        'point_value',
        'total',
        'discount',
        'paid',
        'change',
        'payment_method',
        'notes',
        'status',
        'account_id',
    ];

    protected $casts = [
        'total'       => 'float',
        'discount'    => 'float',
        'paid'        => 'float',
        'change'      => 'float',
        'point_value' => 'float',
        'used_points' => 'integer',
        'notes'       => 'array',   
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
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
        return $this->hasMany(\App\Models\KreditPayment::class, 'transaction_id');
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'reference', 'id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function cicilan()
    {
        return $this->hasMany(KreditPayment::class, 'transaction_id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    public function account() {
        return $this->belongsTo(Account::class);
    }
}
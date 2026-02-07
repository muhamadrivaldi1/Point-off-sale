<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashierSession extends Model
{
    protected $fillable = ['user_id', 
    'opening_balance', 
    'closing_balance', 
    'status', 
    'opened_at', 
    'closed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

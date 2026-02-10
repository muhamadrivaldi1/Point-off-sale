<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
    'name', 'phone', 'address', 'level', 'discount', 'total_spent', 'points', 'status'
];


    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

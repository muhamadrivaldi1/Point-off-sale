<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'sku', 'is_bkp'];

    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
}

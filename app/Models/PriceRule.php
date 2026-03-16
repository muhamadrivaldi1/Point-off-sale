<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRule extends Model
{
    // Tabel yang digunakan sesuai dengan database Anda
    protected $table = 'product_prices';

    // Tambahkan product_id ke dalam mass assignable fields
    protected $fillable = [
        'product_id',   // Tambahkan ini!
        'unit_id',
        'min_qty',
        'price',
        'price_type'
    ];

    /**
     * Relasi ke ProductUnit
     * Menghubungkan harga grosir dengan satuan produk tertentu
     */
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    /**
     * Relasi ke Product (Opsional tapi berguna)
     * Menghubungkan harga langsung ke master produk
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
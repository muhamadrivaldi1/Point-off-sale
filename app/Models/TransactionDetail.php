<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    // Pastikan table name-nya benar jika bukan 'transaction_details'
    protected $table = 'transaction_details'; 

    protected $fillable = [
        'transaction_id', 'product_id', 'product_unit_id', // Tambahkan unit id jika ada
        'qty', 'purchase_price', 'selling_price', 'subtotal'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Tambahkan relasi unit jika Controller memanggil 'items.unit'
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
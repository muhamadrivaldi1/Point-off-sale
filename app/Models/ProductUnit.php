<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    protected $table = 'product_units'; // opsional, default Laravel sudah sesuai

    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion',
        'barcode',
        'price',
    ];

    /**
     * Relasi ke Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke Stock
     */
    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class, 'product_unit_id');
    }

    /**
     * Relasi ke harga bertingkat
     */
    public function priceRules(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'unit_id');
    }

    /**
     * Ambil harga berdasarkan qty dan type (retail, wholesale, member)
     */
    public function getPriceByQty(int $qty, string $price_type = 'retail'): float
    {
        $rule = $this->priceRules()
            ->where('min_qty', '<=', $qty)
            ->where('price_type', $price_type)
            ->orderBy('min_qty', 'desc')
            ->first();

        return $rule ? (float) $rule->price : (float) $this->price;
    }

    /**
     * Jumlah stok di toko
     */
    public function stokToko(): int
    {
        return (int) $this->stock()
            ->where('location', 'toko')
            ->sum('qty');
    }
}

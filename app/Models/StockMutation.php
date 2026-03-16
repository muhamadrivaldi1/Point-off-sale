<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    protected $table = 'stock_mutations';

    protected $fillable = [
        'unit_id',
        'user_id',
        'type',
        'status',
        'qty',
        'stock_before',
        'stock_after',
        'reference',
        'description'
    ];

    protected $casts = [
        'qty' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer'
    ];


    /*
    |--------------------------------------------------------------------------
    | STATUS CONSTANT
    |--------------------------------------------------------------------------
    */

    const STATUS_PEMBELIAN = 'pembelian';
    const STATUS_PENJUALAN = 'penjualan';
    const STATUS_RETUR_PENJUALAN = 'retur_penjualan';
    const STATUS_RETUR_PEMBELIAN = 'retur_pembelian';
    const STATUS_MUTASI = 'mutasi';
    const STATUS_OPNAME = 'opname';


    /**
     * Relasi ke ProductUnit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }


    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
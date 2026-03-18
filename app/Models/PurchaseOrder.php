<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'user_id',
        'supplier_id',
        'tanggal',
        'nomor_faktur',
        'tanggal_faktur',
        'jenis_transaksi',
        'jenis_pembayaran',
        'jk_waktu',
        'tanggal_jatuh_tempo',
        'ppn',
        'disc_nota_persen',
        'disc_nota_rupiah',
        'total',            // ← satu-satunya kolom total yang dipakai
        'status',
        'keterangan',
        'gudang',
        'bulan_lapor',
    ];

    protected $casts = [
        'tanggal'             => 'date',
        'tanggal_faktur'      => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'total'               => 'decimal:2',
        'ppn'                 => 'decimal:2',
    ];

    // ── Relasi ────────────────────────────────────────────────
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }
    
    // ── Scopes ────────────────────────────────────────────────
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal', now()->month)
                     ->whereYear('tanggal', now()->year);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeBelumLunas($query)
    {
        return $query->whereNotIn('status', [
            'paid', 'received', 'Received', 'cancelled', 'canceled'
        ]);
    }
}
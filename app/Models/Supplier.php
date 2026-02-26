<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
    'kode_supplier',
    'nama_supplier',
    'npwp',
    'alamat',
    'telepon',
    'telepon2',
    'fax',
    'email',
    'bank',
    'nomor_rekening',
    'cp',
    'jabatan_cp',
    'telepon_cp',
    'nomor_seri_fp',
];

    // Relasi ke purchase_orders
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];

    // ================= RELASI KE PERMISSION =================
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    // ================= RELASI KE USER =================
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
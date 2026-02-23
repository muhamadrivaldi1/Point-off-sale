<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name'];

    // ================= RELASI KE ROLE =================
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // ================= RELASI KE USER (direct) =================
    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }
}
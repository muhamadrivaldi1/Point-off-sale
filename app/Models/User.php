<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'kasir_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ================= ROLES =================
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // ================= DIRECT PERMISSIONS =================
    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    // ================= HELPER: cek punya role =================
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    // ================= HELPER: cek punya permission =================
    // Cek dari directPermissions (yang disync saat assign role)
    public function hasPermission(string $permission): bool
    {
        return $this->directPermissions->contains('name', $permission);
    }

    // ================= CASHIER =================
    public function cashierSessions()
    {
        return $this->hasMany(\App\Models\CashierSession::class, 'user_id');
    }
}
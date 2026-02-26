<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\CashierSession; // Pastikan ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Tambahkan ini agar Auth::id() dikenali

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'directPermissions')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('users.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6',
            'role'        => 'required|in:owner,kasir',
            'kasir_level' => 'required_if:role,kasir|nullable|in:full,custom',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'        => $request->name,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'role'        => $request->role,
                'kasir_level' => ($request->role === 'kasir') ? $request->kasir_level : null,
            ]);

            $role = Role::firstOrCreate(['name' => $request->role]);
            $user->roles()->sync([$role->id]);

            if ($request->role === 'owner' || $request->kasir_level === 'full') {
                $allPermissions = Permission::pluck('id')->toArray();
                $user->directPermissions()->sync($allPermissions);
            } else {
                $user->directPermissions()->sync($request->permissions ?? []);
            }
        });

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = User::with('directPermissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        
        return view('users.edit', compact('user', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => "required|email|unique:users,email,{$user->id}",
            'password'    => 'nullable|string|min:6',
            'role'        => 'required|in:owner,kasir',
            'kasir_level' => 'required_if:role,kasir|nullable|in:full,custom',
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->name  = $request->name;
            $user->email = $request->email;
            $user->role  = $request->role;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->kasir_level = ($request->role === 'kasir') ? $request->kasir_level : null;
            $user->save();

            $role = Role::firstOrCreate(['name' => $request->role]);
            $user->roles()->sync([$role->id]);

            if ($request->role === 'owner' || $request->kasir_level === 'full') {
                $allPermissions = Permission::pluck('id')->toArray();
                $user->directPermissions()->sync($allPermissions);
            } else {
                $user->directPermissions()->sync($request->permissions ?? []);
            }
        });

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // 1. Cek Owner
        if ($user->role === 'owner') {
            return back()->with('error', 'Owner tidak bisa dihapus');
        }

        // 2. Cek Diri Sendiri (Menggunakan Auth Facade agar lebih aman)
        if (Auth::id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri yang sedang digunakan');
        }

        try {
            DB::transaction(function () use ($user) {
                // Lepas relasi pivot
                $user->roles()->detach();
                $user->directPermissions()->detach();

                // Hapus data sesi kasir
                CashierSession::where('user_id', $user->id)->delete();

                $user->delete();
            });

            return back()->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}
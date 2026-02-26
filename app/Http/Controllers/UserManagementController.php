<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

            // Sync Role
            $role = Role::firstOrCreate(['name' => $request->role]);
            $user->roles()->sync([$role->id]);

            // Sync Permissions
            if ($request->role === 'owner' || $request->kasir_level === 'full') {
                $allPermissions = Permission::pluck('id')->toArray();
                $user->directPermissions()->sync($allPermissions);
            } else {
                $user->directPermissions()->sync($request->permissions ?? []);
            }
        });

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => "required|email|unique:users,email,{$user->id}",
            'password'    => 'nullable|string|min:6',
            'role'        => 'required|in:owner,kasir',
            'kasir_level' => 'required_if:role,kasir|nullable|in:full,custom', // Fix typo kasir_levela
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

            // Sync Role
            $role = Role::firstOrCreate(['name' => $request->role]);
            $user->roles()->sync([$role->id]);

            // Sync Permissions
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

        // 1. Validasi agar Owner tidak bisa dihapus
        if ($user->role === 'owner') {
            return back()->with('error', 'Owner tidak bisa dihapus');
        }

        try {
            DB::transaction(function () use ($user) {
                // 2. Lepas relasi di tabel pivot (Roles & Permissions)
                $user->roles()->detach();
                $user->directPermissions()->detach();

                // 3. Hapus data di tabel yang menyebabkan error (Cashier Sessions)
                // Anda bisa menghapus datanya atau mengeset user_id menjadi null
                \App\Models\CashierSession::where('user_id', $user->id)->delete();

                // 4. Baru hapus user-nya
                $user->delete();
            });

            return back()->with('success', 'User dan data sesi berhasil dihapus permanen');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}

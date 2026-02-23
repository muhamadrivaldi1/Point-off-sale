<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    // ================= LIST USER =================
    public function index()
    {
        // Eager load roles dan directPermissions agar tidak N+1
        $users = User::with('roles', 'directPermissions')->get();

        return view('users.index', compact('users'));
    }

    // ================= FORM TAMBAH USER =================
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('users.create', compact('permissions'));
    }

    // ================= SIMPAN USER BARU =================
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6',
            'role'        => 'required|in:owner,kasir',
            'kasir_level' => 'required_if:role,kasir|nullable|in:full,custom',
        ]);

        // Buat user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ================= ATTACH ROLE =================
        // firstOrCreate: otomatis buat role jika belum ada di DB
        $role = Role::firstOrCreate(['name' => $request->role]);
        $user->roles()->attach($role->id);

        // ================= OWNER = FULL AUTO =================
        if ($request->role === 'owner') {
            $allPermissions = Permission::pluck('id')->toArray();
            $user->directPermissions()->sync($allPermissions);
            $user->kasir_level = null;
            $user->save();

            return redirect()->route('users.index')
                ->with('success', 'Owner berhasil ditambahkan');
        }

        // ================= KASIR =================
        if ($request->role === 'kasir') {
            $user->kasir_level = $request->kasir_level;

            if ($request->kasir_level === 'full') {
                $allPermissions = Permission::pluck('id')->toArray();
                $user->directPermissions()->sync($allPermissions);

            } elseif ($request->kasir_level === 'custom') {
                $user->directPermissions()->sync($request->permissions ?? []);
            }

            $user->save();
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    // ================= FORM EDIT USER =================
    public function edit($id)
    {
        $user        = User::with('roles', 'directPermissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();

        return view('users.edit', compact('user', 'permissions'));
    }

    // ================= UPDATE USER =================
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

        // Update nama & email
        $user->name  = $request->name;
        $user->email = $request->email;

        // Ganti password hanya jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // ================= UPDATE ROLE =================
        $role = Role::firstOrCreate(['name' => $request->role]);
        $user->roles()->sync([$role->id]);

        // ================= OWNER = AUTO FULL =================
        if ($request->role === 'owner') {
            $allPermissions = Permission::pluck('id')->toArray();
            $user->directPermissions()->sync($allPermissions);
            $user->kasir_level = null;
        }

        // ================= KASIR =================
        if ($request->role === 'kasir') {
            $user->kasir_level = $request->kasir_level;

            if ($request->kasir_level === 'full') {
                $allPermissions = Permission::pluck('id')->toArray();
                $user->directPermissions()->sync($allPermissions);

            } elseif ($request->kasir_level === 'custom') {
                $user->directPermissions()->sync($request->permissions ?? []);
            }
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate');
    }

    // ================= HAPUS USER =================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Jangan bisa hapus owner
        if ($user->roles->contains('name', 'owner')) {
            return back()->with('error', 'Owner tidak bisa dihapus');
        }

        $user->roles()->detach();
        $user->directPermissions()->detach();
        $user->delete();

        return back()->with('success', 'User berhasil dihapus');
    }
}
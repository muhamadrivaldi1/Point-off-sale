<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // ============================================================
    //  KONSTANTA TIPE AKUN — label & saldo normal
    // ============================================================
    const TYPES = [
        'asset'     => ['label' => 'Aset',          'normal' => 'debit',  'badge' => 'bg-info'],
        'liability' => ['label' => 'Kewajiban',      'normal' => 'kredit', 'badge' => 'bg-danger'],
        'equity'    => ['label' => 'Ekuitas/Modal',  'normal' => 'kredit', 'badge' => 'bg-purple'],
        'income'    => ['label' => 'Pendapatan',     'normal' => 'kredit', 'badge' => 'bg-success'],
        'expense'   => ['label' => 'Beban/Biaya',    'normal' => 'debit',  'badge' => 'bg-warning text-dark'],
        'cogs'      => ['label' => 'HPP',            'normal' => 'debit',  'badge' => 'bg-orange'],
    ];

    // ============================================================
    //  INDEX
    // ============================================================
    public function index()
    {
        $accounts = Account::orderBy('code')->get()
            ->groupBy('type');

        $types = self::TYPES;

        return view('akun.index', compact('accounts', 'types'));
    }

    // ============================================================
    //  STORE
    // ============================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'          => 'required|string|max:20|unique:accounts,code',
            'name'          => 'required|string|max:100',
            'type'          => 'required|in:asset,liability,equity,income,expense,cogs',
            'normal_balance'=> 'nullable|in:debit,kredit',
            'description'   => 'nullable|string|max:255',
            'is_active'     => 'nullable|boolean',
        ]);

        // Auto-set normal_balance jika tidak diisi
        if (empty($validated['normal_balance'])) {
            $validated['normal_balance'] = in_array($validated['type'], ['asset', 'expense', 'cogs'])
                ? 'debit'
                : 'kredit';
        }

        $validated['is_active'] = $validated['is_active'] ?? true;

        Account::create($validated);

        return back()->with('success', "Akun [{$validated['code']}] {$validated['name']} berhasil ditambahkan!");
    }

    // ============================================================
    //  SHOW (JSON — untuk edit modal)
    // ============================================================
    public function show(Account $account)
    {
        return response()->json($account);
    }

    // ============================================================
    //  UPDATE
    // ============================================================
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'code'          => 'required|string|max:20|unique:accounts,code,' . $account->id,
            'name'          => 'required|string|max:100',
            'type'          => 'required|in:asset,liability,equity,income,expense,cogs',
            'normal_balance'=> 'nullable|in:debit,kredit',
            'description'   => 'nullable|string|max:255',
            'is_active'     => 'nullable|boolean',
        ]);

        if (empty($validated['normal_balance'])) {
            $validated['normal_balance'] = in_array($validated['type'], ['asset', 'expense', 'cogs'])
                ? 'debit'
                : 'kredit';
        }

        $validated['is_active'] = $request->boolean('is_active');

        $account->update($validated);

        return back()->with('success', "Akun [{$account->code}] {$account->name} berhasil diperbarui!");
    }

    // ============================================================
    //  TOGGLE AKTIF / NONAKTIF
    // ============================================================
    public function toggle(Account $account)
    {
        $account->update(['is_active' => !$account->is_active]);
        $status = $account->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$account->name} berhasil {$status}.");
    }

    // ============================================================
    //  DESTROY
    // ============================================================
    public function destroy(Account $account)
    {
        // Cegah hapus jika akun dipakai di jurnal
        if ($account->journalLines()->exists()) {
            return back()->with('error', "Akun [{$account->code}] tidak dapat dihapus karena sudah digunakan di jurnal.");
        }

        $name = $account->name;
        $account->delete();

        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }

    // ============================================================
    //  API: daftar akun aktif (untuk select di form jurnal, dll.)
    // ============================================================
    public function apiList(Request $request)
    {
        $query = Account::where('is_active', true)->orderBy('code');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%");
            });
        }

        return response()->json(
            $query->get()->map(fn($a) => [
                'id'             => $a->id,
                'code'           => $a->code,
                'name'           => $a->name,
                'type'           => $a->type,
                'normal_balance' => $a->normal_balance,
                'label'          => "[{$a->code}] {$a->name}",
            ])
        );
    }
}
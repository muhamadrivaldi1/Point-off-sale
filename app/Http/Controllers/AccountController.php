<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('code', 'asc')->get();
        return view('akun.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:accounts,code',
            'name' => 'required',
            'type' => 'required|in:income,expense,asset,liability,equity'
        ]);

        Account::create($request->all());

        return back()->with('success', 'Akun berhasil ditambahkan!');
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return back()->with('success', 'Akun berhasil dihapus!');
    }
}
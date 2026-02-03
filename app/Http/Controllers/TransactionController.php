<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;


class TransactionController extends Controller
{
    public function index()
    {
        return view('transactions.index', [
            'data' => Transaction::latest()->get()
        ]);
    }

    public function edit($id)
    {
        abort_if(Auth::user()->role !== 'owner', 403);
        return view('transactions.edit', [
            'trx' => Transaction::with('items')->findOrFail($id)
        ]);
    }
}

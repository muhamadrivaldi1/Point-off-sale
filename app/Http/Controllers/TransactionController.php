<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionRequest;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{

    /**
     * LIST TRANSAKSI
     * Semua user login boleh lihat
     * HANYA transaksi PAID
     */
    public function index()
    {
        $data = Transaction::with('items')
            ->where('status', 'paid')
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('data'));
    }

    /**
     * FORM EDIT TRANSAKSI
     * HANYA OWNER
     * HANYA transaksi PAID
     */
    public function edit($id)
    {
        $this->onlyOwner();

        $trx = Transaction::with([
                'items.unit.product',
                'requests.user'
            ])
            ->where('status', 'paid')
            ->findOrFail($id);

        return view('transactions.edit', compact('trx'));
    }

    /**
     * UPDATE TRANSAKSI
     * HANYA OWNER
     */
    public function update(Request $request, $id)
    {
        $this->onlyOwner();

        $request->validate([
            'total' => 'required|numeric|min:0'
        ]);

        $trx = Transaction::where('status','paid')->findOrFail($id);

        $trx->update([
            'total' => $request->total
        ]);

        // Jika ada request edit → approve otomatis
        TransactionRequest::where('transaction_id', $id)
            ->where('status', 'pending')
            ->update(['status' => 'approved']);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    /**
     * KASIR AJUKAN REQUEST EDIT
     */
    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:10'
        ]);

        $trx = Transaction::where('status','paid')->findOrFail($id);

        TransactionRequest::create([
            'transaction_id' => $trx->id,
            'user_id'        => Auth::id(),
            'message'        => $request->message,
            'status'         => 'pending'
        ]);

        return back()->with('success','Permintaan edit dikirim ke owner');
    }

    /**
     * HELPER: BATASI ROLE OWNER
     */
    private function onlyOwner()
    {
        abort_if(Auth::user()->role !== 'owner', 403, 'Akses ditolak');
    }
}

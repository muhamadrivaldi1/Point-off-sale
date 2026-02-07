<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionRequest;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /* ===============================
       LIST TRANSAKSI (PAID)
    =============================== */
    public function index()
    {
        $data = Transaction::with([
            'requests.user' // load semua request untuk cek status pending/approved
        ])
            ->where('status', 'paid')
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('data'));
    }

    /* ===============================
       FORM EDIT (OWNER SAJA)
    =============================== */
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

    /* ===============================
       UPDATE TRANSAKSI (OWNER)
    =============================== */
    public function update(Request $request, $id)
    {
        $this->onlyOwner();

        $request->validate([
            'total' => 'required|numeric|min:0'
        ]);

        $trx = Transaction::where('status', 'paid')->findOrFail($id);

        $trx->update([
            'total' => $request->total
        ]);

        // Approve semua request pending
        TransactionRequest::where('transaction_id', $trx->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    /* ===============================
       KASIR REQUEST EDIT
    =============================== */
    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:10'
        ]);

        $trx = Transaction::where('status', 'paid')->findOrFail($id);

        // Cegah double request
        if ($trx->requests()->where('status', 'pending')->exists()) {
            return response()->json([
                'message' => 'Request sudah pernah dikirim'
            ], 422);
        }

        TransactionRequest::create([
            'transaction_id' => $trx->id,
            'user_id'        => Auth::id(),
            'message'        => $request->message,
            'status'         => 'pending'
        ]);

        return response()->json([
            'message' => 'Permintaan perbaikan berhasil dikirim'
        ]);
    }

    /* ===============================
       OWNER APPROVE EDIT
    =============================== */
    public function approveEdit($id)
    {
        $this->onlyOwner();

        $trx = Transaction::where('status', 'paid')->findOrFail($id);

        // Approve request pending
        TransactionRequest::where('transaction_id', $trx->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

        return back()->with('success', 'Permintaan edit disetujui');
    }

    /* ===============================
       STRUK TRANSAKSI
    =============================== */
    public function struk($id)
    {
        $trx = Transaction::with('items.unit.product')
            ->where('status', 'paid')
            ->findOrFail($id);

        return view('transactions.struk', compact('trx'));
    }

    /* ===============================
       HELPER ROLE
    =============================== */
    private function onlyOwner()
    {
        abort_if(Auth::user()->role !== 'owner', 403, 'Akses ditolak');
    }
}

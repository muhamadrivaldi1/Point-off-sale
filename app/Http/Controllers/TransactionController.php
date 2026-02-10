<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * LIST TRANSAKSI
     */
    public function index(Request $request)
    {
        $query = Transaction::with('requests.user')->latest();

        if ($request->filled('q')) {
            $query->where('trx_number', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $query->whereIn('status', ['paid', 'pending']);

        $data = $query->paginate(10)->withQueryString();

        return view('transactions.index', compact('data'));
    }

    /**
     * FORM EDIT (OWNER)
     */
    public function edit($id)
    {
        $this->onlyOwner();

        $trx = Transaction::with([
            'items.unit.product',
            'requests.user',
            'member'
        ])
            ->where('status', 'paid')
            ->findOrFail($id);

        return view('transactions.edit', compact('trx'));
    }

    /**
     * UPDATE TRANSAKSI (OWNER)
     */
    public function update(Request $request, $id)
    {
        $this->onlyOwner();

        $request->validate([
            'total' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () use ($request, $id) {

            $trx = Transaction::with('member')
                ->where('status', 'paid')
                ->lockForUpdate()
                ->findOrFail($id);

            $oldTotal = $trx->total;
            $newTotal = $request->total;

            // update transaksi
            $trx->update([
                'total' => $newTotal
            ]);

            /* ===============================
               UPDATE MEMBER (JIKA ADA)
            =============================== */
            if ($trx->member) {
                $member = $trx->member;

                // koreksi total_spent
                $member->total_spent = max(
                    0,
                    $member->total_spent - $oldTotal + $newTotal
                );

                // hitung ulang poin
                $member->points = floor($member->total_spent / 1000);

                // update level & diskon
                if ($member->total_spent >= 5000000) {
                    $member->level = 'Gold';
                    $member->discount = 5;
                } elseif ($member->total_spent >= 1000000) {
                    $member->level = 'Silver';
                    $member->discount = 2;
                } else {
                    $member->level = 'Basic';
                    $member->discount = 0;
                }

                $member->save();
            }

            // approve semua request edit
            TransactionRequest::where('transaction_id', $id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
        });

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui & member disesuaikan');
    }

    /**
     * KASIR REQUEST EDIT
     */
    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:10'
        ]);

        $trx = Transaction::where('status', 'paid')->findOrFail($id);

        if ($trx->requests()->where('status', 'pending')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Request edit sudah dikirim'
            ], 400);
        }

        TransactionRequest::create([
            'transaction_id' => $trx->id,
            'user_id'        => Auth::id(),
            'message'        => $request->message,
            'status'         => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan edit dikirim ke owner'
        ]);
    }

    /**
     * HAPUS TRANSAKSI PENDING
     */
    public function destroy($id)
    {
        $trx = Transaction::findOrFail($id);

        if ($trx->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi pending yang bisa dihapus'
            ], 403);
        }

        if (!in_array(Auth::user()->role, ['kasir', 'owner'])) {
            abort(403);
        }

        $trx->items()->delete();
        $trx->requests()->delete();
        $trx->delete();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * APPROVE REQUEST (OWNER)
     */
    public function approve($id)
    {
        $this->onlyOwner();

        $trx = Transaction::findOrFail($id);

        $req = $trx->requests()->where('status', 'pending')->first();
        if (!$req) {
            return back()->with('error', 'Tidak ada request pending');
        }

        $req->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return back()->with('success', 'Request berhasil diapprove');
    }

    /**
     * CETAK STRUK
     */
    public function struk($id)
    {
        $trx = Transaction::with([
                'items.unit.product',
                'user',
                'member'
            ])
            ->findOrFail($id);

        if (
            Auth::user()->role === 'kasir' &&
            $trx->user_id !== Auth::id()
        ) {
            abort(403);
        }

        foreach ($trx->items as $item) {
            $item->subtotal =
                ($item->price - ($item->discount ?? 0)) * $item->qty;
        }

        $total = $trx->items->sum('subtotal');

        return view('transactions.struk', compact('trx', 'total'));
    }

    /**
     * HELPER ROLE
     */
    private function onlyOwner()
    {
        abort_if(Auth::user()->role !== 'owner', 403, 'Akses ditolak');
    }
}

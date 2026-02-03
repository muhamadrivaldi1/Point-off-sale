<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /* ===============================
       LIST MEMBER
    =============================== */
    public function index()
    {
        return view('members.index', [
            'members' => Member::latest()->get()
        ]);
    }

    /* ===============================
       TAMBAH MEMBER
    =============================== */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:members'
        ]);

        Member::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'points' => 0
        ]);

        return back()->with('success', 'Member ditambahkan');
    }

    /* ===============================
       UPDATE MEMBER
    =============================== */
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $member->update($request->only('name','phone'));

        return back()->with('success', 'Member diupdate');
    }

    /* ===============================
       HAPUS MEMBER
    =============================== */
    public function destroy($id)
    {
        Member::findOrFail($id)->delete();
        return back()->with('success', 'Member dihapus');
    }

    /* ===============================
       TAMBAH POIN DARI TRANSAKSI
    =============================== */
    public function addPoint(Transaction $trx)
    {
        if (!$trx->member_id) return;

        $trx->member->increment('points', floor($trx->total / 1000));
    }

    /* ===============================
       REDEEM POIN
    =============================== */
    public function redeem(Request $request)
    {
        $member = Member::findOrFail($request->member_id);

        if ($member->points < $request->points) {
            return response()->json(['error' => 'Poin tidak cukup'], 422);
        }

        $member->decrement('points', $request->points);

        return response()->json(['success' => true]);
    }
}

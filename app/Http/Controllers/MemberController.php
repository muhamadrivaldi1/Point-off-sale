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
        $members = Member::latest()->get();
        return view('members.index', compact('members'));
    }

    /* ===============================
       FORM CREATE MEMBER
    =============================== */
    public function create()
    {
        return view('members.create');
    }

    /* ===============================
       TAMBAH MEMBER
    =============================== */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:members'
        ]);

        Member::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'points' => 0
        ]);

        return redirect()->route('members.index')
            ->with('success', 'Member berhasil ditambahkan');
    }

    /* ===============================
       FORM EDIT MEMBER
    =============================== */
    public function edit($id)
    {
        $member = Member::findOrFail($id);
        return view('members.edit', compact('member'));
    }

    /* ===============================
       UPDATE MEMBER
    =============================== */
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:members,phone,' . $member->id
        ]);

        $member->update($request->only('name','phone'));

        return redirect()->route('members.index')
            ->with('success', 'Member berhasil diperbarui');
    }

    /* ===============================
       HAPUS MEMBER
    =============================== */
    public function destroy($id)
    {
        Member::findOrFail($id)->delete();
        return redirect()->route('members.index')
            ->with('success', 'Member berhasil dihapus');
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

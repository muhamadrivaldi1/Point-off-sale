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
        $members = Member::orderBy('name')->paginate(10); 
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
            'phone' => 'required|string|max:20|unique:members',
            'address' => 'nullable|string|max:255'
        ]);

        Member::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'points' => 0,
            'total_spent' => 0,
            'level' => 'Basic',
            'discount' => 0,
            'status' => 'aktif'
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
            'phone' => 'required|string|max:20|unique:members,phone,' . $member->id,
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        $member->update($request->only('name','phone','address','status'));

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

        $member = $trx->member;

        // Tambah total_spent
        $member->total_spent += $trx->total;

        // Hitung poin (1 point per 1000 rupiah)
        $member->points += floor($trx->total / 1000);

        // Update level & diskon otomatis
        if ($member->total_spent > 5000000) {
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
    
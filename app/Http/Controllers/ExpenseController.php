<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /* ══════════════════════════════════════════════
       INDEX — daftar pengeluaran dengan filter
    ══════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to',   now()->toDateString());

        $query = Expense::dateRange($from, $to);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $data         = $query->orderByDesc('date')->orderByDesc('id')->paginate(20)->withQueryString();
        $totalPeriode = (clone $query)->sum('amount');

        return view('expenses.index', compact('data', 'from', 'to', 'totalPeriode'));
    }

    /* ══════════════════════════════════════════════
       STORE — simpan pengeluaran baru
    ══════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'date'   => 'required|date',
        ]);

        Expense::create([
            'name'   => trim($request->name),
            'amount' => $request->amount,
            'date'   => $request->date,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil disimpan.']);
        }

        return back()->with('success', 'Pengeluaran berhasil disimpan.');
    }

    /* ══════════════════════════════════════════════
       SHOW — detail (JSON untuk modal edit)
    ══════════════════════════════════════════════ */
    public function show(Expense $expense)
    {
        return response()->json($expense);
    }

    /* ══════════════════════════════════════════════
       UPDATE — ubah data pengeluaran
    ══════════════════════════════════════════════ */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'date'   => 'required|date',
        ]);

        $expense->update([
            'name'   => trim($request->name),
            'amount' => $request->amount,
            'date'   => $request->date,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil diperbarui.']);
        }

        return back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    /* ══════════════════════════════════════════════
       DESTROY — hapus pengeluaran
    ══════════════════════════════════════════════ */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dihapus.']);
        }

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }

    /* ══════════════════════════════════════════════
       API TODAY — untuk dashboard widget
    ══════════════════════════════════════════════ */
    public function today()
    {
        $list = Expense::today()->orderByDesc('id')->get();
        return response()->json([
            'total' => $list->sum('amount'),
            'count' => $list->count(),
            'items' => $list,
        ]);
    }
}
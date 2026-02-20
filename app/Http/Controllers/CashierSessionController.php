<?php

namespace App\Http\Controllers;

use App\Models\CashierSession;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionController extends Controller
{

    public function index()
    {
        // Jika OWNER → lihat semua sesi
        if (Auth::user()->role === 'owner') {
            $sessions = CashierSession::latest()->paginate(10);
        }
        // Jika KASIR → hanya miliknya
        else {
            $sessions = CashierSession::where('user_id', Auth::id())
                ->latest()
                ->paginate(10);
        }

        foreach ($sessions as $session) {

            $endTime = $session->closed_at ?? now();

            $cash = \App\Models\Transaction::where('user_id', $session->user_id)
                ->whereBetween('created_at', [$session->opened_at, $endTime])
                ->where('payment_method', 'cash')
                ->sum('total');

            $transfer = \App\Models\Transaction::where('user_id', $session->user_id)
                ->whereBetween('created_at', [$session->opened_at, $endTime])
                ->where('payment_method', 'transfer')
                ->sum('total');

            $session->cash_total = $cash;
            $session->transfer_total = $transfer;
            $session->grand_total = $cash + $transfer;
        }

        return view('cashier.sessions', compact('sessions'));
    }


    public function openForm()
    {
        $openSession = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($openSession) {
            return redirect()->route('dashboard')
                ->with('warning', 'Masih ada sesi kasir yang aktif');
        }

        return view('cashier.open');
    }


    public function open(Request $request)
    {
        $existing = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return redirect()->route('dashboard')
                ->with('warning', 'Sesi kasir sudah terbuka');
        }

        CashierSession::create([
            'user_id' => Auth::id(),
            'opening_balance' => 500000,
            'status' => 'open',
            'opened_at' => now()
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Sesi kasir berhasil dibuka');
    }


    public function close()
    {
        $session = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->firstOrFail();

        $session->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Sesi kasir berhasil ditutup');
    }
}
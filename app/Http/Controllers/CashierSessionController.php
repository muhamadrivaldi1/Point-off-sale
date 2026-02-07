<?php

namespace App\Http\Controllers;

use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionController extends Controller
{
    public function openForm()
    {
        // Cegah buka sesi kalau masih ada yang open
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
        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        // Pastikan tidak double session
        $existing = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return redirect()->route('dashboard')
                ->with('warning', 'Sesi kasir sudah terbuka');
        }

        CashierSession::create([
            'user_id' => Auth::id(),
            'opening_balance' => $request->opening_balance,
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

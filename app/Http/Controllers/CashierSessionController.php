<?php

namespace App\Http\Controllers;

use App\Models\CashierSession;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionController extends Controller
{
    // Menampilkan riwayat sesi kasir
    public function index()
    {
        // Ambil saldo awal terbaru dari setting, default 500000 jika belum ada
        $openingBalance = Setting::where('key', 'opening_balance')->value('value') ?? 500000;

        // Jika OWNER → lihat semua sesi
        if (Auth::user()->role === 'owner') {
            $sessions = CashierSession::latest()->paginate(10);
        } else {
            // Jika KASIR → hanya miliknya
            $sessions = CashierSession::where('user_id', Auth::id())
                ->latest()
                ->paginate(10);
        }

        // Hitung total cash, transfer, dan grand total
        foreach ($sessions as $session) {
            $endTime = $session->closed_at ?? now();

            $cash = Transaction::where('user_id', $session->user_id)
                ->whereBetween('created_at', [$session->opened_at, $endTime])
                ->where('payment_method', 'cash')
                ->sum('total');

            $transfer = Transaction::where('user_id', $session->user_id)
                ->whereBetween('created_at', [$session->opened_at, $endTime])
                ->where('payment_method', 'transfer')
                ->sum('total');

            $session->cash_total = $cash;
            $session->transfer_total = $transfer;
            $session->grand_total = $cash + $transfer;
        }

        return view('cashier.sessions', compact('sessions', 'openingBalance'));
    }

    // Form untuk membuka sesi kasir
    public function openForm()
    {
        $openSession = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($openSession) {
            return redirect()->route('dashboard')
                ->with('warning', 'Masih ada sesi kasir yang aktif');
        }

        // Ambil saldo awal terbaru dari setting
        $openingBalance = Setting::where('key', 'opening_balance')->value('value') ?? 500000;

        return view('cashier.open', compact('openingBalance'));
    }

    // Buka sesi kasir baru
    public function open(Request $request)
    {
        $existing = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return redirect()->route('dashboard')
                ->with('warning', 'Sesi kasir sudah terbuka');
        }

        // Ambil saldo awal dari setting
        $openingBalance = Setting::where('key', 'opening_balance')->value('value') ?? 500000;

        CashierSession::create([
            'user_id' => Auth::id(),
            'opening_balance' => $openingBalance,
            'status' => 'open',
            'opened_at' => now()
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Sesi kasir berhasil dibuka');
    }

    // Tutup sesi kasir aktif
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

    // Update saldo awal (hanya owner)
    public function updateOpeningBalance(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Hanya owner yang bisa mengubah saldo awal');
        }

        $request->validate([
            'opening_balance' => 'required|numeric|min:0|max:100000000',
        ]);

        Setting::updateOrCreate(
            ['key' => 'opening_balance'],
            ['value' => $request->opening_balance]
        );

        return redirect()->back()->with('success', 'Saldo awal berhasil diupdate');
    }
}
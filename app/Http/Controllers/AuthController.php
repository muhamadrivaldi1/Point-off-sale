<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashierSession; // Pastikan import model

class AuthController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function loginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        // Attempt login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect berdasarkan role
            if ($user->role === 'kasir') {
                // Cek apakah ada sesi kasir terbuka
                $openSession = CashierSession::where('user_id', $user->id)
                    ->where('status', 'open')
                    ->first();

                if ($openSession) {
                    return redirect()->route('pos'); // langsung ke POS
                } else {
                    return redirect()->route('cashier.open.form'); // harus buka sesi kasir
                }
            }

            // Jika owner/admin
            return redirect()->route('dashboard');
        }

        // Login gagal
        return back()->withErrors([
            'email' => 'Email atau password salah'
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

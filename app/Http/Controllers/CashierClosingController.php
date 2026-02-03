<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class CashierClosingController extends Controller
{
    public function close()
    {
        $total = Transaction::where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->sum('total');

        return view('closing.summary', compact('total'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function approveStock()
    {
        abort_if(!in_array(Auth::user()->role, ['owner']), 403);
        session(['stock_override' => true]);
        return back();
    }
}

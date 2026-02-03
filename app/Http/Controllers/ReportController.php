<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Stock;

class ReportController extends Controller
{
    public function sales(Request $r)
    {
        return view('reports.sales', [
            'data' => Transaction::whereBetween(
                'created_at', [$r->from, $r->to]
            )->get()
        ]);
    }

    public function stock()
    {
        return view('reports.stock', [
            'data' => Stock::with('unit.product')->get()
        ]);
    }
}

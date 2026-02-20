<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::latest()->paginate(10);
        return view('warehouses.index', compact('warehouses'));
    }

    public function store(Request $r)
    {
        Warehouse::create([
            'name' => $r->name,
            'code' => strtoupper($r->code)
        ]);

        return back()->with('success','Gudang berhasil ditambahkan');
    }

    public function setActive($id)
    {
        // Matikan semua dulu
        Warehouse::query()->update(['is_active' => false]);

        // Aktifkan yang dipilih
        Warehouse::where('id', $id)->update(['is_active' => true]);

        return back()->with('success','Gudang aktif berhasil diubah');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search jika ada input
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_supplier', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_supplier', 'like', '%' . $request->search . '%')
                    ->orWhere('npwp', 'like', '%' . $request->search . '%');
            });
        }

        $suppliers = $query
            ->orderBy('kode_supplier', 'asc')
            ->paginate(10)
            ->withQueryString(); // supaya search tetap saat pagination

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $last = Supplier::latest('id')->first();

        if (!$last) {
            $kode_supplier = 'SUP001';
        } else {
            $number = (int) substr($last->kode_supplier, 3);
            $kode_supplier = 'SUP' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
        }

        return view('suppliers.create', compact('kode_supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_supplier' => 'required|unique:suppliers',
            'nama_supplier' => 'required',
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'kode_supplier' => 'required|unique:suppliers,kode_supplier,' . $id,
            'nama_supplier' => 'required',
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diupdate');
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();

        return back()->with('success', 'Supplier berhasil dihapus');
    }
}

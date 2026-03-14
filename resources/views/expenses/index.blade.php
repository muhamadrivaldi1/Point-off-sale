@extends('layouts.app')
@section('title','Pengeluaran')

@section('content')
<style>
.exp-card { border:none; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.08); }
.field-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; color:#666; margin-bottom:3px; display:block; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 style="font-weight:800; margin:0;">💸 Pengeluaran</h5>
    <button class="btn btn-sm btn-primary" style="font-weight:700;"
            data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        + Tambah Pengeluaran
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success py-2" style="font-size:12px;">{{ session('success') }}</div>
@endif

{{-- Filter --}}
<div class="card exp-card mb-3">
    <div class="card-body py-2 px-3">
        <form method="GET" action="{{ route('expenses.index') }}"
              class="d-flex gap-2 flex-wrap align-items-end">
            <div>
                <label class="field-label">Dari</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="form-control form-control-sm" style="width:140px;">
            </div>
            <div>
                <label class="field-label">Sampai</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="form-control form-control-sm" style="width:140px;">
            </div>
            <div>
                <label class="field-label">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       class="form-control form-control-sm" placeholder="Nama pengeluaran..."
                       style="width:180px;">
            </div>
            <button class="btn btn-sm btn-primary" style="font-weight:700;">🔍 Filter</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </form>
    </div>
</div>

{{-- Ringkasan periode --}}
<div style="background:linear-gradient(135deg,#4a148c,#7b1fa2); color:#fff; border-radius:10px;
            padding:12px 18px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <div style="font-size:11px; font-weight:700; opacity:.85; text-transform:uppercase; letter-spacing:.4px;">
            Total Pengeluaran Periode
        </div>
        <div style="font-size:10px; opacity:.72; margin-top:1px;">
            {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }}
            s/d
            {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
        </div>
    </div>
    <div style="font-size:22px; font-weight:900;">
        Rp {{ number_format($totalPeriode) }}
    </div>
</div>

{{-- Tabel --}}
<div class="card exp-card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" style="font-size:13px;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th style="padding:10px 14px; font-size:11px; color:#666; text-transform:uppercase;">No</th>
                    <th style="padding:10px 14px; font-size:11px; color:#666; text-transform:uppercase;">Tanggal</th>
                    <th style="padding:10px 14px; font-size:11px; color:#666; text-transform:uppercase;">Nama Pengeluaran</th>
                    <th style="padding:10px 14px; font-size:11px; color:#666; text-transform:uppercase; text-align:right;">Jumlah</th>
                    <th style="padding:10px 14px; font-size:11px; color:#666; text-transform:uppercase; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $exp)
                <tr>
                    <td style="padding:9px 14px; color:#999;">{{ $data->firstItem() + $loop->index }}</td>
                    <td style="padding:9px 14px;">
                        {{ \Carbon\Carbon::parse($exp->date)->translatedFormat('d M Y') }}
                    </td>
                    <td style="padding:9px 14px; font-weight:600;">{{ $exp->name }}</td>
                    <td style="padding:9px 14px; text-align:right; font-weight:800; color:#4a148c;">
                        Rp {{ number_format($exp->amount) }}
                    </td>
                    <td style="padding:9px 14px; text-align:center;">
                        @if(auth()->user()->role === 'owner')
                        <form method="POST" action="{{ route('expenses.destroy', $exp->id) }}"
                              onsubmit="return confirm('Yakin hapus pengeluaran ini?')"
                              style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    style="font-size:11px; padding:2px 8px;">🗑 Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding:20px; font-size:12px;">
                        Belum ada pengeluaran di periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot style="background:#f8f9fa;">
                <tr>
                    <td colspan="3" style="padding:9px 14px; font-weight:700; font-size:12px; color:#555;">
                        Total (halaman ini: {{ $data->count() }} item)
                    </td>
                    <td style="padding:9px 14px; text-align:right; font-weight:900; color:#4a148c; font-size:14px;">
                        Rp {{ number_format($data->sum('amount')) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $data->links() }}
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4a148c,#7b1fa2);color:#fff;padding:12px 20px;">
                <h6 class="modal-title" style="font-weight:800;margin:0;">💸 Tambah Pengeluaran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" style="padding:20px 24px;">
                <form method="POST" action="{{ route('expenses.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#555;margin-bottom:4px;display:block;">
                            📝 Nama Pengeluaran
                        </label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="Contoh: Bayar Listrik, Bensin, dll."
                               value="{{ old('name') }}">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#555;margin-bottom:4px;display:block;">
                                💰 Jumlah (Rp)
                            </label>
                            <input type="number" name="amount" class="form-control" required min="1"
                                   placeholder="0" value="{{ old('amount') }}">
                        </div>
                        <div class="col-6">
                            <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#555;margin-bottom:4px;display:block;">
                                📅 Tanggal
                            </label>
                            <input type="date" name="date" class="form-control" required
                                   value="{{ old('date', today()->toDateString()) }}">
                        </div>
                    </div>
                    <button type="submit"
                            style="width:100%;padding:10px;font-size:13px;font-weight:800;color:#fff;
                                   background:linear-gradient(135deg,#4a148c,#7b1fa2);border:none;
                                   border-radius:9px;cursor:pointer;">
                        💾 Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
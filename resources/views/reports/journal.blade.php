@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Jurnal Umum Detail</h4>
                    <p class="text-muted small mb-0">Periode: {{ date('d M Y', strtotime($from)) }} - {{ date('d M Y', strtotime($to)) }}</p>
                </div>
                <div class="btn-group d-print-none gap-2">
                    {{-- TOMBOL TAMBAH TRANSAKSI MANUAL --}}
                    <button type="button" class="btn btn-primary btn-sm px-3 rounded-3" data-bs-toggle="modal" data-bs-target="#modalEntry">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Transaksi
                    </button>
                    
                    <a href="{{ route('reports.journal.export', request()->all()) }}" class="btn btn-light border btn-sm px-3 rounded-3">
                        <i class="bi bi-download me-2"></i>Export
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- FILTER BOX --}}
            <div class="bg-light p-3 rounded-3 mb-4 d-print-none">
                <form method="GET" action="{{ route('reports.journal') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Rentang Tanggal</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="from" class="form-control border-0 shadow-sm" value="{{ $from }}">
                            <span class="input-group-text bg-white border-0">s/d</span>
                            <input type="date" name="to" class="form-control border-0 shadow-sm" value="{{ $to }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Cari Transaksi</label>
                        <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" placeholder="No. TRX / Keterangan..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-dark btn-sm px-4 shadow-sm w-100">Filter Data</button>
                        <a href="{{ route('reports.journal') }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase" style="border-bottom: 2px solid #f8f9fa;">
                            <th class="py-3 px-4">Tanggal / Ref</th>
                            <th class="py-3">Akun & Keterangan</th>
                            <th class="py-3 text-center">Ref</th>
                            <th class="py-3 text-end">Debit</th>
                            <th class="py-3 text-end">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $entry)
                            @php
                                // Logika: Jika type 'income', Kas di Debit. Jika 'expense', Kas di Kredit.
                                $isIncome = $entry->type == 'income' || $entry->type == null; // Default penjualan masuk income
                            @endphp

                            {{-- BARIS 1: AKUN LAWAN (Pilihan User) --}}
                            <tr style="border-left: 4px solid {{ $isIncome ? '#0d6efd' : '#dc3545' }}; background-color: #fcfcfc;">
                                <td class="px-4">
                                    <span class="d-block fw-bold text-dark">{{ $entry->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted" style="font-size: 11px;">{{ $entry->trx_number }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">
                                        {{ $entry->account->code ?? ($isIncome ? '4-1100' : '5-1100') }} · 
                                        {{ $entry->account->name ?? ($isIncome ? 'Pendapatan Penjualan' : 'Beban Transaksi') }}
                                    </span>
                                    <small class="d-block text-muted">
                                        {{ $entry->description ?? 'Penjualan ke ' . ($entry->member->name ?? 'Pelanggan Umum') }}
                                    </small>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark border">{{ $entry->account->code ?? '4100' }}</span></td>
                                {{-- Jika income, akun lawan di Kredit. Jika expense, akun lawan di Debit --}}
                                <td class="text-end fw-bold">{{ !$isIncome ? 'Rp '.number_format($entry->total, 0, ',', '.') : '-' }}</td>
                                <td class="text-end fw-bold">{{ $isIncome ? 'Rp '.number_format($entry->total, 0, ',', '.') : '-' }}</td>
                            </tr>
                            
                            {{-- BARIS 2: SELALU KAS --}}
                            <tr style="border-left: 4px solid #dee2e6;">
                                <td class="px-4"></td>
                                <td class="ps-5">
                                    <span class="text-secondary">1-1100 · Kas dan Bank</span>
                                    <small class="d-block text-muted opacity-75">Otomatis (Lawan Transaksi)</small>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-muted border">1100</span></td>
                                {{-- Kas Debit jika uang masuk, Kredit jika uang keluar --}}
                                <td class="text-end fw-bold text-primary">{{ $isIncome ? 'Rp '.number_format($entry->total, 0, ',', '.') : '-' }}</td>
                                <td class="text-end fw-bold text-muted">{{ !$isIncome ? 'Rp '.number_format($entry->total, 0, ',', '.') : '-' }}</td>
                            </tr>
                            <tr style="height: 15px;"></tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <p class="text-muted">Tidak ada data ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-dark text-white shadow-sm">
                            <td colspan="3" class="py-3 px-4 fw-bold text-end text-uppercase">Balance Total</td>
                            <td class="py-3 text-end fw-bold text-warning">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                            <td class="py-3 text-end fw-bold text-warning">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center d-print-none">
                <div class="small text-muted">
                    Menampilkan {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }} dari {{ $data->total() }} data
                </div>
                <div>
                    {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INPUT TRANSAKSI BARU --}}
<div class="modal fade" id="modalEntry" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Transaksi Kas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('reports.journal.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jenis Transaksi</label>
                        <select name="type" class="form-select border-0 bg-light shadow-sm" required>
                            <option value="income">Kas Masuk (Pendapatan/Piutang/Lainnya)</option>
                            <option value="expense">Kas Keluar (Beban/Hutang/Pembelian)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Akun Lawan</label>
                        <select name="account_id" class="form-select border-0 bg-light shadow-sm" required>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tanggal</label>
                            <input type="date" name="date" class="form-control border-0 bg-light shadow-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Jumlah (Rp)</label>
                            <input type="number" name="total" class="form-control border-0 bg-light shadow-sm" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Keterangan</label>
                        <textarea name="description" class="form-control border-0 bg-light shadow-sm" rows="2" placeholder="Contoh: Pembayaran Listrik Bulanan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media print {
        .d-print-none { display: none !important; }
        .card { box-shadow: none !important; border: none !important; }
        .bg-dark { background-color: #212529 !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
    .ps-5 { padding-left: 3.5rem !important; }
    .table tbody tr:hover { background-color: #f8f9fa !important; }
</style>
@endsection
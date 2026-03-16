{{-- resources/views/po/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Purchase Order #' . $po->po_number)

@section('content')
<div class="container-fluid">

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">

        {{-- Header --}}
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-cart-check me-2"></i>Edit PO #{{ $po->po_number }}
                </h5>
                <small class="text-muted">
                    Dibuat: {{ \Carbon\Carbon::parse($po->created_at)->format('d/m/Y H:i') }}
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                @php
                    $badgeColor = match($po->status) {
                        'approved' => 'bg-primary',
                        'received' => 'bg-success',
                        'canceled' => 'bg-danger',
                        default    => 'bg-secondary',
                    };
                @endphp
                <span class="badge {{ $badgeColor }} fs-6">{{ ucfirst($po->status) }}</span>
                <a href="{{ route('po.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">

            {{-- Info PO (read-only) --}}
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Supplier</label>
                    <p class="mb-0 fw-semibold">{{ $po->supplier->nama_supplier ?? '-' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Jenis Pembayaran</label>
                    <p class="mb-0">
                        <span class="badge bg-light text-dark border">{{ $po->jenis_pembayaran }}</span>
                    </p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Jatuh Tempo</label>
                    <p class="mb-0">
                        {{ $po->tanggal_jatuh_tempo
                            ? \Carbon\Carbon::parse($po->tanggal_jatuh_tempo)->format('d/m/Y')
                            : '-' }}
                    </p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Total Sekarang</label>
                    <p class="mb-0 fw-bold text-primary fs-5">
                        Rp {{ number_format($po->total, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            @if($po->status === 'canceled')

                {{-- PO Canceled: hanya tampilkan info, tidak bisa edit --}}
                <div class="alert alert-secondary">
                    <i class="bi bi-ban me-1"></i>
                    PO ini sudah dibatalkan dan tidak dapat diedit.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th width="40">#</th>
                                <th>Produk</th>
                                <th class="text-center" width="120">Qty</th>
                                <th class="text-end" width="160">Harga Satuan</th>
                                <th class="text-end" width="160">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->product->nama_produk ?? '-' }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else

                {{-- ======================================================= --}}
                {{-- FORM EDIT (draft / approved / received)                  --}}
                {{-- ======================================================= --}}
                <form action="{{ route('po.update', $po->id) }}" method="POST" id="formEditPO">
                    @csrf
                    @method('PUT')

                    @if($po->status === 'received')
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>
                            <strong>Perhatian!</strong>
                            PO ini sudah berstatus <strong>Received</strong>.
                            Perubahan qty/item akan <strong>mengupdate stok produk secara otomatis</strong>
                            (stok lama akan di-reverse, lalu stok baru diterapkan).
                        </div>
                    </div>
                    @endif

                    {{-- Tabel item --}}
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="tabelItem">
                            <thead class="table-dark">
                                <tr>
                                    <th width="40">#</th>
                                    <th>Produk</th>
                                    <th class="text-center" width="120">Qty</th>
                                    <th class="text-end" width="180">Harga Satuan (Rp)</th>
                                    <th class="text-end" width="160">Subtotal</th>
                                    <th class="text-center" width="60">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="bodyItem">

                                @foreach($po->items as $i => $item)
                                <tr class="baris-item">
                                    <td class="nomor-baris">{{ $i + 1 }}</td>

                                    <td>
                                        <select name="items[{{ $i }}][product_id]"
                                            class="form-select form-select-sm select-produk"
                                            required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->nama_produk }}
                                                (Stok: {{ $product->stok }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number"
                                            name="items[{{ $i }}][qty]"
                                            class="form-control form-control-sm text-center input-qty"
                                            value="{{ $item->qty }}"
                                            min="1" required>
                                    </td>

                                    <td>
                                        <input type="number"
                                            name="items[{{ $i }}][harga_satuan]"
                                            class="form-control form-control-sm text-end input-harga"
                                            value="{{ $item->harga_satuan }}"
                                            min="0" step="1" required>
                                    </td>

                                    <td class="text-end fw-semibold kolom-subtotal">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>

                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-hapus-baris">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Baru:</td>
                                    <td class="text-end fw-bold text-primary fs-5" id="totalKeseluruhan">
                                        Rp 0
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>

                    {{-- Tombol tambah baris --}}
                    <div class="mb-4">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahBaris">
                            <i class="bi bi-plus-lg"></i> Tambah Produk
                        </button>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('po.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary" id="btnSimpan">
                            <i class="bi bi-floppy"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>

            @endif

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</div>{{-- /container --}}
@endsection


@push('scripts')
<script>
(function () {
    'use strict';

    // ── Data produk dari server ──────────────────────────────────────
    const daftarProduk = @json($products->map(fn($p) => [
        'id'         => $p->id,
        'nama'       => $p->nama_produk,
        'stok'       => $p->stok,
    ]));

    // ── Format Rupiah ────────────────────────────────────────────────
    function rupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    // ── Hitung ulang total keseluruhan ───────────────────────────────
    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.baris-item').forEach(row => {
            const qty   = parseFloat(row.querySelector('.input-qty').value)   || 0;
            const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
            const sub   = qty * harga;
            row.querySelector('.kolom-subtotal').textContent = rupiah(sub);
            total += sub;
        });
        document.getElementById('totalKeseluruhan').textContent = rupiah(total);
    }

    // ── Nomori ulang baris ───────────────────────────────────────────
    function nomoriBaris() {
        document.querySelectorAll('.baris-item').forEach((row, i) => {
            row.querySelector('.nomor-baris').textContent = i + 1;

            // Update name index supaya array PHP benar
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/items\[\d+\]/, `items[${i}]`);
            });
        });
    }

    // ── Buat option produk ───────────────────────────────────────────
    function buatOptions(selectedId = '') {
        return daftarProduk.map(p =>
            `<option value="${p.id}" ${p.id == selectedId ? 'selected' : ''}>
                ${p.nama} (Stok: ${p.stok})
            </option>`
        ).join('');
    }

    // ── Template baris baru ──────────────────────────────────────────
    function templateBaris(idx) {
        return `
        <tr class="baris-item">
            <td class="nomor-baris">${idx + 1}</td>
            <td>
                <select name="items[${idx}][product_id]"
                    class="form-select form-select-sm select-produk" required>
                    <option value="">-- Pilih Produk --</option>
                    ${buatOptions()}
                </select>
            </td>
            <td>
                <input type="number" name="items[${idx}][qty]"
                    class="form-control form-control-sm text-center input-qty"
                    value="1" min="1" required>
            </td>
            <td>
                <input type="number" name="items[${idx}][harga_satuan]"
                    class="form-control form-control-sm text-end input-harga"
                    value="0" min="0" step="1" required>
            </td>
            <td class="text-end fw-semibold kolom-subtotal">Rp 0</td>
            <td class="text-center">
                <button type="button"
                    class="btn btn-sm btn-outline-danger btn-hapus-baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
    }

    // ── Event: Tambah baris ──────────────────────────────────────────
    document.getElementById('btnTambahBaris')?.addEventListener('click', () => {
        const tbody = document.getElementById('bodyItem');
        const idx   = tbody.querySelectorAll('.baris-item').length;
        tbody.insertAdjacentHTML('beforeend', templateBaris(idx));
        hitungTotal();
    });

    // ── Event: Hapus baris & input berubah (delegasi) ────────────────
    document.getElementById('bodyItem')?.addEventListener('click', e => {
        if (e.target.closest('.btn-hapus-baris')) {
            const rows = document.querySelectorAll('.baris-item');
            if (rows.length <= 1) {
                alert('Minimal harus ada 1 item di PO.');
                return;
            }
            e.target.closest('.baris-item').remove();
            nomoriBaris();
            hitungTotal();
        }
    });

    document.getElementById('bodyItem')?.addEventListener('input', e => {
        if (e.target.matches('.input-qty, .input-harga')) {
            hitungTotal();
        }
    });

    // ── Konfirmasi sebelum simpan (khusus received) ──────────────────
    document.getElementById('formEditPO')?.addEventListener('submit', function (e) {
        const isReceived = {{ $po->status === 'received' ? 'true' : 'false' }};
        if (isReceived) {
            if (!confirm('⚠️ PO ini sudah Received.\n\nMenyimpan perubahan akan mengupdate stok produk secara otomatis.\n\nLanjutkan?')) {
                e.preventDefault();
            }
        }
    });

    // ── Inisialisasi total saat halaman load ─────────────────────────
    hitungTotal();

})();
</script>
@endpush
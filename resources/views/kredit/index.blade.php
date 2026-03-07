<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Kredit #{{ $trx->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.07); }
        .payment-item { border-left: 4px solid #0d6efd; background: #f8f9ff; border-radius: 0 8px 8px 0; }
        .sisa-amount  { font-size: 2rem; font-weight: 700; color: #dc3545; }
    </style>
</head>
<body>
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <div>
            <h5 class="mb-0 fw-bold">Detail Kredit</h5>
            <small class="text-muted">Transaksi #{{ $trx->id }}</small>
        </div>
        <span class="ms-auto badge fs-6 px-3 py-2 {{ $trx->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
            {{ $trx->status === 'paid' ? '✅ Lunas' : '⏳ Belum Lunas' }}
        </span>
    </div>

    <div class="row g-4">

        {{-- Kolom Kiri --}}
        <div class="col-lg-7">

            {{-- Info Member --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-person-circle text-primary me-2"></i>Info Member</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" width="150">Nama</td><td class="fw-semibold">{{ $trx->member->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Telepon</td><td>{{ $trx->member->phone ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Tgl Transaksi</td><td>{{ $trx->created_at->format('d M Y, H:i') }}</td></tr>
                        @if($trx->paid_at)
                        <tr><td class="text-muted">Tgl Lunas</td><td>{{ \Carbon\Carbon::parse($trx->paid_at)->format('d M Y, H:i') }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Item Belanja --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-cart3 text-primary me-2"></i>Item Belanja</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trx->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->unit->product->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $item->unit->name ?? '' }}</small>
                                    </td>
                                    <td class="text-center">{{ $item->qty }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Total</td>
                                    <td class="text-end">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-sticky text-primary me-2"></i>Catatan</h6>
                    <textarea id="notesInput" class="form-control" rows="3"
                        placeholder="Tambahkan catatan..."
                        {{ $trx->status === 'paid' ? 'disabled' : '' }}>{{ $trx->notes }}</textarea>
                    @if($trx->status !== 'paid')
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="saveNotes()">
                        <i class="bi bi-save"></i> Simpan Catatan
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan --}}
        <div class="col-lg-5">

            {{-- Ringkasan --}}
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    <div class="text-muted">Total Tagihan</div>
                    <div class="fs-5 fw-bold">Rp {{ number_format($trx->total, 0, ',', '.') }}</div>
                    <hr>
                    <div class="text-muted">Sudah Dibayar</div>
                    <div class="fs-5 fw-bold text-success">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
                    <hr>
                    <div class="text-muted mb-1">Sisa Tagihan</div>
                    <div class="sisa-amount" id="sisaDisplay">Rp {{ number_format($sisa, 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            @if($trx->status === 'kredit')
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-cash-coin text-primary me-2"></i>Aksi Pembayaran</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalLunasi">
                            <i class="bi bi-check-circle me-1"></i> Lunasi Sekarang
                        </button>
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPartial">
                            <i class="bi bi-cash me-1"></i> Bayar Sebagian
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Riwayat Pembayaran --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pembayaran</h6>
                    <div id="paymentHistory">
                        @forelse($trx->payments as $pay)
                        <div class="payment-item p-3 mb-2">
                            <div class="fw-bold text-success">Rp {{ number_format($pay->amount, 0, ',', '.') }}</div>
                            <small class="text-muted">
                                {{ ucfirst($pay->method) }} &bull; {{ \Carbon\Carbon::parse($pay->paid_at)->format('d M Y H:i') }}
                            </small>
                            @if($pay->note)
                            <div><small class="text-secondary"><i class="bi bi-chat-left-text"></i> {{ $pay->note }}</small></div>
                            @endif
                        </div>
                        @empty
                        <p class="text-muted text-center py-2" id="emptyPayment">Belum ada pembayaran.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal Lunasi --}}
<div class="modal fade" id="modalLunasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill text-success me-2"></i>Lunasi Kredit</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">Melunasi sisa <strong>Rp {{ number_format($sisa, 0, ',', '.') }}</strong></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Pembayaran</label>
                    <select class="form-select" id="lunasiMethod">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan</label>
                    <input type="text" class="form-control" id="lunasiNote" placeholder="Opsional">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Owner <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="lunasiPassword">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success" onclick="submitLunasi()">Konfirmasi Lunasi</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Bayar Sebagian --}}
<div class="modal fade" id="modalPartial" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash text-primary me-2"></i>Bayar Sebagian</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Bayar <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="partialAmount" min="1">
                    </div>
                    <small class="text-muted">Maks: Rp {{ number_format($sisa, 0, ',', '.') }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Pembayaran</label>
                    <select class="form-select" id="partialMethod">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan</label>
                    <input type="text" class="form-control" id="partialNote" placeholder="Opsional">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Owner <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="partialPassword">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary" onclick="submitPartial()">Konfirmasi Bayar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const TRX_ID  = {{ $trx->id }};
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF };
const rupiah  = v => 'Rp ' + parseInt(v).toLocaleString('id-ID');

function saveNotes() {
    fetch('{{ route("pos.kredit.notes") }}', {
        method: 'POST', headers,
        body: JSON.stringify({ trx_id: TRX_ID, notes: document.getElementById('notesInput').value })
    }).then(r => r.json()).then(d => {
        if (d.success) showToast('Catatan berhasil disimpan!', 'success');
    });
}

function submitLunasi() {
    fetch('{{ route("pos.kredit.lunasi") }}', {
        method: 'POST', headers,
        body: JSON.stringify({
            trx_id: TRX_ID,
            password: document.getElementById('lunasiPassword').value,
            method: document.getElementById('lunasiMethod').value,
            note: document.getElementById('lunasiNote').value,
        })
    }).then(r => r.json()).then(d => {
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalLunasi')).hide();
            addPayment(d.payment);
            updateSisa(0);
            showToast('Kredit berhasil dilunasi!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message, 'danger');
        }
    });
}

function submitPartial() {
    fetch('{{ route("pos.kredit.partial") }}', {
        method: 'POST', headers,
        body: JSON.stringify({
            trx_id: TRX_ID,
            password: document.getElementById('partialPassword').value,
            amount: document.getElementById('partialAmount').value,
            method: document.getElementById('partialMethod').value,
            note: document.getElementById('partialNote').value,
        })
    }).then(r => r.json()).then(d => {
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalPartial')).hide();
            addPayment(d.payment);
            updateSisa(d.sisa);
            showToast(d.is_lunas ? 'Kredit lunas!' : 'Pembayaran berhasil!', 'success');
            if (d.is_lunas) setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message, 'danger');
        }
    });
}

function addPayment(pay) {
    const empty = document.getElementById('emptyPayment');
    if (empty) empty.remove();
    const date = new Date(pay.paid_at).toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
    document.getElementById('paymentHistory').insertAdjacentHTML('afterbegin', `
        <div class="payment-item p-3 mb-2">
            <div class="fw-bold text-success">${rupiah(pay.amount)}</div>
            <small class="text-muted">${pay.method} &bull; ${date}</small>
            ${pay.note ? `<div><small class="text-secondary">${pay.note}</small></div>` : ''}
        </div>`);
}

function updateSisa(sisa) {
    document.getElementById('sisaDisplay').textContent = rupiah(sisa);
}

function showToast(msg, type) {
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${type} border-0 show position-fixed bottom-0 end-0 m-3`;
    el.style.zIndex = 9999;
    el.innerHTML = `<div class="d-flex"><div class="toast-body fw-semibold">${msg}</div>
        <button class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button></div>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}
</script>
</body>
</html>
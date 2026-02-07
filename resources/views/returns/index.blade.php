@extends('layouts.app')

@section('title','Retur Barang')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Retur Barang</h4>

    <table class="table table-sm table-bordered" id="returnsTable">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Invoice</th>
                <th>Produk</th>
                <th>Qty Beli</th>
                <th>Qty Retur</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($transactions as $trx)
            @foreach($trx->items as $item)
            @php
                // Cek semua status retur untuk item ini
                $pendingRetur = $item->returns->where('status','pending')->first();
                $approvedRetur = $item->returns->where('status','approved')->first();
                $rejectedRetur = $item->returns->where('status','rejected')->first();
                
                // Hitung total yang sudah diretur (approved)
                $totalReturned = $item->returns->where('status','approved')->sum('qty');
                $availableQty = $item->qty - $totalReturned;
            @endphp
            <tr data-item-id="{{ $item->id }}">
                <td>{{ $loop->parent->iteration }}</td>
                <td>{{ $trx->trx_number }}</td>
                <td>{{ $item->unit->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>
                    @if($pendingRetur)
                        {{ $pendingRetur->qty }} (pending)
                    @elseif($approvedRetur)
                        {{ $item->returns->where('status','approved')->sum('qty') }}
                    @else
                        -
                    @endif
                </td>
                <td class="status-cell">
                    @if($pendingRetur)
                        <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                    @elseif($approvedRetur && $rejectedRetur)
                        <span class="badge bg-success">Disetujui</span>
                        <span class="badge bg-danger">Ada yang Ditolak</span>
                    @elseif($approvedRetur)
                        <span class="badge bg-success">Retur Disetujui</span>
                    @elseif($rejectedRetur)
                        <span class="badge bg-danger">Retur Ditolak</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="action-cell">
                    {{-- Tombol Kasir --}}
                    @if(auth()->user()->role === 'kasir')
                        @if(!$pendingRetur && $availableQty > 0)
                            <button class="btn btn-danger btn-sm btn-retur"
                                    data-item-id="{{ $item->id }}"
                                    data-max-qty="{{ $availableQty }}"
                                    data-product="{{ $item->unit->product->name }}">
                                Ajukan Retur
                            </button>
                        @elseif($pendingRetur)
                            <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                        @elseif($availableQty <= 0)
                            <span class="text-muted">Sudah diretur semua</span>
                        @endif
                    @endif

                    {{-- Tombol Owner --}}
                    @if(auth()->user()->role === 'owner')
                        @if($pendingRetur)
                            <button class="btn btn-success btn-sm btn-approve"
                                data-return-id="{{ $pendingRetur->id }}"
                                data-qty="{{ $pendingRetur->qty }}"
                                data-product="{{ $item->unit->product->name }}">
                                <i class="bi bi-check-circle"></i> Setujui
                            </button>
                            <button class="btn btn-danger btn-sm btn-reject"
                                data-return-id="{{ $pendingRetur->id }}"
                                data-qty="{{ $pendingRetur->qty }}"
                                data-product="{{ $item->unit->product->name }}">
                                <i class="bi bi-x-circle"></i> Tolak
                            </button>
                        @elseif($approvedRetur)
                            <span class="badge bg-success">Sudah Disetujui</span>
                        @elseif($rejectedRetur)
                            <span class="badge bg-danger">Sudah Ditolak</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted">Tidak ada transaksi</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-3 d-flex justify-content-center">
        {{ $transactions->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- MODAL RETUR --}}
<div class="modal fade" id="returModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajukan Retur Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="transaction_item_id">
        
        <div class="mb-3">
            <label class="form-label fw-bold">Produk</label>
            <p id="product_name" class="form-control-plaintext"></p>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Jumlah Retur <span class="text-danger">*</span></label>
            <input type="number" min="1" class="form-control" id="retur_qty" required>
            <small class="text-muted" id="maxQtyText"></small>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Alasan Retur</label>
            <textarea class="form-control" id="retur_reason" rows="3" placeholder="Contoh: Barang rusak, salah pesan, dll"></textarea>
            <small class="text-muted">Opsional, tapi disarankan untuk dicantumkan</small>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Pengajuan retur akan dikirim ke owner untuk disetujui.
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-danger" id="btnSubmitRetur">
            <i class="bi bi-send"></i> Kirim Pengajuan
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    const returModalEl = document.getElementById('returModal');
    const returModal = new bootstrap.Modal(returModalEl);

    const itemIdInput = document.getElementById('transaction_item_id');
    const productNameEl = document.getElementById('product_name');
    const qtyInput = document.getElementById('retur_qty');
    const maxQtyText = document.getElementById('maxQtyText');
    const reasonInput = document.getElementById('retur_reason');
    const btnSubmit = document.getElementById('btnSubmitRetur');

    // Kasir: buka modal Retur
    document.querySelectorAll('.btn-retur').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const maxQty = this.dataset.maxQty;
            const productName = this.dataset.product;

            itemIdInput.value = itemId;
            productNameEl.textContent = productName;
            qtyInput.value = '';
            qtyInput.max = maxQty;
            maxQtyText.innerText = `Maksimal yang bisa diretur: ${maxQty}`;
            reasonInput.value = '';

            returModal.show();
        });
    });

    // Kasir: submit retur via AJAX
    btnSubmit.addEventListener('click', function() {
        const itemId = itemIdInput.value;
        const qty = parseInt(qtyInput.value);
        const reason = reasonInput.value;

        if (!qty || qty <= 0) {
            alert('Jumlah retur tidak valid');
            return;
        }

        if (qty > parseInt(qtyInput.max)) {
            alert(`Jumlah retur maksimal ${qtyInput.max}`);
            return;
        }

        // Disable button
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';

        fetch('{{ route('returns.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                transaction_item_id: itemId,
                qty: qty,
                reason: reason
            })
        })
        .then(res => res.json())
        .then(res => {
            if(res.success){
                alert(res.message);
                returModal.hide();
                location.reload();
            } else {
                alert(res.message || 'Gagal mengirim pengajuan retur');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat mengirim pengajuan retur');
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-send"></i> Kirim Pengajuan';
        });
    });

    // Owner: approve via AJAX
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            const returnId = this.dataset.returnId;
            const qty = this.dataset.qty;
            const product = this.dataset.product;
            
            if(!confirm(`Setujui retur ${qty} unit ${product}?\n\nStok akan dikembalikan dan transaksi akan diupdate.`)) {
                return;
            }

            // Disable button
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`/returns/${returnId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(res => {
                if(res.success){
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message || 'Gagal menyetujui retur');
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat menyetujui retur');
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
    });

    // Owner: reject via AJAX
    document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            const returnId = this.dataset.returnId;
            const qty = this.dataset.qty;
            const product = this.dataset.product;
            
            if(!confirm(`Tolak retur ${qty} unit ${product}?`)) {
                return;
            }

            // Disable button
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`/returns/${returnId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(res => {
                if(res.success){
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message || 'Gagal menolak retur');
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat menolak retur');
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
    });

});
</script>
@endpush
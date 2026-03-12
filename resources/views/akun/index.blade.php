@extends('layouts.app')

@section('title', 'Master Akun (COA)')

@section('content')

<style>
/* ── Warna khusus yang belum ada di Bootstrap ── */
.bg-purple        { background-color: #6d28d9 !important; }
.bg-orange        { background-color: #ea580c !important; }
.text-purple      { color: #6d28d9 !important; }

/* ── Badge tipe ── */
.badge-type {
    font-size: 10px; font-weight: 700; padding: 3px 9px;
    border-radius: 20px; letter-spacing: .3px; white-space: nowrap;
}

/* ── Normal balance chip ── */
.nb-chip {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 20px; white-space: nowrap;
}
.nb-debit  { background: #dbeafe; color: #1e40af; }
.nb-kredit { background: #dcfce7; color: #15803d; }

/* ── Row animasi ── */
@keyframes rowIn {
    from { opacity: 0; transform: translateX(-8px); }
    to   { opacity: 1; transform: translateX(0); }
}
.account-row { animation: rowIn .2s ease; }

/* ── Group header ── */
.group-header-row th {
    background: #f1f5f9; font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .5px; color: #475569;
    padding: 6px 14px; border-top: 2px solid #e2e8f0;
}

/* ── Inactive row ── */
.row-inactive td { opacity: .45; }

/* ── Status pill ── */
.status-pill {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px;
}
.status-pill.active   { background: #d1fae5; color: #065f46; }
.status-pill.inactive { background: #fee2e2; color: #991b1b; }

/* ── Summary card ── */
.summary-card {
    border: 1.5px solid #e2e8f0; border-radius: 10px;
    padding: 12px 16px; background: #fff;
    transition: box-shadow .15s;
}
.summary-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.08); }
.summary-card .sc-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
.summary-card .sc-count { font-size: 22px; font-weight: 900; color: #1e293b; line-height: 1; margin-top: 2px; }
</style>

<div class="container-fluid py-4" style="max-width: 1200px;">

    {{-- ── HEADER ── --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">📒 Master Akun (COA)</h4>
            <p class="text-muted small mb-0">Kelola Chart of Accounts untuk jurnal otomatis POS</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()">
                🔄 Refresh
            </button>
            <button class="btn btn-primary btn-sm px-4"
                    data-bs-toggle="modal" data-bs-target="#addAccountModal">
                ＋ Tambah Akun
            </button>
        </div>
    </div>

    {{-- ── ALERT ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 small mb-3" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 small mb-3" role="alert">
            ❌ {{ session('error') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── SUMMARY CARDS ── --}}
    @php
        $all    = $accounts->flatten();
        $active = $all->where('is_active', true)->count();
        $total  = $all->count();
    @endphp
    <div class="row g-3 mb-4">
        @foreach($types as $typeKey => $typeInfo)
        @php $cnt = isset($accounts[$typeKey]) ? $accounts[$typeKey]->count() : 0; @endphp
        <div class="col-6 col-md-2">
            <div class="summary-card">
                <div class="sc-label">{{ $typeInfo['label'] }}</div>
                <div class="sc-count">{{ $cnt }}</div>
                <span class="badge {{ $typeInfo['badge'] }} badge-type mt-1">akun</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── TABEL ── --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">

            {{-- Filter bar --}}
            <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom bg-white flex-wrap">
                <input type="text" id="filterInput" class="form-control form-control-sm"
                       style="max-width:260px;" placeholder="🔍 Cari kode / nama akun..."
                       oninput="filterRows(this.value)">
                <select id="filterType" class="form-select form-select-sm"
                        style="max-width:180px;" onchange="filterRows(document.getElementById('filterInput').value)">
                    <option value="">Semua Tipe</option>
                    @foreach($types as $k => $v)
                    <option value="{{ $k }}">{{ $v['label'] }}</option>
                    @endforeach
                </select>
                <select id="filterStatus" class="form-select form-select-sm"
                        style="max-width:150px;" onchange="filterRows(document.getElementById('filterInput').value)">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
                <span class="ms-auto text-muted small" id="rowCount">
                    Total: {{ $total }} akun ({{ $active }} aktif)
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="accountTable">
                    <thead class="bg-light">
                        <tr class="small text-uppercase text-muted" style="font-size:11px;">
                            <th class="py-3 px-4" style="width:110px;">Kode</th>
                            <th class="py-3">Nama Akun</th>
                            <th class="py-3" style="width:130px;">Tipe</th>
                            <th class="py-3" style="width:100px;">Saldo Normal</th>
                            <th class="py-3" style="width:90px;">Status</th>
                            <th class="py-3 text-center" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="accountTableBody">

                    @foreach($types as $typeKey => $typeInfo)
                    @if(isset($accounts[$typeKey]) && $accounts[$typeKey]->count())

                        {{-- Group header --}}
                        <tr class="group-header-row" data-group="{{ $typeKey }}">
                            <th colspan="6">
                                <span class="badge {{ $typeInfo['badge'] }} me-2" style="font-size:11px;">
                                    {{ $typeInfo['label'] }}
                                </span>
                                {{ $accounts[$typeKey]->count() }} akun
                            </th>
                        </tr>

                        @foreach($accounts[$typeKey]->sortBy('code') as $account)
                        @php
                            $normalBalance = $account->normal_balance
                                ?? (in_array($typeKey, ['asset','expense','cogs']) ? 'debit' : 'kredit');
                        @endphp
                        <tr class="account-row {{ $account->is_active ? '' : 'row-inactive' }}"
                            data-code="{{ strtolower($account->code) }}"
                            data-name="{{ strtolower($account->name) }}"
                            data-type="{{ $typeKey }}"
                            data-active="{{ $account->is_active ? '1' : '0' }}">

                            <td class="px-4 fw-bold" style="font-family: monospace; color:#0d6efd; font-size:13px;">
                                {{ $account->code }}
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;">{{ $account->name }}</div>
                                @if($account->description)
                                    <div class="text-muted" style="font-size:11px;">{{ $account->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $typeInfo['badge'] }} badge-type">
                                    {{ $typeInfo['label'] }}
                                </span>
                            </td>
                            <td>
                                <span class="nb-chip nb-{{ $normalBalance }}">
                                    {{ $normalBalance === 'debit' ? '← Debit' : 'Kredit →' }}
                                </span>
                            </td>
                            <td>
                                <span class="status-pill {{ $account->is_active ? 'active' : 'inactive' }}">
                                    {{ $account->is_active ? '✓ Aktif' : '✗ Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- Edit --}}
                                    <button class="btn btn-sm btn-outline-primary"
                                            style="padding:2px 8px; font-size:11px;"
                                            onclick="openEdit({{ $account->id }},
                                                '{{ addslashes($account->code) }}',
                                                '{{ addslashes($account->name) }}',
                                                '{{ $typeKey }}',
                                                '{{ $normalBalance }}',
                                                {{ $account->is_active ? 'true' : 'false' }},
                                                '{{ addslashes($account->description ?? '') }}')"
                                            title="Edit akun">
                                        ✏️
                                    </button>

                                    {{-- Toggle aktif/nonaktif --}}
                                    <form action="{{ route('accounts.toggle', $account->id) }}" method="POST"
                                          style="display:inline;">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $account->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                style="padding:2px 8px; font-size:11px;"
                                                title="{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            {{ $account->is_active ? '⏸' : '▶' }}
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form action="{{ route('accounts.destroy', $account->id) }}" method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('Hapus akun [{{ $account->code }}] {{ $account->name }}?\n\nAkun yang sudah dipakai di jurnal tidak dapat dihapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                style="padding:2px 8px; font-size:11px;"
                                                title="Hapus akun">
                                            🗑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach

                    @endif
                    @endforeach

                    {{-- Empty state --}}
                    <tr id="emptyRow" style="display:none;">
                        <td colspan="6" class="text-center text-muted py-5">
                            Tidak ada akun yang cocok dengan filter.
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div><!-- /container -->


{{-- ════════════════════════════════
     MODAL TAMBAH AKUN
     ════════════════════════════════ --}}
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold">➕ Tambah Akun Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 pb-0">

                    <div class="row g-3">
                        <div class="col-5">
                            <label class="form-label small fw-bold">Kode Akun <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control"
                                   placeholder="Contoh: 1-1001" required
                                   value="{{ old('code') }}">
                            <div class="form-text">Unik, tidak boleh sama</div>
                        </div>
                        <div class="col-7">
                            <label class="form-label small fw-bold">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="Contoh: Kas Tunai" required
                                   value="{{ old('name') }}">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tipe Akun <span class="text-danger">*</span></label>
                            <select name="type" id="addType" class="form-select" required
                                    onchange="autoNormalBalance('addNormal', this.value)">
                                @foreach($types as $k => $v)
                                <option value="{{ $k }}" {{ old('type') === $k ? 'selected' : '' }}>
                                    {{ $v['label'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Saldo Normal</label>
                            <select name="normal_balance" id="addNormal" class="form-select">
                                <option value="debit">← Debit</option>
                                <option value="kredit">Kredit →</option>
                            </select>
                            <div class="form-text">Otomatis sesuai tipe</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Deskripsi (opsional)</label>
                        <input type="text" name="description" class="form-control"
                               placeholder="Keterangan singkat akun ini"
                               value="{{ old('description') }}">
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox"
                               name="is_active" id="addIsActive" value="1" checked>
                        <label class="form-check-label small" for="addIsActive">Akun aktif</label>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pt-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">💾 Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ════════════════════════════════
     MODAL EDIT AKUN
     ════════════════════════════════ --}}
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold">✏️ Edit Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body px-4 pb-0">

                    <div class="row g-3">
                        <div class="col-5">
                            <label class="form-label small fw-bold">Kode Akun <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="editCode" class="form-control" required>
                        </div>
                        <div class="col-7">
                            <label class="form-label small fw-bold">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tipe Akun <span class="text-danger">*</span></label>
                            <select name="type" id="editType" class="form-select" required
                                    onchange="autoNormalBalance('editNormal', this.value)">
                                @foreach($types as $k => $v)
                                <option value="{{ $k }}">{{ $v['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Saldo Normal</label>
                            <select name="normal_balance" id="editNormal" class="form-select">
                                <option value="debit">← Debit</option>
                                <option value="kredit">Kredit →</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Deskripsi (opsional)</label>
                        <input type="text" name="description" id="editDescription" class="form-control"
                               placeholder="Keterangan singkat akun ini">
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox"
                               name="is_active" id="editIsActive" value="1">
                        <label class="form-check-label small" for="editIsActive">Akun aktif</label>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pt-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
/* ── Auto normal balance saat ganti tipe ── */
function autoNormalBalance(targetId, typeVal) {
    const debitTypes = ['asset', 'expense', 'cogs'];
    document.getElementById(targetId).value = debitTypes.includes(typeVal) ? 'debit' : 'kredit';
}

/* Inisialisasi saldo normal di modal tambah saat halaman load */
autoNormalBalance('addNormal', document.getElementById('addType').value);

/* ── Buka modal edit ── */
function openEdit(id, code, name, type, normalBalance, isActive, description) {
    const form = document.getElementById('editForm');
    form.action = `/accounts/${id}`;    // sesuaikan prefix route jika ada

    document.getElementById('editCode').value          = code;
    document.getElementById('editName').value          = name;
    document.getElementById('editType').value          = type;
    document.getElementById('editNormal').value        = normalBalance;
    document.getElementById('editDescription').value   = description;
    document.getElementById('editIsActive').checked    = isActive;

    const modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
    modal.show();
}

/* ── Filter tabel ── */
function filterRows(q) {
    q = q.toLowerCase().trim();
    const typeFilter   = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;

    let visible = 0;
    const rows  = document.querySelectorAll('#accountTableBody tr.account-row');
    const groups = document.querySelectorAll('#accountTableBody tr.group-header-row');

    rows.forEach(row => {
        const matchQ      = !q || row.dataset.code.includes(q) || row.dataset.name.includes(q);
        const matchType   = !typeFilter   || row.dataset.type   === typeFilter;
        const matchStatus = !statusFilter || row.dataset.active === statusFilter;
        const show = matchQ && matchType && matchStatus;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    /* Sembunyikan group header jika semua anggotanya disembunyikan */
    groups.forEach(g => {
        const gType = g.dataset.group;
        const anyVisible = [...rows].some(r => r.dataset.type === gType && r.style.display !== 'none');
        g.style.display = anyVisible ? '' : 'none';
    });

    document.getElementById('emptyRow').style.display = visible === 0 ? '' : 'none';
    document.getElementById('rowCount').textContent   = `Menampilkan: ${visible} akun`;
}
</script>

@endsection
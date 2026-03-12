@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Master Akun (COA)</h4>
                    <p class="text-muted small mb-0">Kelola kategori akun untuk jurnal otomatis</p>
                </div>
                <button class="btn btn-primary btn-sm px-4 rounded-3" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Akun
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr class="small text-uppercase text-muted">
                            <th class="py-3 px-4">Kode Akun</th>
                            <th class="py-3">Nama Akun</th>
                            <th class="py-3">Tipe</th>
                            <th class="py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                        <tr>
                            <td class="px-4 fw-bold text-primary">{{ $account->code }}</td>
                            <td>{{ $account->name }}</td>
                            <td>
                                <span class="badge {{ $account->type == 'income' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $account->type == 'income' ? 'text-success' : 'text-danger' }} px-3">
                                    {{ ucfirst($account->type) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Tambah Akun Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Akun</label>
                        <input type="text" name="code" class="form-control border-0 bg-light shadow-sm" placeholder="Contoh: 5-1001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Akun</label>
                        <input type="text" name="name" class="form-control border-0 bg-light shadow-sm" placeholder="Contoh: Beban Listrik" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Tipe Akun</label>
                        <select name="type" class="form-select border-0 bg-light shadow-sm" required>
                            <option value="income">Income (Pendapatan)</option>
                            <option value="expense">Expense (Beban/Biaya)</option>
                            <option value="asset">Asset (Harta)</option>
                            <option value="liability">Liability (Hutang)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
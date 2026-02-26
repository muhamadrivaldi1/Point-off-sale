@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-primary">Tambah User</h4>
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Batal</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Tipe Akun</label>
                <select name="role" id="roleSelect" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
                </select>
            </div>

            <div id="kasirSection" class="border p-3 rounded bg-light mb-3" style="display: none;">
                <div class="mb-3">
                    <label class="form-label fw-bold">Akses Kasir</label>
                    <select name="kasir_level" id="kasirLevel" class="form-select">
                        <option value="full" {{ old('kasir_level') === 'full' ? 'selected' : '' }}>Full (Semua Menu)</option>
                        <option value="custom" {{ old('kasir_level') === 'custom' ? 'selected' : '' }}>Custom (Pilih Menu)</option>
                    </select>
                </div>

                <div id="permissionBox" style="display: none;">
                    <label class="form-label fw-bold mb-2">Pilih Hak Akses:</label>
                    <div class="row">
                        @foreach($permissions as $permission)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary px-4">Simpan User</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const role = document.getElementById('roleSelect');
        const section = document.getElementById('kasirSection');
        const level = document.getElementById('kasirLevel');
        const box = document.getElementById('permissionBox');

        function toggle() {
            section.style.display = (role.value === 'kasir') ? 'block' : 'none';
            box.style.display = (role.value === 'kasir' && level.value === 'custom') ? 'block' : 'none';
        }

        role.addEventListener('change', toggle);
        level.addEventListener('change', toggle);
        toggle(); // Jalankan saat load
    });
</script>
@endsection
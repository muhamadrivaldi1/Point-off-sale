@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    {{-- Header hanya berisi judul --}}
    <div class="card-header bg-white py-3">
        <h4 class="mb-0 text-primary fw-bold">Edit User — {{ $user->name }}</h4>
    </div>

    <div class="card-body p-4">
        {{-- Alert Error --}}
        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <div class="d-flex">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>Terdapat kesalahan:</strong>
                        <ul class="mb-0 mt-1 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf
            @method('PUT')

            {{-- NAMA --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- EMAIL --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}"
                    required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- PASSWORD OPSIONAL --}}
            <div class="mb-3">
                <label class="form-label fw-bold">
                    Password Baru
                    <small class="text-muted fw-normal">(kosongkan jika tidak ingin diganti)</small>
                </label>
                <input type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Minimal 6 karakter">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="my-4">

            {{-- ROLE --}}
            @php
                $currentRole = old('role', $user->role ?? '');
            @endphp
            <div class="mb-3">
                <label class="form-label fw-bold">Tipe User <span class="text-danger">*</span></label>
                <select name="role" id="roleSelect"
                    class="form-select @error('role') is-invalid @enderror"
                    required>
                    <option value="">-- Pilih Role --</option>
                    <option value="owner" {{ $currentRole === 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="kasir" {{ $currentRole === 'kasir' ? 'selected' : '' }}>Kasir</option>
                </select>
            </div>

            {{-- KASIR SECTION --}}
            @php
                $currentKasirLevel = old('kasir_level', $user->kasir_level ?? 'full');
            @endphp
            <div id="kasirSection" class="border p-3 rounded bg-light mb-3" style="display: {{ $currentRole === 'kasir' ? 'block' : 'none' }}">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mode Kasir</label>
                    <select name="kasir_level" id="kasirLevel"
                        class="form-select @error('kasir_level') is-invalid @enderror">
                        <option value="full" {{ $currentKasirLevel === 'full' ? 'selected' : '' }}>
                            Kasir Full — Semua Akses
                        </option>
                        <option value="custom" {{ $currentKasirLevel === 'custom' ? 'selected' : '' }}>
                            Kasir Custom — Pilih Manual
                        </option>
                    </select>
                </div>

                {{-- PERMISSION CHECKBOX --}}
                @php
                    $userPermissionIds = old('permissions', $user->directPermissions->pluck('id')->toArray());
                @endphp
                <div id="permissionBox" style="display: {{ $currentKasirLevel === 'custom' ? 'block' : 'none' }}">
                    <label class="form-label fw-bold mb-2 text-muted small">PILIH HAK AKSES:</label>
                    <div class="row g-2">
                        @forelse($permissions as $permission)
                            <div class="col-md-4">
                                <div class="form-check p-2 border rounded bg-white ms-0 ps-5">
                                    <input class="form-check-input" type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        id="perm_{{ $permission->id }}"
                                        {{ in_array($permission->id, $userPermissionIds) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="perm_{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted small">Belum ada permission tersedia</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- TOMBOL AKSI BERDAMPINGAN --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Update
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('roleSelect');
        const kasirSection = document.getElementById('kasirSection');
        const kasirLevel = document.getElementById('kasirLevel');
        const permissionBox = document.getElementById('permissionBox');

        function toggle() {
            // Tampilkan section kasir jika role yang dipilih adalah kasir
            if (roleSelect.value === 'kasir') {
                kasirSection.style.display = 'block';
                // Tampilkan box permission hanya jika mode custom dipilih
                permissionBox.style.display = (kasirLevel.value === 'custom') ? 'block' : 'none';
            } else {
                kasirSection.style.display = 'none';
            }
        }

        roleSelect.addEventListener('change', toggle);
        kasirLevel.addEventListener('change', toggle);
        
        // Jalankan saat load pertama kali
        toggle();
    });
</script>
@endsection
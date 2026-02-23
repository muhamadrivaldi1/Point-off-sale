@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Edit User — {{ $user->name }}</h4>
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>

    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Terdapat kesalahan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf
            @method('PUT')

            {{-- NAMA --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
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
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
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
                <label class="form-label fw-semibold">
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

            <hr>

            {{-- ROLE --}}
            @php
                // Ambil role aktif user, fallback ke old() jika ada validasi error
                $currentRole = old('role', $user->roles->first()?->name ?? '');
            @endphp

            <div class="mb-3">
                <label class="form-label fw-semibold">Tipe User <span class="text-danger">*</span></label>
                <select name="role" id="roleSelect"
                    class="form-select @error('role') is-invalid @enderror"
                    required>
                    <option value="">-- Pilih Role --</option>
                    <option value="owner" {{ $currentRole === 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="kasir" {{ $currentRole === 'kasir' ? 'selected' : '' }}>Kasir</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- KASIR SECTION --}}
            @php
                $currentKasirLevel = old('kasir_level', $user->kasir_level ?? 'full');
            @endphp

            <div id="kasirSection" style="display: {{ $currentRole === 'kasir' ? 'block' : 'none' }}">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mode Kasir</label>
                    <select name="kasir_level" id="kasirLevel"
                        class="form-select @error('kasir_level') is-invalid @enderror">
                        <option value="full"   {{ $currentKasirLevel === 'full'   ? 'selected' : '' }}>
                            Kasir Full — Semua Akses
                        </option>
                        <option value="custom" {{ $currentKasirLevel === 'custom' ? 'selected' : '' }}>
                            Kasir Custom — Pilih Manual
                        </option>
                    </select>
                    @error('kasir_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PERMISSION CHECKBOX --}}
                @php
                    // Gunakan permissions yang sudah tersimpan, atau dari old() jika ada error
                    $userPermissionIds = old('permissions', $user->directPermissions->pluck('id')->toArray());
                @endphp

                <div id="permissionBox" style="display: {{ $currentKasirLevel === 'custom' ? 'block' : 'none' }}">
                    <div class="card card-body bg-light mb-3">
                        <h6 class="mb-2">Pilih Hak Akses</h6>
                        @forelse($permissions as $permission)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    id="perm_{{ $permission->id }}"
                                    {{ in_array($permission->id, $userPermissionIds) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada permission tersedia</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
            </div>

        </form>

    </div>
</div>

<script>
    const roleSelect    = document.getElementById('roleSelect');
    const kasirSection  = document.getElementById('kasirSection');
    const kasirLevel    = document.getElementById('kasirLevel');
    const permissionBox = document.getElementById('permissionBox');

    roleSelect.addEventListener('change', function () {
        if (this.value === 'kasir') {
            kasirSection.style.display = 'block';
        } else {
            kasirSection.style.display = 'none';
            permissionBox.style.display = 'none';
        }
    });

    kasirLevel.addEventListener('change', function () {
        permissionBox.style.display = this.value === 'custom' ? 'block' : 'none';
    });
</script>

@endsection
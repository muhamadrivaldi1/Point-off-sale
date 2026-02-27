@extends('layouts.app')

@section('content')
<div class="card shadow-sm">

    {{-- HEADER --}}
    <div class="card-header bg-white py-3">
        <h4 class="mb-0 text-primary fw-bold">Tambah User</h4>
    </div>

    <div class="card-body p-4">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            {{-- NAMA --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name') }}"
                       placeholder="Masukkan nama lengkap" required>
            </div>

            {{-- EMAIL --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}"
                       placeholder="nama@email.com" required>
            </div>

            {{-- PASSWORD --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password"
                       class="form-control"
                       placeholder="Minimal 6 karakter" required>
            </div>

            {{-- ROLE --}}
            <div class="mb-4">
                <label class="form-label fw-bold">Tipe Akun</label>
                <select name="role" id="roleSelect" class="form-select" required>
                    <option value="">-- Pilih Tipe Akun --</option>
                    <option value="owner" {{ old('role')=='owner'?'selected':'' }}>Owner</option>
                    <option value="kasir" {{ old('role')=='kasir'?'selected':'' }}>Kasir</option>
                </select>
            </div>

            {{-- KASIR CONFIG --}}
            <div id="kasirSection"
                 class="border p-3 rounded bg-light mb-4"
                 style="display:none;">

                {{-- LEVEL --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Akses Kasir</label>
                    <select name="kasir_level" id="kasirLevel" class="form-select">
                        <option value="full" {{ old('kasir_level')=='full'?'selected':'' }}>
                            Full (Semua Menu)
                        </option>
                        <option value="custom" {{ old('kasir_level')=='custom'?'selected':'' }}>
                            Custom (Pilih Menu)
                        </option>
                    </select>
                </div>

                {{-- PERMISSION --}}
                <div id="permissionBox" style="display:none;">
                    <label class="form-label fw-bold mb-2 text-muted small">
                        CENTANG MENU YANG DIIZINKAN:
                    </label>

                    <div class="row g-3">
                        @foreach($permissions as $permission)
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-white ms-3">
                                <input class="form-check-input permission-check"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->id }}"
                                       id="perm_{{ $permission->id }}"
                                       {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>

                                <label class="form-check-label"
                                       for="perm_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <hr>

            {{-- BUTTON --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    Simpan User
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

{{-- SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function(){

    const role = document.getElementById('roleSelect');
    const kasirSection = document.getElementById('kasirSection');
    const level = document.getElementById('kasirLevel');
    const permissionBox = document.getElementById('permissionBox');
    const checks = document.querySelectorAll('.permission-check');

    function toggleKasir(){
        // tampil hanya jika kasir
        if(role.value === 'kasir'){
            kasirSection.style.display = 'block';
        }else{
            kasirSection.style.display = 'none';
            permissionBox.style.display = 'none';
        }
    }

    function togglePermission(){
        // tampil hanya jika custom
        if(role.value === 'kasir' && level.value === 'custom'){
            permissionBox.style.display = 'block';

            // aktifkan checkbox
            checks.forEach(c => c.disabled = false);

        }else{
            permissionBox.style.display = 'none';

            // matikan & kosongkan checkbox
            checks.forEach(c => {
                c.checked = false;
                c.disabled = true;
            });
        }
    }

    role.addEventListener('change', function(){
        toggleKasir();
        togglePermission();
    });

    level.addEventListener('change', togglePermission);

    // initial load
    toggleKasir();
    togglePermission();

});
</script>

@endsection
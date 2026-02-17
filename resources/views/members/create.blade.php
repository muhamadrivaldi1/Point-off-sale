@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Tambah Member</h4>
                </div>

                <div class="card-body">

                    {{-- ERROR --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('members.store') }}" method="POST">
                        @csrf

                        {{-- NAMA --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama lengkap"
                                   required>
                        </div>

                        {{-- TELEPON --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Telepon</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="0812xxxxxxx"
                                   required>
                        </div>

                        {{-- ALAMAT --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea class="form-control"
                                      name="address"
                                      rows="3"
                                      placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                        </div>

                        {{-- LEVEL --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Level Member</label>
                            <select name="level" class="form-control form-select" required>
                                <option value="">-- Pilih Level --</option>
                                <option value="Basic" {{ old('level') == 'Basic' ? 'selected' : '' }}>Basic</option>
                                <option value="Silver" {{ old('level') == 'Silver' ? 'selected' : '' }}>Silver</option>
                                <option value="Gold" {{ old('level') == 'Gold' ? 'selected' : '' }}>Gold</option>
                            </select>
                        </div>

                        {{-- DISKON --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Diskon (%)</label>
                            <input type="number"
                                   class="form-control"
                                   name="discount"
                                   value="{{ old('discount',0) }}"
                                   min="0"
                                   max="100"
                                   step="0.01">
                            <small class="text-muted">
                                Isi manual jika ingin override diskon level
                            </small>
                        </div>

                        {{-- STATUS --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-control form-select" required>
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>

                        {{-- SUBMIT --}}
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">
                            Simpan Member
                        </button>

                        <a href="{{ route('members.index') }}" class="btn btn-secondary btn-lg w-100">
                            ← Kembali
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
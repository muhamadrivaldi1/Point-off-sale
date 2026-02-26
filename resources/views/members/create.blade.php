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
                                   required>
                        </div>

                        {{-- TELEPON --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Telepon</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   required>
                        </div>

                        {{-- ALAMAT --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea class="form-control"
                                      name="address"
                                      rows="3">{{ old('address') }}</textarea>
                        </div>

                        {{-- STATUS KARTU --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kartu Member</label>
                            <select name="has_card" id="has_card" class="form-control form-select" required>
                                <option value="no">Belum punya kartu (Generate otomatis)</option>
                                <option value="yes">Sudah punya kartu</option>
                            </select>
                        </div>

                        {{-- INPUT BARCODE --}}
                        <div class="mb-3" id="barcode_input" style="display:none;">
                            <label class="form-label fw-bold">Kode Barcode</label>
                            <input type="text"
                                   class="form-control"
                                   name="barcode"
                                   placeholder="Scan / input barcode kartu">
                        </div>

                        {{-- LEVEL --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Level Member</label>
                            <select name="level" class="form-control form-select" required>
                                <option value="">-- Pilih Level --</option>
                                <option value="Basic">Basic</option>
                                <option value="Silver">Silver</option>
                                <option value="Gold">Gold</option>
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
                        </div>

                        {{-- STATUS --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-control form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Simpan Member
                        </button>

                        <a href="{{ route('members.index') }}" class="btn btn-secondary w-100">
                            ← Kembali
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('has_card').addEventListener('change', function() {
    let barcodeField = document.getElementById('barcode_input');
    barcodeField.style.display = this.value === 'yes' ? 'block' : 'none';
});
</script>

@endsection
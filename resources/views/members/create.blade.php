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

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Nama</label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold">Telepon</label>
                            <input type="text" class="form-control form-control-lg" id="phone" name="phone" 
                                value="{{ old('phone') }}" placeholder="0812xxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">
                            <i class="bi bi-check-circle me-2"></i> Simpan
                        </button>

                        <a href="{{ route('members.index') }}" class="btn btn-secondary btn-lg w-100">
                            &larr; Kembali
                        </a>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

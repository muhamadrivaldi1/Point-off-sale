@extends('layouts.app')
@section('title','Tambah Member')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="mb-0">Tambah Member</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('members.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="{{ old('name') }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Telepon</label>
                <input type="text" id="phone" name="phone" class="form-control" 
                       value="{{ old('phone') }}" required>
                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('members.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

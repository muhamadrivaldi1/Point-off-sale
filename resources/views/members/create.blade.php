@extends('layouts.app')
@section('title','Tambah Member')

@section('content')
<h3>Tambah Member</h3>

<form action="{{ route('members.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        @error('name')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    <div class="mb-3">
        <label>Telepon</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
        @error('phone')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('members.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection

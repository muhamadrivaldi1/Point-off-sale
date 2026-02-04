@extends('layouts.app')
@section('title','Edit Member')

@section('content')
<h3 class="mb-4">Edit Member</h3>

<form action="{{ route('members.update', $member->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" 
               id="name"
               name="name" 
               class="form-control @error('name') is-invalid @enderror" 
               value="{{ old('name', $member->name) }}" 
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Telepon</label>
        <input type="text" 
               id="phone"
               name="phone" 
               class="form-control @error('phone') is-invalid @enderror" 
               value="{{ old('phone', $member->phone) }}" 
               required>
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="points" class="form-label">Poin</label>
        <input type="number"
               id="points"
               name="points"
               class="form-control @error('points') is-invalid @enderror"
               value="{{ old('points', $member->points) }}" 
               min="0">
        @error('points')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Poin ini bisa diupdate manual jika diperlukan.</small>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="{{ route('members.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection

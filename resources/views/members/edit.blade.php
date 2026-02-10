@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Member</h4>
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

                    <form action="{{ route('members.update', $member->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Nama</label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name"
                                value="{{ old('name', $member->name) }}" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold">Telepon</label>
                            <input type="text" class="form-control form-control-lg" id="phone" name="phone"
                                value="{{ old('phone', $member->phone) }}" placeholder="0812xxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3"
                                placeholder="Masukkan alamat lengkap">{{ old('address', $member->address) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select name="status" id="status" class="form-control form-select" required>
                                <option value="aktif" {{ $member->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ $member->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="level" class="form-label fw-bold">Level</label>
                            <select name="level" id="level" class="form-control form-select" required>
                                <option value="Basic" {{ $member->level == 'Basic' ? 'selected' : '' }}>Basic</option>
                                <option value="Silver" {{ $member->level == 'Silver' ? 'selected' : '' }}>Silver</option>
                                <option value="Gold" {{ $member->level == 'Gold' ? 'selected' : '' }}>Gold</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="discount" class="form-label fw-bold">Diskon (%)</label>
                            <input type="number" class="form-control" id="discount" name="discount"
                                value="{{ old('discount', $member->discount) }}" min="0" max="100" step="0.1">
                        </div>

                        <div class="mb-3">
                            <label for="total_spent" class="form-label fw-bold">Total Spent</label>
                            <input type="number" class="form-control" id="total_spent" name="total_spent"
                                value="{{ old('total_spent', $member->total_spent) }}" min="0" step="1000">
                        </div>

                        <div class="mb-3">
                            <label for="points" class="form-label fw-bold">Poin</label>
                            <input type="number" class="form-control" id="points" name="points"
                                value="{{ old('points', $member->points) }}" min="0" step="1">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">
                            <i class="bi bi-save me-2"></i> Update Member
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

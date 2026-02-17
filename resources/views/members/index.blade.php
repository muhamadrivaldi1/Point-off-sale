@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0 fw-bold">Daftar Member</h2>
        <a href="{{ route('members.create') }}" class="btn btn-primary shadow-sm">
            + Tambah Member
        </a>
    </div>

    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div id="success-alert" class="alert alert-success shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">

            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8f9fa;">
                    <tr class="text-center text-muted">
                        <th>No</th>
                        <th class="text-start">Nama</th>
                        <th>Telepon</th>
                        <th class="text-start">Alamat</th>
                        <th>Level</th>
                        <th>Diskon</th>
                        <th>Total Spent</th>
                        <th>Poin</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $no = ($members->currentPage() - 1) * $members->perPage();
                    @endphp

                    @forelse($members as $member)
                    @php $no++; @endphp
                    <tr class="text-center">
                        <td>{{ $no }}</td>

                        <td class="text-start fw-semibold">
                            {{ $member->name }}
                        </td>

                        <td>{{ $member->phone }}</td>

                        <td class="text-start text-muted">
                            {{ $member->address }}
                        </td>

                        <td>
                            <span class="badge 
                                {{ $member->level == 'Gold' ? 'bg-warning text-dark' : '' }}
                                {{ $member->level == 'Silver' ? 'bg-secondary' : '' }}
                                {{ $member->level == 'Basic' ? 'bg-primary' : '' }}">
                                {{ $member->level }}
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-success">
                                {{ $member->discount }}%
                            </span>
                        </td>

                        <td class="text-end">
                            Rp {{ number_format($member->total_spent, 0, ',', '.') }}
                        </td>

                        <td>
                            <span class="badge bg-info text-dark">
                                {{ $member->points }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $member->status == 'aktif' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('members.edit', $member->id) }}" 
                               class="btn btn-sm btn-warning">
                               Edit
                            </a>

                            <form action="{{ route('members.destroy', $member->id) }}" 
                                  method="POST" 
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin hapus member?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Belum ada member
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

    <div class="mt-3">
        {{ $members->links() }}
    </div>

</div>

{{-- AUTO HIDE ALERT --}}
<script>
    setTimeout(function () {
        let alert = document.getElementById('success-alert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 5000);
</script>

@endsection
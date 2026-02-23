@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Kelola Hak Akses User</h4>
        <a href="{{ route('users.create') }}" class="btn btn-success">
            + Tambah User
        </a>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="40">#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th width="130">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $i => $user)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>

                    <td>
                        @if($user->roles->isEmpty())
                            {{-- Belum punya role --}}
                            <span class="badge bg-danger">Tidak ada role</span>

                        @elseif($user->roles->contains('name', 'owner'))
                            {{-- OWNER --}}
                            <span class="badge bg-dark">Owner</span>
                            <span class="badge bg-success">Full Access</span>

                        @elseif($user->roles->contains('name', 'kasir'))
                            {{-- KASIR --}}
                            <span class="badge bg-primary">Kasir</span>

                            @if($user->kasir_level === 'full')
                                <span class="badge bg-success">Full</span>
                            @elseif($user->kasir_level === 'custom')
                                <span class="badge bg-warning text-dark">Custom</span>
                            @else
                                <span class="badge bg-secondary">Basic</span>
                            @endif

                        @else
                            {{-- Role lain (tampilkan nama role apa adanya) --}}
                            @foreach($user->roles as $r)
                                <span class="badge bg-secondary">{{ ucfirst($r->name) }}</span>
                            @endforeach
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        {{-- Owner tidak bisa dihapus --}}
                        @if(!$user->roles->contains('name', 'owner'))
                            <form action="{{ route('users.delete', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus {{ addslashes($user->name) }}?')">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </td>

                </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Belum ada user</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@endsection
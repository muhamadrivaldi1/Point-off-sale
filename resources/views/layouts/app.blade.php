<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','POS System')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f4f6f9; overflow-x: hidden; }
        .sidebar { width: 240px; min-height: 100vh; background: #212529; transition: all 0.3s; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; font-size: 0.9rem; }
        .sidebar a:hover { background: #343a40; color: #fff; padding-left: 20px; transition: 0.2s; }
        .sidebar .nav-header { font-size: 0.75rem; color: #6c757d; padding: 15px 15px 5px; text-transform: uppercase; font-weight: bold; }
        .collapse-inner a { padding-left: 35px; font-size: 0.85rem; }
        .content { padding: 20px; }
        hr.sidebar-divider { margin: 10px 15px; border-color: rgba(255,255,255,0.1); }
    </style>
</head>
<body>

@php
    $user = auth()->user();
    
    if($user) {
        $user->loadMissing('directPermissions');
    }

    $openSession = null;
    if($user && $user->role === 'kasir') {
        $openSession = \App\Models\CashierSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();
    }

    if (!function_exists('hasAkses')) {
        function hasAkses($permName) {
            $u = auth()->user();
            if (!$u) return false;
            if ($u->role === 'owner') return true;
            if ($u->role === 'kasir' && $u->kasir_level === 'full') return true;

            return $u->directPermissions->contains(function($p) use ($permName) {
                return strtolower(trim($p->name)) === strtolower(trim($permName));
            });
        }
    }
@endphp

<div class="d-flex">
    {{-- SIDEBAR --}}
    <div class="sidebar shadow">
        <div class="text-white text-center py-4 fw-bold border-bottom">
            <i class="bi bi-shop me-2"></i> POS SYSTEM
        </div>

        <div class="py-2">
            <a href="/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            
            <hr class="sidebar-divider">

            {{-- SECTION KASIR --}}
            @if(hasAkses('akses_pos') || hasAkses('akses_transaksi'))
            <div class="nav-header">Kasir</div>
            @if(hasAkses('akses_pos')) 
                <a href="/pos"><i class="bi bi-cash-stack me-2"></i> POS / Kasir</a> 
            @endif
            @if(hasAkses('akses_transaksi')) 
                <a href="/transactions"><i class="bi bi-receipt me-2"></i> Transaksi</a> 
            @endif
            @endif

            {{-- SECTION MASTER DATA --}}
            @php
                $showMaster = hasAkses('akses_produk') || hasAkses('akses_supplier') || hasAkses('akses_member') || hasAkses('kelola_user');
            @endphp
            @if($showMaster)
            <div class="nav-header">Master Data</div>
            <a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuMaster">
                <span><i class="bi bi-folder me-2"></i> Database</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse show ps-2" id="menuMaster">
                <div class="collapse-inner">
                    @if(hasAkses('akses_produk')) <a href="/products">Produk</a> @endif
                    @if(hasAkses('akses_supplier')) <a href="/suppliers">Supplier</a> @endif
                    @if(hasAkses('akses_member')) <a href="/members">Member</a> @endif
                    @if(hasAkses('kelola_user')) <a href="/users">Kelola User</a> @endif
                </div>
            </div>
            @endif

            {{-- SECTION OPERASIONAL --}}
            @php
                $showOps = hasAkses('akses_stok') || hasAkses('akses_pembelian') || hasAkses('akses_sesi_kasir') || hasAkses('akses_retur');
            @endphp
            @if($showOps)
            <div class="nav-header">Operasional</div>
            <a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuOps">
                <span><i class="bi bi-gear me-2"></i> Manajemen</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse show ps-2" id="menuOps">
                <div class="collapse-inner">
                    @if(hasAkses('akses_stok')) <a href="/stocks">Stok</a> @endif
                    @if(hasAkses('akses_pembelian')) <a href="{{ route('po.index') }}">Pembelian (PO)</a> @endif
                    @if(hasAkses('akses_sesi_kasir')) <a href="{{ route('cashier.sessions') }}">Sesi Kasir</a> @endif
                    @if(hasAkses('akses_retur')) <a href="/returns">Retur Barang</a> @endif
                </div>
            </div>
            @endif

            {{-- SECTION LAPORAN --}}
            @if(hasAkses('akses_laporan'))
            <div class="nav-header">Laporan</div>
            <a href="/reports/sales"><i class="bi bi-graph-up me-2"></i> Penjualan</a>
            <a href="/reports/stock"><i class="bi bi-bar-chart me-2"></i> Stok</a>
            @endif

            {{-- TOMBOL SESI KASIR --}}
            @if($user->role === 'kasir')
                <hr class="sidebar-divider">
                <div class="px-3">
                    @if($openSession)
                        <form action="{{ route('cashier.close') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning w-100"><i class="bi bi-door-closed"></i> Tutup Sesi</button>
                        </form>
                    @else
                        <a href="{{ route('cashier.open.form') }}" class="btn btn-sm btn-success w-100 text-white"><i class="bi bi-door-open"></i> Buka Sesi</a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        <nav class="navbar navbar-expand navbar-light bg-white shadow-sm px-4">
            {{-- BAGIAN Dashboard / @yield('title') SUDAH DIHAPUS --}}
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3"><span class="badge bg-primary">{{ strtoupper($user->role) }}</span></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <span class="fw-bold small me-2">{{ $user->name }}</span>
                        <i class="bi bi-person-circle fs-4"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li><a class="dropdown-item" href="/profile">Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Keluar</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div class="content flex-grow-1">@yield('content')</div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
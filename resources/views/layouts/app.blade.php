<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','POS System')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background: #f4f6f9; }
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #212529;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: #343a40;
            color: #fff;
        }
        .content { padding: 20px; }
        .card-chart { height: 220px; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ================= SAFE GLOBAL VARIABLE ================= --}}
@php
    $openSession = null;

    if(auth()->check() && auth()->user()->role === 'kasir') {
        try {
            $openSession = \App\Models\CashierSession::where('user_id', auth()->id())
                ->where('status', 'open')
                ->first();
        } catch (\Exception $e) {
            $openSession = null;
        }
    }
@endphp
{{-- ======================================================== --}}

<div class="d-flex">

    {{-- SIDEBAR --}}
    <div class="sidebar">
        <div class="text-white text-center py-3 fw-bold border-bottom">
            <i class="bi bi-shop"></i> POS SYSTEM
        </div>

        {{-- COMMON --}}
        <a href="/dashboard">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        {{-- ================= KASIR ================= --}}
        @if(auth()->check() && auth()->user()->role === 'kasir')

            <a href="/pos">
                <i class="bi bi-cash-stack"></i> POS / Kasir
            </a>

            <a href="/transactions">
                <i class="bi bi-receipt"></i> Transaksi
            </a>

            <a href="{{ route('cashier.sessions') }}">
                <i class="bi bi-table"></i> Sesi Kasir
            </a>

            <a href="/returns">
                <i class="bi bi-arrow-counterclockwise"></i> Retur Barang
            </a>
           <hr class="text-white" style="border-width: 3px; border-color: white;">
           
            {{-- <a href="/members">
                <i class="bi bi-people"></i> Member
            </a> --}}

          @if($openSession)
            <form action="{{ route('cashier.close') }}" method="POST" class="px-3">
                @csrf
                <button type="submit" class="btn btn-link text-warning p-0">
                    <i class="bi bi-door-closed"></i> Tutup Sesi Kasir
                </button>
            </form>
        @else
            <a href="{{ route('cashier.open.form') }}" class="text-success">
                <i class="bi bi-door-open"></i> Buka Sesi Kasir
            </a>
        @endif
        @endif

        {{-- ================= OWNER ================= --}}
       @if(auth()->check() && auth()->user()->role === 'owner')

    <a href="/pos">
        <i class="bi bi-cash-stack"></i> POS / Kasir
    </a>

    {{-- ================= DATA MASTER DROPDOWN ================= --}}
    <a class="d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#menuMaster"
       role="button"
       aria-expanded="false"
       aria-controls="menuMaster">
        <span><i class="bi bi-folder"></i> Data Master</span>
        <i class="bi bi-chevron-down"></i>
    </a>

    <div class="collapse ps-3" id="menuMaster">

        <a href="/products">
            <i class="bi bi-box-seam"></i> Produk
        </a>

        <a href="/suppliers">
            <i class="bi bi-truck"></i> Supplier
        </a>

        <a href="/members">
            <i class="bi bi-people"></i> Member
        </a>

        <a href="/master/harga">
            <i class="bi bi-tags"></i> Harga
        </a>

        <a href="{{ route('warehouses.index') }}">
            <i class="bi bi-building"></i> Gudang
        </a>

        @if(auth()->user()->hasPermission('kelola_user'))
        <a href="/users">
            <i class="bi bi-people-fill"></i> Kelola User
        </a>
@endif

    </div>

    {{-- ================= OPERASIONAL ================= --}}
    <a class="d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#menuOperasional"
       role="button">
        <span><i class="bi bi-gear"></i> Operasional</span>
        <i class="bi bi-chevron-down"></i>
    </a>

    <div class="collapse ps-3" id="menuOperasional">

        <a href="/stocks">
            <i class="bi bi-archive"></i> Stok
        </a>

        <a href="/transactions">
            <i class="bi bi-receipt"></i> Transaksi
        </a>

        <a href="/returns">
            <i class="bi bi-arrow-counterclockwise"></i> Retur Barang
        </a>

        <a href="{{ route('po.index') }}">
            <i class="bi bi-cart-fill"></i> Pembelian
        </a>

         <a href="{{ route('cashier.sessions') }}">
                <i class="bi bi-table"></i> Sesi Kasir
        </a>

    </div>

    {{-- ================= LAPORAN ================= --}}
    <a class="d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#menuLaporan"
       role="button">
        <span><i class="bi bi-graph-up"></i> Laporan</span>
        <i class="bi bi-chevron-down"></i>
    </a>

    <div class="collapse ps-3" id="menuLaporan">

        <a href="/reports/sales">
            <i class="bi bi-graph-up"></i> Penjualan
        </a>

        <a href="/reports/stock">
            <i class="bi bi-bar-chart"></i> Stok
        </a>

    </div>

@endif

    </div>

    {{-- MAIN --}}
    <div class="flex-grow-1">

        {{-- TOPBAR --}}
        <nav class="navbar navbar-light bg-white shadow-sm px-4 d-flex justify-content-between">
    <span class="navbar-text">
        Login sebagai:
        <strong>{{ auth()->user()->name }}</strong>
        <span class="badge bg-secondary ms-2">
            {{ strtoupper(auth()->user()->role) }}
        </span>
    </span>

    {{-- USER DROPDOWN --}}
    <div class="dropdown">
        <button
            class="btn btn-sm btn-light dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <i class="bi bi-person-circle fs-5"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a class="dropdown-item" href="/profile">
                    <i class="bi bi-person me-2"></i> Profil
                </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
        {{-- CONTENT --}}
        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

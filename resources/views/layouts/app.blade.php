<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','POS System')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }
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
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="d-flex">

    {{-- SIDEBAR --}}
    <div class="sidebar">
        <div class="text-white text-center py-3 fw-bold border-bottom">
            <i class="bi bi-shop"></i> POS SYSTEM
        </div>

        <a href="/dashboard">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="/pos">
            <i class="bi bi-cash-stack"></i> POS / Kasir
        </a>

        <a href="/products">
            <i class="bi bi-box-seam"></i> Produk
        </a>

        <a href="/stocks">
            <i class="bi bi-archive"></i> Stok
        </a>

        <a href="/transactions">
            <i class="bi bi-receipt"></i> Transaksi
        </a>

        <a href="/members">
            <i class="bi bi-people"></i> Member
        </a>

        <a href="/po">
            <i class="bi bi-truck"></i> Purchase Order
        </a>

        <hr class="text-secondary">

        <a href="/reports/sales">
            <i class="bi bi-graph-up"></i> Laporan Penjualan
        </a>

        <a href="/reports/stock">
            <i class="bi bi-bar-chart"></i> Laporan Stok
        </a>
    </div>

    {{-- MAIN AREA --}}
    <div class="flex-grow-1">

        {{-- TOPBAR --}}
        <nav class="navbar navbar-light bg-white shadow-sm px-4">
            <span class="navbar-text">
                Login sebagai: <strong>{{ auth()->user()->name ?? '-' }}</strong>
            </span>

            <form method="POST" action="/logout">
                @csrf
                <button class="btn btn-sm btn-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </nav>

        {{-- CONTENT --}}
        <div class="content">
            @yield('content')
        </div>

    </div>
</div>

</body>
</html>

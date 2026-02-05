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
        body {
            background: #f4f6f9;
        }

        /* SIDEBAR */
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

        /* CONTENT */
        .content {
            padding: 20px;
        }

        /* CHART CARD */
        .card-chart {
            height: 220px; /* ✅ ukurannya lebih kecil */
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

        <a href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="/pos"><i class="bi bi-cash-stack"></i> POS / Kasir</a>
        <a href="/products"><i class="bi bi-box-seam"></i> Produk</a>
        <a href="/stocks"><i class="bi bi-archive"></i> Stok</a>
        <a href="/transactions"><i class="bi bi-receipt"></i> Transaksi</a>
        <a href="/members"><i class="bi bi-people"></i> Member</a>
        <a href="/po"><i class="bi bi-truck"></i> Purchase Order</a>

        <hr class="text-secondary">

        <a href="/reports/sales"><i class="bi bi-graph-up"></i> Laporan Penjualan</a>
        <a href="/reports/stock"><i class="bi bi-bar-chart"></i> Laporan Stok</a>
    </div>

    {{-- MAIN AREA --}}
    <div class="flex-grow-1">

        {{-- TOPBAR --}}
        <nav class="navbar navbar-light bg-white shadow-sm px-4 d-flex justify-content-between">
            <span class="navbar-text">
                Login sebagai: <strong>{{ auth()->user()->name ?? '-' }}</strong>
            </span>

            {{-- DROPDOWN USER --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="/profile">
                            <i class="bi bi-person"></i> Profil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="/logout" class="d-inline">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        {{-- CONTENT --}}
        <div class="content">

            @yield('content')

            {{-- Contoh Dashboard Grafik Revenue --}}
            @if(request()->is('dashboard'))
            @php
                $labels = $revenueLabels ?? ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                $data   = $revenueData ?? [1200000, 1500000, 900000, 1700000, 1300000, 1900000, 0, 0, 0, 0, 0, 0];
            @endphp
            <div class="row mt-4">
                <div class="col-12 col-md-12"> <!-- ✅ Bisa lebih kecil juga di lebar -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Revenue Bulanan</h5>
                            <canvas id="revenueChart" class="card-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                const ctx = document.getElementById('revenueChart').getContext('2d');
                const revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($labels),
                        datasets: [{
                            label: 'Revenue (Rp)',
                            data: @json($data),
                            backgroundColor: 'rgba(13, 110, 253, 0.2)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: true },
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            </script>
            @endif

        </div>

    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

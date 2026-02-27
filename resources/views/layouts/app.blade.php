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

@php
$user = auth()->user();

/* LOAD PERMISSION */
if ($user && method_exists($user,'directPermissions')) {
    try { $user->loadMissing('directPermissions'); } catch (\Exception $e){}
}

/* SESSION KASIR */
$openSession = null;
if ($user && $user->role === 'kasir') {
    try {
        $openSession = \App\Models\CashierSession::where('user_id',$user->id)
                        ->where('status','open')->first();
    } catch (\Exception $e){}
}

/* HELPER AKSES MENU */
if (!function_exists('hasAkses')) {
function hasAkses($perm) {

    $u = auth()->user();
    if(!$u) return false;

    // Kasir FULL tetap full
    if($u->role === 'kasir' && $u->kasir_level === 'full') return true;

    // Jika tidak ada relasi → fallback owner
    if(!method_exists($u,'directPermissions')) return $u->role === 'owner';

    if(!$u->relationLoaded('directPermissions')) {
        try { $u->load('directPermissions'); }
        catch(\Exception $e){ return $u->role === 'owner'; }
    }

    // Owner tapi belum diset permission → tetap full
    if($u->role === 'owner' && $u->directPermissions->isEmpty()) return true;

    return $u->directPermissions->contains(function($p) use ($perm){
        return strtolower(trim($p->name)) === strtolower(trim($perm));
    });
}}
@endphp

<div class="d-flex">

<div class="sidebar">
<div class="text-white text-center py-3 fw-bold border-bottom">
<i class="bi bi-shop"></i> POS SYSTEM
</div>

<a href="/dashboard">
<i class="bi bi-speedometer2"></i> Dashboard
</a>

{{-- ================= KASIR ================= --}}
@if(auth()->check() && auth()->user()->role === 'kasir')

@if(hasAkses('akses_pos'))
<a href="/pos"><i class="bi bi-cash-stack"></i> POS / Kasir</a>
@endif

@if(hasAkses('akses_transaksi'))
<a href="/transactions"><i class="bi bi-receipt"></i> Transaksi</a>
@endif

@if(hasAkses('akses_sesi_kasir'))
<a href="{{ route('cashier.sessions') }}"><i class="bi bi-table"></i> Sesi Kasir</a>
@endif

@if(hasAkses('akses_retur'))
<a href="/returns"><i class="bi bi-arrow-counterclockwise"></i> Retur Barang</a>
@endif

<hr class="text-white" style="border-width:3px">

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

@if(hasAkses('akses_pos'))
<a href="/pos"><i class="bi bi-cash-stack"></i> POS / Kasir</a>
@endif

<a class="d-flex justify-content-between align-items-center"
data-bs-toggle="collapse" href="#menuMaster">
<span><i class="bi bi-folder"></i> Data Master</span>
<i class="bi bi-chevron-down"></i>
</a>

<div class="collapse ps-3" id="menuMaster">

@if(hasAkses('akses_produk'))
<a href="/products"><i class="bi bi-box-seam"></i> Produk</a>
@endif

@if(hasAkses('akses_supplier'))
<a href="/suppliers"><i class="bi bi-truck"></i> Supplier</a>
@endif

@if(hasAkses('akses_member'))
<a href="/members"><i class="bi bi-people"></i> Member</a>
@endif

@if(hasAkses('akses_produk'))
<a href="/master/harga"><i class="bi bi-tags"></i> Harga</a>
@endif

@if(hasAkses('akses_gudang'))
<a href="{{ route('warehouses.index') }}"><i class="bi bi-building"></i> Gudang</a>
@endif

@if(hasAkses('kelola_user'))
<a href="/users"><i class="bi bi-people-fill"></i> Kelola User</a>
@endif

</div>

<a class="d-flex justify-content-between align-items-center"
data-bs-toggle="collapse" href="#menuOperasional">
<span><i class="bi bi-gear"></i> Operasional</span>
<i class="bi bi-chevron-down"></i>
</a>

<div class="collapse ps-3" id="menuOperasional">

@if(hasAkses('akses_stok'))
<a href="/stocks"><i class="bi bi-archive"></i> Stok</a>
@endif

@if(hasAkses('akses_transaksi'))
<a href="/transactions"><i class="bi bi-receipt"></i> Transaksi</a>
@endif

@if(hasAkses('akses_retur'))
<a href="/returns"><i class="bi bi-arrow-counterclockwise"></i> Retur Barang</a>
@endif

@if(hasAkses('akses_pembelian'))
<a href="{{ route('po.index') }}"><i class="bi bi-cart-fill"></i> Pembelian</a>
@endif

@if(hasAkses('akses_sesi_kasir'))
<a href="{{ route('cashier.sessions') }}"><i class="bi bi-table"></i> Sesi Kasir</a>
@endif

</div>

<a class="d-flex justify-content-between align-items-center"
data-bs-toggle="collapse" href="#menuLaporan">
<span><i class="bi bi-graph-up"></i> Laporan</span>
<i class="bi bi-chevron-down"></i>
</a>

<div class="collapse ps-3" id="menuLaporan">

@if(hasAkses('akses_laporan'))
<a href="/reports/sales"><i class="bi bi-graph-up"></i> Penjualan</a>
@endif

@if(hasAkses('akses_laporan'))
<a href="/reports/stock"><i class="bi bi-bar-chart"></i> Stok</a>
@endif

</div>

@endif

</div>

<div class="flex-grow-1">

<nav class="navbar navbar-light bg-white shadow-sm px-4 d-flex justify-content-between">
<span class="navbar-text">
Login sebagai: <strong>{{ auth()->user()->name }}</strong>
<span class="badge bg-secondary ms-2">{{ strtoupper(auth()->user()->role) }}</span>
</span>

<div class="dropdown">
    <a href="#" class="text-dark d-flex align-items-center" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle fs-4"></i>
    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
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

<div class="content">
@yield('content')
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
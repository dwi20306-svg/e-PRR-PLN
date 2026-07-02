<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>e-PRR UP3 Banda Aceh — @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

{{-- ══ NAVBAR ══ --}}
<nav class="navbar">
    <a href="{{ route('dashboard') }}" class="navbar__brand">
        <img src="{{ asset('images/logo-pln.png') }}" alt="PLN"
             onerror="this.style.display='none'">
        <div class="navbar__brand-text">
            <span>e-PRR</span>
            <span class="sub">UP3 BANDA ACEH</span>
        </div>
    </a>
    <div class="navbar__spacer"></div>

    <div class="navbar__user">
        <div class="navbar__avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <div class="navbar__user-name">{{ auth()->user()->name }}</div>
        </div>
        <div class="navbar__dropdown">
            <a href="{{ route('profil.edit') }}">
                <i class="fa-solid fa-gear"></i> Kelola Profil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ══ SIDEBAR ══ --}}
@php
$ulpList = [
    'ulp_syiah_kuala'  => 'ULP Syiah Kuala',
    'ulp_jantho'       => 'ULP Jantho',
    'ulp_sabang'       => 'ULP Sabang',
    'ulp_merduati'     => 'ULP Merduati',
    'ulp_lambaro'      => 'ULP Lambaro',
    'ulp_keudeu_bieng' => 'ULP Keudeu Bieng',
];
$activeUlp   = request('ulp', array_key_first($ulpList));
$isDashboard = request()->routeIs('dashboard');
$isBerkas    = request()->routeIs('admin.dashboard');

// Hitung status tiap ULP
$statusUlp    = [];
$gambarFields = [
    'gambar_tul_vi01', 'gambar_tul_vi03', 'gambar_spk',
    'gambar_berita_acara', 'gambar_pdp', 'gambar_tug10',
    'gambar_invoice', 'gambar_rumah',
];
foreach (array_keys($ulpList) as $key) {
    $all = \App\Models\BerkasPrr::where('ulp', $key)->get();
    if ($all->isEmpty()) {
        $statusUlp[$key] = 'none';
    } else {
        $complete = $all->every(function($b) use ($gambarFields) {
            return collect($gambarFields)->every(fn($f) => !empty($b->$f));
        });
        $partial = $all->some(function($b) use ($gambarFields) {
            return collect($gambarFields)->some(fn($f) => !empty($b->$f));
        });
        $statusUlp[$key] = $complete ? 'complete' : ($partial ? 'partial' : 'none');
    }
}
@endphp

<aside class="sidebar" id="sidebar">

    {{-- Dashboard --}}
    <div class="sidebar__label">Menu</div>
    <a href="{{ route('dashboard') }}"
       class="sidebar__item {{ $isDashboard ? 'active' : '' }}">
        <i class="fa-solid fa-house" style="width:16px"></i>
        Dashboard
    </a>

    {{-- Data Berkas per ULP --}}
    <!-- <div class="sidebar__label" style="margin-top:8px">Data Berkas ULP</div>
    @foreach($ulpList as $key => $label)
        <a href="{{ route('admin.dashboard', ['ulp' => $key]) }}"
           class="sidebar__item {{ $isBerkas && $activeUlp === $key ? 'active' : '' }}">
            <i class="fa-solid fa-folder" style="width:16px"></i>
            {{ $label }}
            <span class="sidebar__status-dot dot-{{ $statusUlp[$key] ?? 'none' }}"></span>
        </a>
    @endforeach -->

    <div class="sidebar__label" style="margin-top:8px">Data Berkas ULP</div>

    {{-- Semua Berkas --}}
    <a href="{{ route('admin.dashboard') }}"
    class="sidebar__item {{ $isBerkas && !request()->has('ulp') ? 'active' : '' }}">
        <i class="fa-solid fa-folder-open" style="width:16px"></i>
        Semua Berkas
    </a>

    @foreach($ulpList as $key => $label)
        <a href="{{ route('admin.dashboard', ['ulp' => $key]) }}"
        class="sidebar__item {{ $isBerkas && request('ulp') === $key ? 'active' : '' }}">
            <i class="fa-solid fa-folder" style="width:16px"></i>
            {{ $label }}
            <span class="sidebar__status-dot dot-{{ $statusUlp[$key] ?? 'none' }}"></span>
        </a>
    @endforeach
</aside>

{{-- ══ KONTEN UTAMA ══ --}}
<main class="main">
    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

@stack('modals')
@stack('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });
});
function openModal(id)  { document.getElementById(id).classList.add('open');    }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
</body>
</html>